<?php

namespace ThreeLeaf\Biblioteca\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Models\Figure;

/**
 * The {@link Figure} {@link FormRequest} class used to validate incoming requests.
 *
 * @mixin Figure
 *
 * @OA\Schema(
 *     schema="FigureRequest",
 *     required={"chapter_id", "figure_label", "caption", "image_url"},
 *     @OA\Property(property="chapter_id", type="string", example="f7f9d3e0-434b-11ed-b878-0242ac120002", description="UUID of the associated chapter"),
 *     @OA\Property(property="figure_label", type="string", example="Fig 1.1", description="Alphanumeric label of the figure"),
 *     @OA\Property(property="caption", type="string", example="A detailed diagram of the structure.", description="Caption of the figure"),
 *     @OA\Property(property="image_url", type="string", example="http://example.com/figure1.jpg", description="URL of the figure image"),
 *     @OA\Property(property="description", type="string", example="This figure depicts the structure of the main component.", description="Description of the figure"),
 * )
 */
class FigureRequest extends FormRequest
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
     * @return array<string, mixed> The validation rules for the figure request.
     */
    public function rules(): array
    {
        return [
            'chapter_id' => ['required', 'exists:' . Chapter::TABLE_NAME . ',chapter_id', 'uuid'],
            'figure_label' => ['required', 'string', 'max:50'],
            'caption' => ['required', 'string', 'max:255'],
            'image_url' => ['required', 'url'],
            'description' => ['nullable', 'string'],
        ];
    }
}
