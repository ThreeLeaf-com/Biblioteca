<?php

namespace ThreeLeaf\Biblioteca\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use ThreeLeaf\Biblioteca\Models\Tag;

/**
 * The {@link Tag} {@link JsonResource} class used to format the response for a tag.
 *
 * @mixin Tag
 *
 * @OA\Schema(
 *     schema="TagResource",
 *     @OA\Property(property="tag_id", type="string", example="b1234567-89ab-cdef-0123-456789abcdef", description="UUID of the tag"),
 *     @OA\Property(property="name", type="string", example="Fiction", description="Name of the tag"),
 *     @OA\Property(
 *         property="books",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/BookResource"),
 *         description="Books associated with this tag"
 *     ),
 * )
 */
class TagResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed> The transformed tag resource.
     */
    public function toArray(Request $request): array
    {
        return [
            'tag_id' => $this->tag_id,
            'name' => $this->name,
            'books' => BookResource::collection($this->whenLoaded('books')),
        ];
    }
}
