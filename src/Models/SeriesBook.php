<?php

namespace ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ThreeLeaf\Biblioteca\Constants\BibliotecaConstants;
use ThreeLeaf\Biblioteca\Traits\HasCompositeKey;

/**
 * A {@link Book} within a {@link Series}.
 *
 * @property string      $series_id    The series unique ID.
 * @property string      $book_id      The book unique ID.
 * @property int         $number       The number of the book within the series.
 * @property-read Series $series       The series.
 * @property-read Book   $book         The book.
 *
 * @mixin Builder
 *
 * @OA\Schema(
 *     schema="SeriesBook",
 *     type="object",
 *     title="SeriesBook",
 *     description="The association between a book and a series",
 *     required={"series_id", "book_id", "number"},
 *     @OA\Property(
 *         property="series_id",
 *         type="string",
 *         description="Unique identifier for the series"
 *     ),
 *     @OA\Property(
 *         property="book_id",
 *         type="string",
 *         description="Unique identifier for the book"
 *     ),
 *     @OA\Property(
 *         property="number",
 *         type="integer",
 *         description="Position of the book in the series"
 *     )
 * )
 */
class SeriesBook extends Model
{
    use HasFactory;
    use HasCompositeKey;

    public const TABLE_NAME = BibliotecaConstants::TABLE_PREFIX . 'series_books';

    protected $table = self::TABLE_NAME;

    /** @var string[] composite primary key. */
    protected array $primaryKeys = ['series_id', 'book_id'];

    protected $fillable = [
        'series_id',
        'book_id',
        'number',
    ];

    public static function getNextNumberForSeries($seriesId): int
    {
        return static::where('series_id', $seriesId)->max('number') + 1;
    }

    /**
     * Get the series.
     *
     * @return BelongsTo<Series>
     */
    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class, 'series_id');
    }

    /**
     * Get the book.
     *
     * @return BelongsTo<Book>
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
}
