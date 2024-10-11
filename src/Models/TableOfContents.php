<?php

namespace ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ThreeLeaf\Biblioteca\Constants\BibliotecaConstants;

/**
 * A table of contents entry associated with a book and a chapter.
 *
 * @property string       $toc_id      Primary key of the table of contents entry in UUID format.
 * @property string       $book_id     UUID of the associated book.
 * @property string       $title       Title of the chapter/section in the table of contents.
 * @property string       $chapter_id  UUID of the associated chapter.
 * @property int          $page_number Page number of the chapter/section in the book.
 * @property-read Book    $book        The book associated with the table of contents entry.
 * @property-read Chapter $chapter     The chapter associated with the table of contents entry.
 *
 * @mixin Builder
 *
 * @OA\Schema(
 *     title="TableOfContents",
 *     description="A table of contents model",
 *     @OA\Property(property="toc_id", type="string", description="Primary key of the table of contents entry in UUID format"),
 *     @OA\Property(property="book_id", type="string", description="UUID of the associated book"),
 *     @OA\Property(property="title", type="string", description="Title of the chapter/section in the table of contents"),
 *     @OA\Property(property="chapter_id", type="string", description="UUID of the associated chapter"),
 *     @OA\Property(property="page_number", type="integer", description="Page number of the chapter/section in the book"),
 *     @OA\Property(
 *         property="book",
 *         ref="#/components/schemas/Book",
 *         description="The book associated with the table of contents entry"
 *     ),
 *     @OA\Property(
 *         property="chapter",
 *         ref="#/components/schemas/Chapter",
 *         description="The chapter associated with the table of contents entry"
 *     )
 * )
 */
class TableOfContents extends Model
{
    use HasUuids;
    use HasFactory;

    public const TABLE_NAME = BibliotecaConstants::TABLE_PREFIX . 'table_of_contents';

    protected $table = self::TABLE_NAME;

    protected $primaryKey = 'toc_id';

    protected $fillable = [
        'book_id',
        'title',
        'chapter_id',
        'page_number',
    ];

    /**
     * Get the book associated with the table of contents entry.
     *
     * @return BelongsTo<Book>
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    /**
     * Get the chapter associated with the table of contents entry.
     *
     * @return BelongsTo<Chapter>
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }
}
