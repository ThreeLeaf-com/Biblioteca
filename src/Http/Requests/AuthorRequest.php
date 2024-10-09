<?php

namespace ThreeLeaf\Biblioteca\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use ThreeLeaf\Biblioteca\Models\Author;

/**
 * The {@link Author} {@link FormRequest} class used to validate incoming requests.
 *
 * @OA\Schema(
 *     title="AuthorRequest",
 *     description="Request body for creating or updating an author",
 *     @OA\Property(
 *         property="first_name",
 *         type="string",
 *         description="First name of the author",
 *         example="John"
 *     ),
 *     @OA\Property(
 *         property="last_name",
 *         type="string",
 *         description="Last name of the author",
 *         example="Marsh"
 *     ),
 *     @OA\Property(
 *         property="biography",
 *         type="string",
 *         description="Short biography of the author",
 *         example="John Marsh is an accomplished author known for his contributions to modern literature."
 *     ),
 *     @OA\Property(
 *         property="author_image_url",
 *         type="string",
 *         format="url",
 *         description="URL to the author's profile image",
 *         example="https://example.com/images/authors/john-marsh.jpg"
 *     )
 * )
 *
 * @mixin Author
 */
class AuthorRequest extends FormRequest
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
     * @return array<string, mixed> The validation rules for the author request.
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'biography' => 'nullable|string',
            'author_image_url' => 'nullable|string|url',
        ];
    }
}
