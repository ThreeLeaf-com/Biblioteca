<?php

namespace ThreeLeaf\Biblioteca\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use ThreeLeaf\Biblioteca\Models\Publisher;

/**
 * The {@link Publisher} {@link FormRequest} class used to validate incoming requests.
 *
 * @mixin Publisher
 *
 * @OA\Schema(
 *     schema="PublisherRequest",
 *     required={"name"},
 *     @OA\Property(property="name", type="string", example="ThreeLeaf Publishing", description="Name of the publisher"),
 *     @OA\Property(property="address", type="string", example="123 Publishing Lane, New York, NY", description="Address of the publisher"),
 *     @OA\Property(property="website", type="string", example="https://threeleaf.com", description="Website of the publisher"),
 * )
 */
class PublisherRequest extends FormRequest
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
     * @return array<string, mixed> The validation rules for the publisher request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
        ];
    }
}
