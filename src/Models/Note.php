<?php

namespace ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ThreeLeaf\Biblioteca\Constants\Biblioteca;
use ThreeLeaf\Biblioteca\Enums\Context;
use ThreeLeaf\Biblioteca\Enums\NoteType;

/**
 * A note associated with a sentence, identified by an alphanumeric label.
 *
 * @property string        $note_id     Primary key of the note in UUID format.
 * @property string        $sentence_id UUID of the associated sentence.
 * @property string        $content     Content of the note.
 * @property string        $note_label  Alphanumeric label of the note.
 * @property NoteType      $note_type   Type of the note (citation, explanation, reference).
 * @property Context       $context     The context in which the note appears (page, chapter, book).
 * @property-read Sentence $sentence    The sentence associated with the note.
 *
 * @mixin Builder
 *
 * @OA\Schema(
 *     title="Note",
 *     description="A note model",
 *     @OA\Property(property="note_id", type="string", description="Primary key of the note in UUID format"),
 *     @OA\Property(property="sentence_id", type="string", description="UUID of the associated sentence"),
 *     @OA\Property(property="content", type="string", description="Content of the note"),
 *     @OA\Property(property="note_label", type="string", description="Alphanumeric label of the note"),
 *     @OA\Property(property="note_type", ref="#/components/schemas/NoteType", description="Type of the note"),
 *     @OA\Property(property="context", ref="#/components/schemas/Context", description="Context in which the note appears"),
 *     @OA\Property(
 *         property="sentence",
 *         ref="#/components/schemas/Sentence",
 *         description="The sentence associated with the note"
 *     )
 * )
 */
class Note extends Model
{
    use HasUuids;
    use HasFactory;

    public const TABLE_NAME = Biblioteca::TABLE_PREFIX . 'notes';

    protected $table = self::TABLE_NAME;

    protected $primaryKey = 'note_id';

    protected $fillable = [
        'sentence_id',
        'content',
        'note_label',
        'note_type',
        'context',
    ];

    /**
     * Get the sentence associated with the note.
     *
     * @return BelongsTo<Sentence>
     */
    public function sentence(): BelongsTo
    {
        return $this->belongsTo(Sentence::class, 'sentence_id');
    }
}
