<?php

namespace ThreeLeaf\Biblioteca\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Stringable;
use ThreeLeaf\Biblioteca\Models\Annotation;

/**
 * The {@link Annotation} {@link FormRequest} class used to validate incoming requests.
 *
 * @mixin Annotation
 *
 * @OA\Schema(
 *     schema="AnnotationRequest",
 *     required={"reference_id", "reference_type", "content"},
 *     @OA\Property(property="reference_id", type="string", example="f7f9d3e0-434b-11ed-b878-0242ac120002", description="Reference UUID of an existing paragraph or sentence"),
 *     @OA\Property(property="reference_type", type="string", enum={"ThreeLeaf\Biblioteca\Models\Paragraph", "ThreeLeaf\Biblioteca\Models\Sentence"}, example="ThreeLeaf\Biblioteca\Models\Sentence", description="The class of the referenced entity. Only Paragraph and Sentence are permitted; a leading backslash is accepted and stripped"),
 *     @OA\Property(property="content", type="string", example="This is an annotation explaining the text.", description="The content of the annotation"),
 * )
 */
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
     * both. Normalizing here means the allow-list below compares one form. The comparison
     * is otherwise exact, so a mis-cased class name is rejected rather than stored in a
     * form the allow-list would not match again.
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
     * <code>reference_type</code> is restricted to {@link Annotation::REFERENCE_TYPES},
     * because the stored value is later resolved to a class by Eloquent.
     * <code>reference_id</code> is then checked against the table that type resolves to, so
     * an annotation cannot be attached to a row that does not exist.
     *
     * This is the HTTP half of the constraint. {@link Annotation} enforces the same
     * allow-list on every write and on resolution, so a caller that bypasses the API is
     * held to it too.
     *
     * @return array<string, array<int, Stringable|string>> The validation rules for the annotation request.
     */
    public function rules(): array
    {
        return [
            'reference_id' => $this->referenceIdRules(),
            'reference_type' => ['required', 'string', Rule::in(Annotation::REFERENCE_TYPES)],
            'content' => ['required', 'string'],
        ];
    }

    /**
     * Build the <code>reference_id</code> rules for the submitted reference type.
     *
     * The <code>exists</code> rule can only be added once the reference type is known to be
     * permitted. When it is not, the rule is omitted and the <code>reference_type</code>
     * allow-list reports the error instead. The rule is built from the model class rather
     * than its table name, because
     * {@link \Illuminate\Validation\Rules\DatabaseRule::resolveTableName()} reads the
     * model's connection only when it is given a class name.
     *
     * @return array<int, Stringable|string> The rules for the reference identifier.
     */
    private function referenceIdRules(): array
    {
        $rules = ['bail', 'required', 'uuid'];
        $referenceType = $this->input('reference_type');
        $isPermitted = is_string($referenceType)
            && in_array($referenceType, Annotation::REFERENCE_TYPES, true);

        if ($isPermitted) {
            /* The class, not the table name, so the rule follows the model's own connection. */
            $rules[] = Rule::exists($referenceType, (new $referenceType())->getKeyName());
        }

        return $rules;
    }
}
