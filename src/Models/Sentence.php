<?php

namespace ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use ThreeLeaf\Biblioteca\Constants\BibliotecaConstants;
use ThreeLeaf\Biblioteca\Utils\UuidUtil;

/**
 * A sentence associated with a paragraph.
 *
 * @property string                     $sentence_id       Primary key of the sentence in UUID format.
 * @property string                     $paragraph_id      UUID of the associated paragraph.
 * @property int                        $sentence_number   Number of the sentence within the paragraph.
 * @property string                     $content           Content of the sentence.
 * @property-read Paragraph             $paragraph         The paragraph associated with the sentence.
 * @property-read MorphMany<Annotation> $annotations       The annotations associated with the sentence.
 *
 * @mixin Builder
 *
 * @OA\Schema(
 *     title="Sentence",
 *     description="A sentence model",
 *     @OA\Property(property="sentence_id", type="string", description="Primary key of the sentence in UUID format"),
 *     @OA\Property(property="paragraph_id", type="string", description="UUID of the associated paragraph"),
 *     @OA\Property(property="sentence_number", type="integer", description="Number of the sentence within the paragraph"),
 *     @OA\Property(property="content", type="string", description="Content of the sentence"),
 *     @OA\Property(
 *         property="paragraph",
 *         ref="#/components/schemas/Paragraph",
 *         description="The paragraph associated with the sentence"
 *     )
 * )
 */
class Sentence extends Model
{
    use HasUuids;
    use HasFactory;

    public const TABLE_NAME = BibliotecaConstants::TABLE_PREFIX . 'sentences';

    protected $table = self::TABLE_NAME;

    protected $primaryKey = 'sentence_id';

    protected $fillable = [
        'paragraph_id',
        'sentence_number',
        'content',
    ];

    /**
     * Get the sentence ID attribute.
     *
     * @return string
     */
    public function getSentenceIdAttribute(): string
    {
        if (empty($this->attributes['sentence_id'])) {
            $distinguishedName = "o=$this->paragraph_id,ou=$this->sentence_number";
            $this->attributes['sentence_id'] = UuidUtil::generateX500Uuid($distinguishedName);
        }

        return $this->attributes['sentence_id'];
    }

    /**
     * Get the paragraph associated with the sentence.
     *
     * @return BelongsTo<Paragraph>
     */
    public function paragraph(): BelongsTo
    {
        return $this->belongsTo(Paragraph::class, 'paragraph_id');
    }

    /**
     * Get the annotations associated with the sentence.
     *
     * This method retrieves all annotations that are associated with the sentence.
     * Annotations are polymorphic relationships, meaning they can be associated with
     * multiple models. In this case, the sentence can have many annotations.
     *
     * @return MorphMany<Annotation> A collection of annotations associated with the sentence.
     */
    public function annotations(): MorphMany
    {
        return $this->morphMany(Annotation::class, 'reference');
    }
}
