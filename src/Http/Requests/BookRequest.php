<?php

namespace ThreeLeaf\Biblioteca\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use ThreeLeaf\Biblioteca\Models\Book;

/**
 * The {@link Book} {@link FormRequest} class used to validate incoming requests.
 *
 * @mixin Book
 *
 * @OA\Schema(
 *     schema="BookRequest",
 *     required={"title", "author_id", "publisher_id"},
 *     @OA\Property(property="title", type="string", example="The Great Adventure", description="Title of the book"),
 *     @OA\Property(property="author_id", type="integer", example=1, description="ID of the author of the book"),
 *     @OA\Property(property="publisher_id", type="integer", example=2, description="ID of the publisher of the book"),
 *     @OA\Property(property="published_date", type="string", format="date", example="2023-10-07", description="Published date of the book"),
 *     @OA\Property(property="isbn", type="string", example="9781234567890", description="ISBN of the book"),
 *     @OA\Property(property="edition", type="string", example="First", description="Edition of the book"),
 *     @OA\Property(property="locale", type="string", example="en", description="Locale of the book"),
 *     @OA\Property(property="suggested_citation", type="string", example="Doe, J. (2023). The Great Adventure.", description="Suggested citation for the book"),
 *     @OA\Property(property="cover_image_url", type="string", example="http://example.com/cover.jpg", description="URL of the book's cover image"),
 *     @OA\Property(property="summary", type="string", example="An intriguing tale of mystery and discovery.", description="Summary of the book"),
 *     @OA\Property(property="number_in_series", type="integer", example=1, description="Number of the book in its series"),
 * )
 */
class BookRequest extends FormRequest
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
     * @return array<string, mixed> The validation rules for the book request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'author_id' => 'required|exists:authors,id',
            'publisher_id' => 'required|exists:publishers,id',
            'published_date' => 'nullable|date',
            'isbn' => 'nullable|string|max:13|unique:books,isbn',
            'edition' => 'nullable|string|max:100',
            'locale' => 'nullable|string|max:10',
            'suggested_citation' => 'nullable|string',
            'cover_image_url' => 'nullable|url',
            'summary' => 'nullable|string',
            'number_in_series' => 'nullable|integer',
        ];
    }
}
