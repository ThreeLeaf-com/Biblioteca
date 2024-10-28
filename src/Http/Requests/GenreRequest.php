<?php

namespace ThreeLeaf\Biblioteca\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use ThreeLeaf\Biblioteca\Models\Genre;

/**
 * The {@link Genre} {@link FormRequest} class used to validate incoming requests.
 *
 * @mixin Genre
 *
 * @OA\Schema(
 *     schema="GenreRequest",
 *     required={"name"},
 *     @OA\Property(property="name", type="string", example="Science Fiction", description="Name of the genre"),
 *     @OA\Property(property="description", type="string", example="A genre that explores futuristic concepts.", description="Description of the genre"),
 * )
 */
class GenreRequest extends FormRequest
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
     * @return array<string, mixed> The validation rules for the genre request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}
