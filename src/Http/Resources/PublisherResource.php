<?php

namespace ThreeLeaf\Biblioteca\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use ThreeLeaf\Biblioteca\Models\Publisher;

/**
 * The {@link Publisher} {@link JsonResource} class used to shape API output.
 *
 * @mixin Publisher
 *
 * @OA\Schema(
 *     schema="PublisherResource",
 *     @OA\Property(property="publisher_id", type="string", example="f7f9d3e0-434b-11ed-b878-0242ac120002", description="UUID of the publisher"),
 *     @OA\Property(property="name", type="string", example="Penguin Random House", description="Name of the publisher"),
 *     @OA\Property(property="address", type="string", example="123 Publishing Lane, New York, NY", description="Address of the publisher"),
 *     @OA\Property(property="website", type="string", example="https://www.penguinrandomhouse.com", description="Website of the publisher"),
 *     @OA\Property(
 *         property="books",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/BookResource"),
 *         description="The books published by this publisher"
 *     ),
 * )
 */
class PublisherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed> The transformed publisher resource.
     */
    public function toArray(Request $request): array
    {
        return [
            'publisher_id' => $this->publisher_id,
            'name' => $this->name,
            'address' => $this->address,
            'website' => $this->website,
            'books' => BookResource::collection($this->whenLoaded('books')),
        ];
    }
}
