<?php

namespace ThreeLeaf\Biblioteca\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Chapter;

/**
 * The {@link Chapter} {@link FormRequest} class used to validate incoming requests.
 *
 * @mixin Chapter
 *
 * @OA\Schema(
 *     schema="ChapterRequest",
 *     required={"book_id", "chapter_number", "title"},
 *     @OA\Property(property="book_id", type="string", example="f7f9d3e0-434b-11ed-b878-0242ac120002", description="UUID of the associated book"),
 *     @OA\Property(property="chapter_number", type="integer", example=1, description="Number of the chapter in the book"),
 *     @OA\Property(property="title", type="string", example="The Mysterious Beginning", description="Title of the chapter"),
 *     @OA\Property(property="summary", type="string", example="This chapter introduces the main mystery.", description="A brief summary of the chapter"),
 *     @OA\Property(property="chapter_image_url", type="string", example="https://example.com/chapter1.jpg", description="URL of the chapter’s image"),
 *     @OA\Property(property="content", type="string", example="This is the first paragraph of the chapter.\nParagraph two.", description="Content of the chapter"),
 * )
 */
class ChapterRequest extends FormRequest
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
     * @return array<string, mixed> The validation rules for the chapter request.
     */
    public function rules(): array
    {
        return [
            'book_id' => ['required', 'exists:' . Book::TABLE_NAME . ',book_id', 'uuid'],
            'chapter_number' => ['sometimes', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'chapter_image_url' => ['nullable', 'url'],
            'content' => ['nullable', 'string'],
        ];
    }
}
