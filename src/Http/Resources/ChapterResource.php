<?php

namespace ThreeLeaf\Biblioteca\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use ThreeLeaf\Biblioteca\Models\Chapter;

/**
 * The {@link Chapter} {@link JsonResource} class used to shape API output.
 *
 * This resource provides a structured representation of a chapter, including
 * its key attributes like title, summary, and related paragraphs and figures.
 *
 * @mixin Chapter
 *
 * @OA\Schema(
 *     schema="ChapterResource",
 *     @OA\Property(property="chapter_id", type="string", example="b1234567-89ab-cdef-0123-456789abcdef", description="UUID of the chapter"),
 *     @OA\Property(property="book_id", type="string", example="f7f9d3e0-434b-11ed-b878-0242ac120002", description="UUID of the associated book"),
 *     @OA\Property(property="chapter_number", type="integer", example=1, description="Number of the chapter in the book"),
 *     @OA\Property(property="title", type="string", example="The Mysterious Beginning", description="Title of the chapter"),
 *     @OA\Property(property="summary", type="string", example="This chapter introduces the main mystery.", description="A brief summary of the chapter"),
 *     @OA\Property(property="content", type="string", example="This is a paragraph.", description="The chapter content"),
 *     @OA\Property(property="chapter_image_url", type="string", example="https://example.com/chapter1.jpg", description="URL of the chapter’s image"),
 *     @OA\Property(
 *         property="paragraphs",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/ParagraphResource"),
 *         description="Paragraphs in this chapter"
 *     ),
 *     @OA\Property(
 *         property="figures",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/FigureResource"),
 *         description="Figures in this chapter"
 *     ),
 * )
 */
class ChapterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed> The transformed chapter resource.
     */
    public function toArray(Request $request): array
    {
        return [
            'chapter_id' => $this->chapter_id,
            'book_id' => $this->book_id,
            'chapter_number' => $this->chapter_number,
            'title' => $this->title,
            'summary' => $this->summary,
            'content' => $this->content,
            'chapter_image_url' => $this->chapter_image_url,
            'paragraphs' => ParagraphResource::collection($this->whenLoaded('paragraphs')),
        ];
    }
}
