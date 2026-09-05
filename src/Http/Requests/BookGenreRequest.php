<?php

namespace ThreeLeaf\Biblioteca\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Genre;

/**
 * The {@link FormRequest} used to validate genres being attached to a {@link Book}.
 *
 * @OA\Schema(
 *     schema="BookGenreRequest",
 *     required={"genre_ids"},
 *     @OA\Property(
 *         property="genre_ids",
 *         type="array",
 *         @OA\Items(type="string", format="uuid", example="b1234567-89ab-cdef-0123-456789abcdef"),
 *         description="Identifiers of existing genres to attach to the book"
 *     ),
 * )
 */
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
     * failure. The rule is built from the model class rather than its table name, because
     * {@link \Illuminate\Validation\Rules\DatabaseRule::resolveTableName()} reads the
     * model's connection only when it is given a class name.
     *
     * @return array<string, array<int, ValidationRule|string>> The validation rules for the request.
     */
    public function rules(): array
    {
        return [
            'genre_ids' => ['required', 'array'],
            'genre_ids.*' => ['bail', 'uuid', Rule::exists(Genre::class, 'genre_id')],
        ];
    }
}
