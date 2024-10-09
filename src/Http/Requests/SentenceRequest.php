<?php

namespace ThreeLeaf\Biblioteca\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use ThreeLeaf\Biblioteca\Models\Sentence;

/**
 * The {@link Sentence} {@link FormRequest} class used to validate incoming requests.
 *
 * @mixin Sentence
 *
 * @OA\Schema(
 *     schema="SentenceRequest",
 *     required={"paragraph_id", "sentence_number", "content"},
 *     @OA\Property(property="paragraph_id", type="string", example="f7f9d3e0-434b-11ed-b878-0242ac120002", description="UUID of the associated paragraph"),
 *     @OA\Property(property="sentence_number", type="integer", example=1, description="Number of the sentence within the paragraph"),
 *     @OA\Property(property="content", type="string", example="This is the first sentence of the paragraph.", description="Content of the sentence"),
 * )
 */
class SentenceRequest extends FormRequest
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
     * @return array<string, mixed> The validation rules for the sentence request.
     */
    public function rules(): array
    {
        return [
            'paragraph_id' => 'required|exists:paragraphs,paragraph_id|uuid',
            'sentence_number' => 'required|integer|min:1',
            'content' => 'required|string',
        ];
    }
}
