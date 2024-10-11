<?php

namespace ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ThreeLeaf\Biblioteca\Constants\BibliotecaConstants;

/**
 * A bibliography entry associated with a book.
 *
 * @property string    $bibliography_id Primary key of the bibliography entry in UUID format.
 * @property string    $book_id         UUID of the associated book.
 * @property string    $content         Content of the bibliography entry.
 * @property-read Book $book            The book associated with the bibliography entry.
 *
 * @mixin Builder
 *
 * @OA\Schema(
 *     title="Bibliography",
 *     description="A bibliography entry model",
 *     @OA\Property(property="bibliography_id", type="string", description="Primary key of the bibliography entry in UUID format"),
 *     @OA\Property(property="book_id", type="string", description="UUID of the associated book"),
 *     @OA\Property(property="content", type="string", description="Content of the bibliography entry"),
 *     @OA\Property(
 *         property="book",
 *         ref="#/components/schemas/Book",
 *         description="The book associated with the bibliography entry"
 *     )
 * )
 */
class Bibliography extends Model
{
    use HasUuids;
    use HasFactory;

    public const TABLE_NAME = BibliotecaConstants::TABLE_PREFIX . 'bibliographies';

    protected $table = self::TABLE_NAME;

    protected $primaryKey = 'bibliography_id';

    protected $fillable = [
        'book_id',
        'content',
    ];

    /**
     * Get the book associated with the bibliography entry.
     *
     * @return BelongsTo<Book>
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
}
