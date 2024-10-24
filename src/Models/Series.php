<?php

namespace ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use ThreeLeaf\Biblioteca\Constants\BibliotecaConstants;

/**
 * A series of books associated with an author.
 *
 * @property string                   $series_id    The series unique ID.
 * @property string                   $title        The title of the series.
 * @property string                   $subtitle     The subtitle of the series.
 * @property string                   $description  The description of the series.
 * @property string                   $editor_id    The unique ID of the editor or author.
 * @property-read Author              $editor       The editor or author associated with the series.
 * @property-read HasMany<SeriesBook> $books        The books associated with this series.
 *
 * @mixin Builder
 *
 * @OA\Schema(
 *     title="Series",
 *     description="A series model",
 *     @OA\Property(property="series_id", type="string", description="The series unique ID"),
 *     @OA\Property(property="title", type="string", description="The title of the series"),
 *     @OA\Property(property="subtitle", type="string", description="The subtitle of the series"),
 *     @OA\Property(property="description", type="string", description="The series desccription"),
 *     @OA\Property(property="editor_id", type="string", description="The editor or author unique ID"),
 *     @OA\Property(
 *         property="editor",
 *         ref="#/components/schemas/Author",
 *         description="The editor or author associated with the series"
 *     ),
 *     @OA\Property(
 *         property="books",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/SeriesBook"),
 *         description="The books associated with this series"
 *     )
 * )
 */
class Series extends Model
{
    use HasUuids;
    use HasFactory;

    public const TABLE_NAME = BibliotecaConstants::TABLE_PREFIX . 'series';

    protected $table = self::TABLE_NAME;

    protected $primaryKey = 'series_id';

    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'editor_id',
    ];

    /**
     * Get the author associated with the series.
     *
     * @return BelongsTo<Author>
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'editor_id');
    }

    /**
     * Get the books associated with this series.
     *
     * @return BelongsToMany<Book>
     */
    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'b_series_books', 'series_id', 'book_id')
            ->withPivot('number')
            ->withTimestamps();
    }

    /**
     * Attach a book to the series with auto-incremented number.
     *
     * @param string $bookId
     *
     * @return void
     */
    public function attachBook(string $bookId): void
    {
        $nextNumber = SeriesBook::getNextNumberForSeries($this->series_id);

        $this->books()->attach($bookId, ['number' => $nextNumber]);
    }

    /**
     * Reorder books within the series based on the given book IDs array.
     *
     * @param array $orderedBookIds Array of book IDs in the desired order.
     *
     * @return void
     */
    public function reorderBooks(array $orderedBookIds): void
    {
        DB::transaction(function () use ($orderedBookIds) {
            foreach ($orderedBookIds as $index => $bookId) {
                $this->books()->updateExistingPivot($bookId, ['number' => $index + 1]);
            }
        });
    }
}
