<?php

namespace ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ThreeLeaf\Biblioteca\Constants\Biblioteca;

/**
 * A paragraph associated with a chapter.
 *
 * @property string                 $paragraph_id      Primary key of the paragraph in UUID format.
 * @property string                 $chapter_id        UUID of the associated chapter.
 * @property int                    $paragraph_number  Number of the paragraph in the chapter.
 * @property string                 $content           Content of the paragraph.
 * @property-read Chapter           $chapter           The chapter associated with the paragraph.
 * @property-read HasMany<Sentence> $sentences         The sentences associated with the paragraph.
 *
 * @mixin Builder
 *
 * @OA\Schema(
 *     title="Paragraph",
 *     description="A paragraph model",
 *     @OA\Property(property="paragraph_id", type="string", description="Primary key of the paragraph in UUID format"),
 *     @OA\Property(property="chapter_id", type="string", description="UUID of the associated chapter"),
 *     @OA\Property(property="paragraph_number", type="integer", description="Number of the paragraph in the chapter"),
 *     @OA\Property(property="content", type="string", description="Content of the paragraph"),
 *     @OA\Property(
 *         property="chapter",
 *         ref="#/components/schemas/Chapter",
 *         description="The chapter associated with the paragraph"
 *     ),
 *     @OA\Property(
 *         property="sentences",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Sentence"),
 *         description="The sentences associated with the paragraph"
 *     )
 * )
 */
class Paragraph extends Model
{
    use HasUuids;
    use HasFactory;

    public const TABLE_NAME = Biblioteca::TABLE_PREFIX . 'paragraphs';

    protected $table = self::TABLE_NAME;

    protected $primaryKey = 'paragraph_id';

    protected $fillable = [
        'chapter_id',
        'paragraph_number',
        'content',
    ];

    /**
     * Get the chapter associated with the paragraph.
     *
     * @return BelongsTo<Chapter>
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }

    /**
     * Get the sentences associated with the paragraph.
     *
     * @return HasMany<Sentence>
     */
    public function sentences(): HasMany
    {
        return $this->hasMany(Sentence::class, 'sentence_id');
    }
}
