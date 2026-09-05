<?php

namespace ThreeLeaf\Biblioteca\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use ThreeLeaf\Biblioteca\Models\Annotation;
use ThreeLeaf\Biblioteca\Providers\BibliotecaServiceProvider;

/**
 * The {@link Annotation} {@link FormRequest} class used to validate incoming requests.
 *
 * @mixin Annotation
 *
 * @OA\Schema(
 *     schema="AnnotationRequest",
 *     required={"reference_id", "reference_type", "content"},
 *     @OA\Property(property="reference_id", type="string", example="f7f9d3e0-434b-11ed-b878-0242ac120002", description="Reference UUID of an existing paragraph or sentence"),
 *     @OA\Property(property="reference_type", type="string", enum={"b_paragraphs", "b_sentences"}, example="b_sentences", description="The morph alias of the referenced entity. The legacy fully-qualified class names are still accepted and are normalized to the alias"),
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
     * Normalize a legacy fully-qualified class name into its morph alias.
     *
     * Releases before the morph map stored <code>reference_type</code> as a class name.
     * Accepting those values keeps existing API clients working; anything that is neither
     * a registered alias nor one of the two mapped class names is left untouched and is
     * then rejected by {@link AnnotationRequest::rules()}.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $referenceType = $this->input('reference_type');

        if (is_string($referenceType)) {
            $alias = array_search(ltrim($referenceType, '\\'), BibliotecaServiceProvider::MORPH_MAP, true);

            if ($alias !== false) {
                $this->merge(['reference_type' => $alias]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * <code>reference_type</code> is restricted to the aliases registered in
     * {@link BibliotecaServiceProvider::MORPH_MAP}, because the stored value is later
     * resolved to a class by Eloquent. <code>reference_id</code> is then checked against
     * the table that alias resolves to, so an annotation cannot be attached to a row that
     * does not exist.
     *
     * @return array<string, array<int, ValidationRule|string>> The validation rules for the annotation request.
     */
    public function rules(): array
    {
        return [
            'reference_id' => $this->referenceIdRules(),
            'reference_type' => ['required', 'string', Rule::in(array_keys(BibliotecaServiceProvider::MORPH_MAP))],
            'content' => ['required', 'string'],
        ];
    }

    /**
     * Build the <code>reference_id</code> rules for the submitted reference type.
     *
     * The <code>exists</code> rule can only be added once the reference type is known to be
     * one of the registered aliases. When it is not, the rule is omitted and the
     * <code>reference_type</code> allow-list reports the error instead.
     *
     * @return array<int, ValidationRule|string> The rules for the reference identifier.
     */
    private function referenceIdRules(): array
    {
        $rules = ['required', 'uuid'];
        $referenceType = $this->input('reference_type');
        $modelClass = is_string($referenceType)
            ? (BibliotecaServiceProvider::MORPH_MAP[$referenceType] ?? null)
            : null;

        if ($modelClass !== null) {
            $model = new $modelClass();
            $rules[] = Rule::exists($model->getTable(), $model->getKeyName());
        }

        return $rules;
    }
}
