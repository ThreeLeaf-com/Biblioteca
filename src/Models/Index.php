<?php

namespace ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ThreeLeaf\Biblioteca\Constants\Biblioteca;

/**
 * An index entry associated with a book.
 *
 * @property string    $index_id    Primary key of the index entry in UUID format.
 * @property string    $book_id     UUID of the associated book.
 * @property string    $term        Indexed term.
 * @property int       $page_number Page number where the term is located.
 * @property-read Book $book        The book associated with the index entry.
 *
 * @mixin Builder
 *
 * @OA\Schema(
 *     title="Index",
 *     description="An index entry model",
 *     @OA\Property(property="index_id", type="string", description="Primary key of the index entry in UUID format"),
 *     @OA\Property(property="book_id", type="string", description="UUID of the associated book"),
 *     @OA\Property(property="term", type="string", description="Indexed term"),
 *     @OA\Property(property="page_number", type="integer", description="Page number where the term is located"),
 *     @OA\Property(
 *         property="book",
 *         ref="#/components/schemas/Book",
 *         description="The book associated with the index entry"
 *     )
 * )
 */
class Index extends Model
{
    use HasUuids;
    use HasFactory;

    public const TABLE_NAME = Biblioteca::TABLE_PREFIX . 'indices';

    protected $table = self::TABLE_NAME;

    protected $primaryKey = 'index_id';

    protected $fillable = [
        'book_id',
        'term',
        'page_number',
    ];

    /**
     * Get the book associated with the index entry.
     *
     * @return BelongsTo<Book>
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
}
