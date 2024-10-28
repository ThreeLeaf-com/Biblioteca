<?php

namespace ThreeLeaf\Biblioteca\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use ThreeLeaf\Biblioteca\Models\Tag;

/**
 * The {@link Tag} {@link FormRequest} class used to validate incoming requests.
 *
 * @mixin Tag
 *
 * @OA\Schema(
 *     schema="TagRequest",
 *     required={"name"},
 *     @OA\Property(property="name", type="string", example="Fiction", description="Name of the tag"),
 * )
 */
class TagRequest extends FormRequest
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
     * @return array<string, mixed> The validation rules for the tag request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
