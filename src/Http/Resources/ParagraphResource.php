<?php

namespace ThreeLeaf\Biblioteca\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use ThreeLeaf\Biblioteca\Models\Paragraph;

/**
 * The {@link Paragraph} {@link JsonResource} class used to shape API output.
 *
 * This resource provides a structured representation of a paragraph, including
 * its key attributes like content, chapter, and related sentences.
 *
 * @mixin Paragraph
 *
 * @OA\Schema(
 *     schema="ParagraphResource",
 *     @OA\Property(property="paragraph_id", type="string", example="b1234567-89ab-cdef-0123-456789abcdef", description="UUID of the paragraph"),
 *     @OA\Property(property="chapter_id", type="string", example="f7f9d3e0-434b-11ed-b878-0242ac120002", description="UUID of the associated chapter"),
 *     @OA\Property(property="paragraph_number", type="integer", example=1, description="Number of the paragraph in the chapter"),
 *     @OA\Property(property="content", type="string", example="This is the first paragraph of the chapter.", description="Content of the paragraph"),
 *     @OA\Property(
 *         property="sentences",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/SentenceResource"),
 *         description="Sentences in this paragraph"
 *     ),
 * )
 */
class ParagraphResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed> The transformed paragraph resource.
     */
    public function toArray(Request $request): array
    {
        return [
            'paragraph_id' => $this->paragraph_id,
            'chapter_id' => $this->chapter_id,
            'paragraph_number' => $this->paragraph_number,
            'content' => $this->content,
            'sentences' => SentenceResource::collection($this->whenLoaded('sentences')),
        ];
    }
}
