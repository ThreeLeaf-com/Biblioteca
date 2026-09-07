<?php

namespace ThreeLeaf\Biblioteca\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;
use Stringable;
use ThreeLeaf\Biblioteca\Exceptions\InvalidReferenceTypeException;
use ThreeLeaf\Biblioteca\Models\Annotation;

/**
 * The {@link Annotation} {@link FormRequest} class used to validate incoming requests.
 *
 * @mixin Annotation
 */
#[OA\Schema(
    schema: 'AnnotationRequest',
    required: ['reference_id', 'reference_type', 'content'],
    properties: [
        new OA\Property(
            property: 'reference_id',
            type: 'string',
            example: 'f7f9d3e0-434b-11ed-b878-0242ac120002',
            description: 'Reference UUID of an existing paragraph or sentence',
        ),
        new OA\Property(
            property: 'reference_type',
            type: 'string',
            example: 'b_sentences',
            description: 'The referenced entity. Must denote Paragraph or Sentence: the b_paragraphs or b_sentences morph alias, their class names in any letter case, a subclass, or a morph alias the host application has registered for one of them. It is stored, and returned, as the alias',
        ),
        new OA\Property(
            property: 'content',
            type: 'string',
            example: 'This is an annotation explaining the text.',
            description: 'The content of the annotation',
        ),
    ],
)]
class AnnotationRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool True if the user is authorized, otherwise false.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Strip a leading backslash from the reference type before it is validated.
     *
     * <code>\Foo\Bar</code> and <code>Foo\Bar</code> name the same class, and clients write
     * both. Normalizing here means one form reaches the rules and the model.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $referenceType = $this->input('reference_type');

        if (is_string($referenceType)) {
            $this->merge(['reference_type' => ltrim($referenceType, '\\')]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * <code>reference_type</code> must denote one of {@link Annotation::REFERENCE_TYPES},
     * because the stored value is later resolved to a class by Eloquent.
     * <code>reference_id</code> is then checked against the table that type resolves to, so
     * an annotation cannot be attached to a row that does not exist.
     *
     * The type is resolved once here and both rules are built from the result, rather than
     * each rule resolving it again. Resolution can reach the autoloader for an unrecognised
     * name, and these requests are unauthenticated by default.
     *
     * This is the HTTP half of the constraint. {@link Annotation} applies the same check to
     * ordinary model writes and to its own read paths, so a caller that bypasses the API is
     * held to it too.
     *
     * @return array<string, array<int, Closure|Stringable|string>> The validation rules for the annotation request.
     */
    public function rules(): array
    {
        $modelClass = $this->resolvedReferenceType();

        return [
            'reference_id' => $this->referenceIdRules($modelClass),
            'reference_type' => ['bail', 'required', 'string', $this->referenceTypeRule($modelClass)],
            'content' => ['required', 'string'],
        ];
    }

    /**
     * Resolve the submitted reference type to a permitted model class.
     *
     * @return class-string<\Illuminate\Database\Eloquent\Model>|null The resolved class, or null when the value does not denote one.
     */
    private function resolvedReferenceType(): ?string
    {
        $referenceType = $this->input('reference_type');

        if (!is_string($referenceType)) {
            return null;
        }

        try {
            return Annotation::resolveReferenceType($referenceType);
        } catch (InvalidReferenceTypeException) {
            return null;
        }
    }

    /**
     * Build the <code>reference_type</code> rule.
     *
     * This reports the outcome of {@link Annotation::resolveReferenceType()} rather than
     * listing the permitted classes with <code>Rule::in()</code>, so the API accepts exactly
     * what the model accepts: a morph alias the host has registered, a subclass, or a class
     * name in any letter case. A literal list would reject all three and leave a morph-map
     * host with no working API path.
     *
     * @param string|null $modelClass The resolved model class, or null when resolution failed.
     *
     * @return Closure The reference type rule.
     */
    private function referenceTypeRule(?string $modelClass): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($modelClass): void {
            if ($modelClass === null) {
                $fail('The :attribute must reference a paragraph or a sentence.');
            }
        };
    }

    /**
     * Build the <code>reference_id</code> rules for the resolved reference type.
     *
     * The <code>exists</code> rule can only be added once the reference type is known to
     * denote a permitted model. When it does not, the rule is omitted and the
     * <code>reference_type</code> rule reports the error instead. The rule is built from
     * the model class rather than its table name, because
     * {@link \Illuminate\Validation\Rules\DatabaseRule::resolveTableName()} reads the
     * model's connection only when it is given a class name.
     *
     * @param string|null $modelClass The resolved model class, or null when resolution failed.
     *
     * @return array<int, Stringable|string> The rules for the reference identifier.
     */
    private function referenceIdRules(?string $modelClass): array
    {
        $rules = ['bail', 'required', 'uuid'];

        if ($modelClass !== null) {
            /* The class, not the table name, so the rule follows the model's own connection. */
            $rules[] = Rule::exists($modelClass, (new $modelClass())->getKeyName());
        }

        return $rules;
    }
}
