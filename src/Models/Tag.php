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
 * A tag associated with multiple books.
 *
 * @property string             $tag_id Primary key of the tag in UUID format.
 * @property string             $name   Name of the tag.
 * @property-read HasMany<Book> $books  The books associated with this tag.
 *
 * @mixin Builder
 *
 * @OA\Schema(
 *     title="Tag",
 *     description="A tag model",
 *     @OA\Property(property="tag_id", type="string", description="Primary key of the tag in UUID format"),
 *     @OA\Property(property="name", type="string", description="Name of the tag"),
 *     @OA\Property(
 *         property="books",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Book"),
 *         description="The books associated with this tag"
 *     )
 * )
 */
class Tag extends Model
{
    use HasUuids;
    use HasFactory;

    public const TABLE_NAME = BibliotecaConstants::TABLE_PREFIX . 'tags';

    protected $table = self::TABLE_NAME;

    protected $primaryKey = 'tag_id';

    protected $fillable = [
        'name',
    ];

    /**
     * Get the books associated with this tag.
     *
     * @return BelongsToMany<Book>
     */
    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, BookTag::TABLE_NAME, 'tag_id', 'book_id');
    }
}
