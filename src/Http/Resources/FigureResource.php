<?php

namespace ThreeLeaf\Biblioteca\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use ThreeLeaf\Biblioteca\Models\Figure;

/**
 * The {@link Figure} {@link JsonResource} class used to shape API output.
 *
 * This resource provides a structured representation of a figure, including
 * its key attributes like caption, description, and image URL.
 *
 * @mixin Figure
 *
 * @OA\Schema(
 *     schema="FigureResource",
 *     @OA\Property(property="figure_id", type="string", example="b1234567-89ab-cdef-0123-456789abcdef", description="UUID of the figure"),
 *     @OA\Property(property="chapter_id", type="string", example="f7f9d3e0-434b-11ed-b878-0242ac120002", description="UUID of the associated chapter"),
 *     @OA\Property(property="figure_label", type="string", example="Fig 1.1", description="Alphanumeric label of the figure"),
 *     @OA\Property(property="caption", type="string", example="A detailed diagram of the structure.", description="Caption of the figure"),
 *     @OA\Property(property="image_url", type="string", example="http://example.com/figure1.jpg", description="URL of the figure image"),
 *     @OA\Property(property="description", type="string", example="This figure depicts the structure of the main component.", description="Description of the figure"),
 * )
 */
class FigureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed> The transformed figure resource.
     */
    public function toArray(Request $request): array
    {
        return [
            'figure_id' => $this->figure_id,
            'chapter_id' => $this->chapter_id,
            'figure_label' => $this->figure_label,
            'caption' => $this->caption,
            'image_url' => $this->image_url,
            'description' => $this->description,
        ];
    }
}
