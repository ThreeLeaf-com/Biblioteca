<?php

namespace ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ThreeLeaf\Biblioteca\Constants\BibliotecaConstants;

/**
 * A series of books associated with an author.
 *
 * @property string             $series_id   Primary key of the series in UUID format.
 * @property string             $name        Name of the series.
 * @property string             $description Description of the series.
 * @property string             $author_id   UUID of the associated author.
 * @property-read Author        $author      The author associated with the series.
 * @property-read HasMany<Book> $books       The books associated with this series.
 *
 * @mixin Builder
 *
 * @OA\Schema(
 *     title="Series",
 *     description="A series model",
 *     @OA\Property(property="series_id", type="string", description="Primary key of the series in UUID format"),
 *     @OA\Property(property="name", type="string", description="Name of the series"),
 *     @OA\Property(property="description", type="string", description="Description of the series"),
 *     @OA\Property(property="author_id", type="string", description="UUID of the associated author"),
 *     @OA\Property(
 *         property="author",
 *         ref="#/components/schemas/Author",
 *         description="The author associated with the series"
 *     ),
 *     @OA\Property(
 *         property="books",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Book"),
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
        'name',
        'description',
        'author_id',
    ];

    /**
     * Get the author associated with the series.
     *
     * @return BelongsTo<Author>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'author_id');
    }

    /**
     * Get the books associated with this series.
     *
     * @return HasMany<Book>
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'series_id');
    }
}
