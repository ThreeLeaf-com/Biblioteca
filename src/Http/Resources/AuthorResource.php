<?php

namespace ThreeLeaf\Biblioteca\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use ThreeLeaf\Biblioteca\Models\Author;

/**
 * The {@link Author} {@link JsonResource} class used to shape API output.
 *
 * @mixin Author
 *
 * @OA\Schema(
 *     title="AuthorResource",
 *     description="Author resource representation",
 *     @OA\Property(
 *         property="author_id",
 *         type="string",
 *         description="Unique identifier for the author",
 *         example="123e4567-e89b-12d3-a456-426614174000"
 *     ),
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
 *         example="Doe"
 *     ),
 *     @OA\Property(
 *         property="biography",
 *         type="string",
 *         description="Short biography of the author",
 *         example="John Doe is an accomplished author known for his contributions to modern literature."
 *     ),
 *     @OA\Property(
 *         property="author_image_url",
 *         type="string",
 *         format="url",
 *         description="URL to the author's profile image",
 *         example="https://example.com/images/authors/johndoe.jpg"
 *     ),
 *     @OA\Property(
 *         property="books",
 *         type="array",
 *         @OA\Items(type="string", description="Unique identifier for a book"),
 *         description="Array of book IDs associated with the author"
 *     )
 * )
 */
class AuthorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed> The transformed author resource.
     */
    public function toArray(Request $request): array
    {
        return [
            'author_id' => $this->author_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'biography' => $this->biography,
            'author_image_url' => $this->author_image_url,
            'books' => $this->books->pluck('book_id')->toArray(),
        ];
    }
}
