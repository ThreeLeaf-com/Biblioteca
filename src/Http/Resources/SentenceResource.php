<?php

namespace ThreeLeaf\Biblioteca\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use ThreeLeaf\Biblioteca\Models\Sentence;

/**
 * The {@link Sentence} {@link JsonResource} class used to shape API output.
 *
 * This resource provides a structured representation of a sentence, including
 * its key attributes like content and related paragraph.
 *
 * @mixin Sentence
 *
 * @OA\Schema(
 *     schema="SentenceResource",
 *     @OA\Property(property="sentence_id", type="string", example="b1234567-89ab-cdef-0123-456789abcdef", description="UUID of the sentence"),
 *     @OA\Property(property="paragraph_id", type="string", example="f7f9d3e0-434b-11ed-b878-0242ac120002", description="UUID of the associated paragraph"),
 *     @OA\Property(property="sentence_number", type="integer", example=1, description="Number of the sentence within the paragraph"),
 *     @OA\Property(property="content", type="string", example="This is the first sentence of the paragraph.", description="Content of the sentence"),
 * )
 */
class SentenceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed> The transformed sentence resource.
     */
    public function toArray(Request $request): array
    {
        return [
            'sentence_id' => $this->sentence_id,
            'paragraph_id' => $this->paragraph_id,
            'sentence_number' => $this->sentence_number,
            'content' => $this->content,
        ];
    }
}
