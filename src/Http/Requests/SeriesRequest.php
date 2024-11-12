<?php

namespace ThreeLeaf\Biblioteca\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use ThreeLeaf\Biblioteca\Models\Author;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Series;

/**
 * The {@link Series} {@link FormRequest} class used to validate incoming requests.
 *
 * @mixin Series
 *
 * @OA\Schema(
 *     schema="SeriesRequest",
 *     required={"title", "author_id"},
 *     @OA\Property(property="title", type="string", example="Mystery Chronicles", description="The title of the series"),
 *     @OA\Property(property="subtitle", type="string", example="The Beginning", description="The subtitle of the series"),
 *     @OA\Property(property="description", type="string", example="A captivating series of mystery books.", description="The description of the series"),
 *     @OA\Property(property="author_id", type="string", example="f7f9d3e0-434b-11ed-b878-0242ac120002", description="The unique ID of the associated author or editor"),
 *     @OA\Property(
 *         property="book_ids",
 *         type="array",
 *         @OA\Items(type="string", format="uuid"),
 *         description="The ordered array of book unique IDs to include in the series"
 *     ),
 * )
 */
class SeriesRequest extends FormRequest
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
     * @return array<string, mixed> The validation rules for the series request.
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'author_id' => ['required', 'exists:' . Author::TABLE_NAME . ',author_id', 'uuid'],
            'book_ids' => ['nullable', 'array'],
            'book_ids.*' => ['uuid', 'exists:' . Book::TABLE_NAME . ',book_id'],
        ];
    }
}
