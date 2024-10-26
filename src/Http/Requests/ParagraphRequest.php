<?php

namespace ThreeLeaf\Biblioteca\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Models\Paragraph;

/**
 * The {@link Paragraph} {@link FormRequest} class used to validate incoming requests.
 *
 * @mixin Paragraph
 *
 * @OA\Schema(
 *     schema="ParagraphRequest",
 *     required={"chapter_id", "paragraph_number", "content"},
 *     @OA\Property(property="chapter_id", type="string", example="f7f9d3e0-434b-11ed-b878-0242ac120002", description="UUID of the associated chapter"),
 *     @OA\Property(property="paragraph_number", type="integer", example=1, description="Number of the paragraph in the chapter"),
 *     @OA\Property(property="content", type="string", example="This is the first paragraph of the chapter.", description="Content of the paragraph"),
 * )
 */
class ParagraphRequest extends FormRequest
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
     * @return array<string, mixed> The validation rules for the paragraph request.
     */
    public function rules(): array
    {
        return [
            'chapter_id' => 'required|exists:' . Chapter::TABLE_NAME . ',chapter_id|uuid',
            'paragraph_number' => 'required|integer|min:1',
            'content' => 'required|string',
        ];
    }
}
