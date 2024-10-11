<?php

namespace ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ThreeLeaf\Biblioteca\Constants\BibliotecaConstants;

/**
 * A genre that can be associated with multiple books.
 *
 * @property string             $genre_id     Primary key of the genre in UUID format.
 * @property string             $name         Name of the genre.
 * @property string             $description  Description of the genre.
 * @property-read HasMany<Book> $books        The books associated with this genre.
 *
 * @mixin Builder
 *
 * @OA\Schema(
 *     title="Genre",
 *     description="A genre model",
 *     @OA\Property(property="genre_id", type="string", description="Primary key of the genre in UUID format"),
 *     @OA\Property(property="name", type="string", description="Name of the genre"),
 *     @OA\Property(property="description", type="string", description="Description of the genre"),
 *     @OA\Property(
 *         property="books",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Book"),
 *         description="The books associated with this genre"
 *     )
 * )
 */
class Genre extends Model
{
    use HasUuids;
    use HasFactory;

    public const TABLE_NAME = BibliotecaConstants::TABLE_PREFIX . 'genres';

    protected $table = self::TABLE_NAME;

    protected $primaryKey = 'genre_id';

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the books associated with this genre.
     *
     * @return BelongsToMany<Book>
     */
    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class);
    }
}
