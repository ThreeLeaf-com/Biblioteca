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
 * @property string                $book_id               The book's unique ID.
 * @property string                $title                 The title of the book.
 * @property string|null           $subtitle              The subtitle of the book.
 * @property string                $author_id             The primary author's unique ID.
 * @property string                $publisher_id          The publisher's unique ID.
 * @property Carbon                $published_date        The publication date.
 * @property string|null           $isbn                  The ISBN of the book.
 * @property string|null           $edition               The book edition.
 * @property string                $locale                The locale /language of the book (e.g., en_US).
 * @property string|null           $suggested_citation    The suggested citation format for the book.
 * @property string|null           $cover_image_url       The URL of the book cover image.
 * @property string|null           $summary               The book summary.
 * @property-read Author           $author                The author associated with the book.
 * @property-read Publisher        $publisher             The publisher associated with the book.
 * @property-read Series           $series                The series the book belongs to.
 * @property-read HasMany<Chapter> $chapters              The chapters associated with the book.
 * @property-read HasMany<Tag>     $tags                  The tags associated with the book.
 * @property-read HasMany<Genre>   $genres                The genres associated with the book.
 *
 * @mixin Builder
 *
 * @OA\Schema(
 *     title="Book",
 *     description="A book model",
 *     @OA\Property(property="book_id", type="string", description="Primary key of the book in UUID format"),
 *     @OA\Property(property="title", type="string", description="The title of the book"),
 *     @OA\Property(property="subtitle", type="string", description="The subtitle of the book"),
 *     @OA\Property(property="author_id", type="string", description="The primary author"s unique ID"),
 *     @OA\Property(property="publisher_id", type="string", description="The publisher"s unique ID"),
 *     @OA\Property(property="published_date", type="string", format="date", description="Publication date of the book"),
 *     @OA\Property(property="edition", type="string", description="Edition of the book"),
 *     @OA\Property(property="isbn", type="string", description="ISBN of the book"),
 *     @OA\Property(property="locale", type="string", description="Locale of the book (e.g., en_US)"),
 *     @OA\Property(property="suggested_citation", type="string", description="Suggested citation format for the book"),
 *     @OA\Property(property="cover_image_url", type="string", description="URL of the book cover image"),
 *     @OA\Property(property="summary", type="string", description="A brief summary of the book"),
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
        'publisher_id',
        'published_date',
        'isbn',
        'edition',
        'locale',
        'suggested_citation',
        'cover_image_url',
        'summary',
        'series_id',
        'number_in_series',
    ];

    protected $casts = [
        'published_date' => 'date',
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
     * @return BelongsToMany<Series>
     */
    public function series(): BelongsToMany
    {
        return $this->belongsToMany(Series::class, 'b_series_books', 'book_id', 'series_id')
            ->withPivot('number')
            ->withTimestamps();
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
        return $this->belongsToMany(Tag::class, BookTag::TABLE_NAME, 'book_id', 'tag_id')
            ->using(BookTag::class)
            ->withTimestamps();
    }

    /**
     * Get the genres associated with this book.
     *
     * @return BelongsToMany<Genre>
     */
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, BookGenre::TABLE_NAME, 'book_id', 'genre_id')
            ->using(BookGenre::class)
            ->withTimestamps();
    }
}
