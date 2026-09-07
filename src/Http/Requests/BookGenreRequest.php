<?php

namespace ThreeLeaf\Biblioteca\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Genre;

/**
 * The {@link FormRequest} used to validate genres being attached to a {@link Book}.
 */
#[OA\Schema(
    schema: 'BookGenreRequest',
    required: ['genre_ids'],
    properties: [
        new OA\Property(
            property: 'genre_ids',
            type: 'array',
            items: new OA\Items(type: 'string', format: 'uuid', example: 'b1234567-89ab-cdef-0123-456789abcdef'),
            description: 'Identifiers of existing genres to attach to the book',
        ),
    ],
)]
class BookGenreRequest extends FormRequest
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
     * Each identifier is checked against the genres table, so an unknown one is reported as a
     * validation error rather than reaching the pivot insert and surfacing as a foreign-key
     * failure.
     *
     * <code>required</code> on the element is not redundant. Without an implicit rule
     * present, Laravel skips the whole element rule set for a value that trims to empty, so
     * <code>[""]</code> and <code>[" "]</code> would pass the array through unvalidated and
     * reach the database. The stock <code>ConvertEmptyStringsToNull</code> middleware
     * happens to turn those into <code>null</code>, but that is the host's middleware
     * stack, not this rule's guarantee.
     *
     * @return array<string, array<int, ValidationRule|string>> The validation rules for the request.
     */
    public function rules(): array
    {
        return [
            'genre_ids' => ['required', 'array'],
            'genre_ids.*' => ['bail', 'required', 'uuid', Rule::exists(Genre::class, 'genre_id')],
        ];
    }
}
