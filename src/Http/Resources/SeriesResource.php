<?php

namespace ThreeLeaf\Biblioteca\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use ThreeLeaf\Biblioteca\Models\Series;

/**
 * The {@link Series} {@link JsonResource} class used to shape API output.
 *
 * @mixin Series
 *
 * @OA\Schema(
 *     schema="SeriesResource",
 *     @OA\Property(property="series_id", type="string", example="b1234567-89ab-cdef-0123-456789abcdef", description="UUID of the series"),
 *     @OA\Property(property="name", type="string", example="Mystery Chronicles", description="Name of the series"),
 *     @OA\Property(property="description", type="string", example="A captivating series of mystery books.", description="Description of the series"),
 *     @OA\Property(
 *         property="author",
 *         ref="#/components/schemas/AuthorResource",
 *         description="The author associated with the series"
 *     ),
 *     @OA\Property(
 *         property="books",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/BookResource"),
 *         description="Books that are part of this series"
 *     ),
 * )
 */
class SeriesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed> The transformed series resource.
     */
    public function toArray(Request $request)
    {
        return [
            'series_id' => $this->series_id,
            'name' => $this->name,
            'description' => $this->description,
            'author' => new AuthorResource($this->whenLoaded('author')),
            'books' => BookResource::collection($this->whenLoaded('books')),
        ];
    }
}
