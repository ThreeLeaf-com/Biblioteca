<?php

namespace ThreeLeaf\Biblioteca\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use ThreeLeaf\Biblioteca\Models\Annotation;
use ThreeLeaf\Biblioteca\Models\Paragraph;

/**
 * The {@link Annotation} {@link JsonResource} class used to shape API output.
 *
 * This resource provides a structured representation of an annotation, including
 * its key attributes like content and reference information.
 *
 * @mixin Annotation
 *
 * @OA\Schema(
 *     schema="AnnotationResource",
 *     @OA\Property(property="annotation_id", type="string", example="b1234567-89ab-cdef-0123-456789abcdef", description="UUID of the annotation"),
 *     @OA\Property(property="reference_id", type="string", example="f7f9d3e0-434b-11ed-b878-0242ac120002", description="UUID of the associated paragraph or sentence"),
 *     @OA\Property(property="reference_type", type="string", example="\ThreeLeaf\Biblioteca\Models\Sentence", description="Type of the referenced entity"),
 *     @OA\Property(property="content", type="string", example="This is an annotation explaining the text.", description="Content of the annotation"),
 *     @OA\Property(
 *         property="reference",
 *         oneOf={
 *             @OA\Schema(ref="#/components/schemas/ParagraphResource"),
 *             @OA\Schema(ref="#/components/schemas/SentenceResource")
 *         },
 *         description="The reference to the paragraph or sentence associated with this annotation"
 *     ),
 * )
 */
class AnnotationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed> The transformed annotation resource.
     */
    public function toArray(Request $request): array
    {
        return [
            'annotation_id' => $this->annotation_id,
            'reference_id' => $this->reference_id,
            'reference_type' => $this->reference_type,
            'content' => $this->content,
            'reference' => $this->whenLoaded('reference', function () {
                return $this->reference instanceof Paragraph
                    ? new ParagraphResource($this->reference)
                    : new SentenceResource($this->reference);
            }),
        ];
    }
}
