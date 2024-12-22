<?php

namespace ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ThreeLeaf\Biblioteca\Constants\BibliotecaConstants;
use ThreeLeaf\Biblioteca\Utils\UuidUtil;

/**
 * Author of books.
 *
 * @property string             $author_id        Primary key of the author in UUID format
 * @property string             $first_name       First name of the author
 * @property string             $last_name        Last name of the author
 * @property string             $biography        A brief biography of the author
 * @property string             $author_image_url URL of the author's image
 * @property-read HasMany<Book> $books            Collection of books associated with this author
 *
 * @mixin Builder
 *
 * @OA\Schema(
 *     schema="Author",
 *     title="Author",
 *     description="An author of books",
 *     required={"first_name", "last_name"},
 *     @OA\Property(property="author_id", type="string", description="Primary key of the author in UUID format"),
 *     @OA\Property(property="first_name", type="string", description="First name of the author"),
 *     @OA\Property(property="last_name", type="string", description="Last name of the author"),
 *     @OA\Property(property="biography", type="string", description="A brief biography of the author"),
 *     @OA\Property(property="author_image_url", type="string", description="URL of the author's image"),
 *     @OA\Property(
 *         property="books",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Book"),
 *         description="Collection of books associated with this author"
 *     )
 * )
 */
class Author extends Model
{
    use HasUuids;
    use HasFactory;

    public const TABLE_NAME = BibliotecaConstants::TABLE_PREFIX . 'authors';

    protected $table = self::TABLE_NAME;

    protected $primaryKey = 'author_id';

    protected $fillable = [
        'first_name',
        'last_name',
        'biography',
        'author_image_url',
    ];

    /**
     * Boot the model and attach event listeners to handle UUID generation.
     *
     * This method overrides the boot method in the HasUuids trait and attaches a creating event listener.
     */
    protected static function boot(): void
    {
        parent::boot();

        /**
         * When a new author is being created, a deterministic UUID is generated using the author's name.
         *
         * @param Closure $callback The callback function to be executed when a new author is being created.
         */
        static::creating(function (/** @var Author $author */ $author) {
            $distinguishedName = "sn=$author->last_name,givenName=$author->first_name";
            $author->author_id = UuidUtil::generateX500Uuid($distinguishedName);
        });
    }

    /**
     * Get the books associated with the author.
     *
     * @return HasMany<Book>
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'book_id');
    }
}
