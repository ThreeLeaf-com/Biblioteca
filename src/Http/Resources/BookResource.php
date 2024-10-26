<?php

namespace ThreeLeaf\Biblioteca\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use ThreeLeaf\Biblioteca\Models\Book;

/**
 * The {@link Book} {@link JsonResource} class used to shape API output.
 *
 * @mixin Book
 *
 * @OA\Schema(
 *     schema="BookResource",
 *     @OA\Property(property="id", type="integer", example=1, description="ID of the book"),
 *     @OA\Property(property="title", type="string", example="The Great Adventure", description="Title of the book"),
 *     @OA\Property(property="author", ref="#/components/schemas/AuthorResource", description="Author of the book"),
 *     @OA\Property(property="publisher", type="object", ref="#/components/schemas/PublisherResource", description="Publisher of the book"),
 *     @OA\Property(property="series", type="object", ref="#/components/schemas/SeriesResource", description="Series to which the book belongs"),
 *     @OA\Property(property="published_date", type="string", format="date", example="2023-10-07", description="Published date of the book"),
 *     @OA\Property(property="edition", type="string", example="First", description="Edition of the book"),
 *     @OA\Property(property="locale", type="string", example="en", description="Locale of the book"),
 *     @OA\Property(property="suggested_citation", type="string", example="Doe, J. (2023). The Great Adventure.", description="Suggested citation for the book"),
 *     @OA\Property(property="cover_image_url", type="string", example="http://example.com/cover.jpg", description="URL of the book's cover image"),
 *     @OA\Property(property="summary", type="string", example="An intriguing tale of mystery and discovery.", description="Summary of the book"),
 *     @OA\Property(property="number_in_series", type="integer", example=1, description="Number of the book in its series"),
 *     @OA\Property(property="tags", type="array", @OA\Items(ref="#/components/schemas/TagResource"), description="Tags associated with the book"),
 *     @OA\Property(property="genres", type="array", @OA\Items(ref="#/components/schemas/GenreResource"), description="Genres associated with the book"),
 *     @OA\Property(property="chapters", type="array", @OA\Items(ref="#/components/schemas/ChapterResource"), description="Chapters of the book"),
 * )
 */
class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed> The transformed book resource.
     */
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => new AuthorResource($this->whenLoaded('author')),
            'publisher' => new PublisherResource($this->whenLoaded('publisher')),
            'series' => new SeriesResource($this->whenLoaded('series')),
            'published_date' => $this->published_date,
            'edition' => $this->edition,
            'locale' => $this->locale,
            'suggested_citation' => $this->suggested_citation,
            'cover_image_url' => $this->cover_image_url,
            'summary' => $this->summary,
            'number_in_series' => $this->number_in_series,
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'genres' => GenreResource::collection($this->whenLoaded('genres')),
            'chapters' => ChapterResource::collection($this->whenLoaded('chapters')),
        ];
    }
}
