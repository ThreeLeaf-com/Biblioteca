<?php

namespace ThreeLeaf\Biblioteca\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use ThreeLeaf\Biblioteca\Models\Genre;

/**
 * The {@link Genre} {@link JsonResource} class used to shape API output.
 *
 * @mixin Genre
 *
 * @OA\Schema(
 *     schema="GenreResource",
 *     @OA\Property(property="genre_id", type="string", example="b1234567-89ab-cdef-0123-456789abcdef", description="UUID of the genre"),
 *     @OA\Property(property="name", type="string", example="Science Fiction", description="Name of the genre"),
 *     @OA\Property(property="description", type="string", example="A genre that explores futuristic concepts.", description="Description of the genre"),
 *     @OA\Property(
 *         property="books",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/BookResource"),
 *         description="Books associated with this genre"
 *     ),
 * )
 */
class GenreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed> The transformed genre resource.
     */
    public function toArray(Request $request): array
    {
        return [
            'genre_id' => $this->genre_id,
            'name' => $this->name,
            'description' => $this->description,
            'books' => BookResource::collection($this->whenLoaded('books')),
        ];
    }
}
