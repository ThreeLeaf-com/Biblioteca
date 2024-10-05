<?php

namespace ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ThreeLeaf\Biblioteca\Constants\Biblioteca;

/**
 * A sentence associated with a paragraph.
 *
 * @property string         $sentence_id     Primary key of the sentence in UUID format.
 * @property string         $paragraph_id    UUID of the associated paragraph.
 * @property int            $sentence_number Number of the sentence within the paragraph.
 * @property string         $content         Content of the sentence.
 * @property-read Paragraph $paragraph       The paragraph associated with the sentence.
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

    public const TABLE_NAME = Biblioteca::TABLE_PREFIX . 'sentences';

    protected $table = self::TABLE_NAME;

    protected $primaryKey = 'sentence_id';

    protected $fillable = [
        'paragraph_id',
        'sentence_number',
        'content',
    ];

    /**
     * Get the paragraph associated with the sentence.
     *
     * @return BelongsTo<Paragraph>
     */
    public function paragraph(): BelongsTo
    {
        return $this->belongsTo(Paragraph::class, 'paragraph_id');
    }
}
