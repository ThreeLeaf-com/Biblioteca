<?php

namespace ThreeLeaf\Biblioteca\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use ThreeLeaf\Biblioteca\Models\Annotation;

/**
 * The {@link Annotation} {@link FormRequest} class used to validate incoming requests.
 *
 * @mixin Annotation
 *
 * @OA\Schema(
 *     schema="AnnotationRequest",
 *     required={"reference_id", "reference_type", "content"},
 *     @OA\Property(property="reference_id", type="string", example="f7f9d3e0-434b-11ed-b878-0242ac120002", description="Reference UUID for the associated paragraph or sentence"),
 *     @OA\Property(property="reference_type", type="string", example="\ThreeLeaf\Biblioteca\Models\Sentence", description="The type/class of the referenced entity"),
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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> The validation rules for the annotation request.
     */
    public function rules(): array
    {
        return [
            'reference_id' => ['required', 'uuid'],
            'reference_type' => ['required', 'string'],
            'content' => ['required', 'string'],
        ];
    }
}
