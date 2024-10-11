<?php

namespace ThreeLeaf\Biblioteca\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ThreeLeaf\Biblioteca\Constants\BibliotecaConstants;

/**
 * A book with chapters, associated with an author and publisher.
 *
 * @property string                $book_id            Primary key of the book in UUID format.
 * @property string                $title              Title of the book.
 * @property string                $author_id          UUID of the author.
 * @property Carbon                $published_date     Publication date of the book.
 * @property string                $isbn               ISBN of the book.
 * @property string                $publisher_id       UUID of the publisher.
 * @property string                $edition            Edition of the book.
 * @property string                $locale             Locale of the book (e.g., en_US).
 * @property string                $suggested_citation Suggested citation format for the book.
 * @property string                $cover_image_url    URL of the book cover image.
 * @property string                $summary            A brief summary of the book.
 * @property int                   $number_in_series   The book’s number in a series (if applicable).
 * @property-read Author           $author             The author associated with the book.
 * @property-read Publisher        $publisher          The publisher associated with the book.
 * @property-read Series           $series             The series the book belongs to.
 * @property-read HasMany<Chapter> $chapters           The chapters associated with the book.
 * @property-read HasMany<Tag>     $tags               The tags associated with the book.
 * @property-read HasMany<Genre>   $genres             The genres associated with the book.
 *
 * @mixin Builder
 *
 * @OA\Schema(
 *     title="Book",
 *     description="A book model",
 *     @OA\Property(property="book_id", type="string", description="Primary key of the book in UUID format"),
 *     @OA\Property(property="title", type="string", description="Title of the book"),
 *     @OA\Property(property="author_id", type="string", description="UUID of the author"),
 *     @OA\Property(property="published_date", type="string", format="date", description="Publication date of the book"),
 *     @OA\Property(property="isbn", type="string", description="ISBN of the book"),
 *     @OA\Property(property="publisher_id", type="string", description="UUID of the publisher"),
 *     @OA\Property(property="edition", type="string", description="Edition of the book"),
 *     @OA\Property(property="locale", type="string", description="Locale of the book (e.g., en_US)"),
 *     @OA\Property(property="suggested_citation", type="string", description="Suggested citation format for the book"),
 *     @OA\Property(property="cover_image_url", type="string", description="URL of the book cover image"),
 *     @OA\Property(property="summary", type="string", description="A brief summary of the book"),
 *     @OA\Property(property="series_id", type="string", description="The series ID of the series this book belongs to"),
 *     @OA\Property(property="number_in_series", type="integer", description="The book’s number in a series (if applicable)"),
 *     @OA\Property(
 *         property="author",
 *         ref="#/components/schemas/Author",
 *         description="The author associated with the book"
 *     ),
 *     @OA\Property(
 *         property="publisher",
 *         ref="#/components/schemas/Publisher",
 *         description="The publisher associated with the book"
 *     ),
 *     @OA\Property(
 *         property="series",
 *         ref="#/components/schemas/Series",
 *         description="The series the book belongs to"
 *     ),
 *     @OA\Property(
 *         property="chapters",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Chapter"),
 *         description="The chapters associated with the book"
 *     ),
 *     @OA\Property(
 *         property="tags",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Tag"),
 *         description="The tags associated with the book"
 *     ),
 *     @OA\Property(
 *         property="genres",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Genre"),
 *         description="The genres associated with the book"
 *     )
 * )
 */
class Book extends Model
{
    use HasUuids;
    use HasFactory;

    public const TABLE_NAME = BibliotecaConstants::TABLE_PREFIX . 'books';

    protected $table = self::TABLE_NAME;

    protected $primaryKey = 'book_id';

    protected $fillable = [
        'title',
        'author_id',
        'published_date',
        'isbn',
        'publisher_id',
        'edition',
        'locale',
        'suggested_citation',
        'cover_image_url',
        'summary',
        'series_id',
        'number_in_series',
    ];

    protected $casts = [
        'published_date' => 'date',  // Cast to a Carbon date instance
    ];

    /**
     * Get the author associated with the book.
     *
     * @return BelongsTo<Author>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'author_id');
    }

    /**
     * Get the publisher associated with the book.
     *
     * @return BelongsTo<Publisher>
     */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class, 'publisher_id');
    }

    /**
     * Get the series the book belongs to.
     *
     * @return BelongsTo<Series>
     */
    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class, 'series_id');
    }

    /**
     * Get the chapters associated with the book.
     *
     * @return HasMany<Chapter>
     */
    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class, 'book_id');
    }

    /**
     * Get the tags associated with this book.
     *
     * @return BelongsToMany<Tag>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, BookTag::TABLE_NAME, 'book_id', 'tag_id');
    }

    /**
     * Get the genres associated with this book.
     *
     * @return BelongsToMany<Genre>
     */
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, BookGenre::TABLE_NAME, 'book_id', 'genre_id');
    }
}
