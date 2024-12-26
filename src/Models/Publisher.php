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
 * A publisher associated with multiple books.
 *
 * @property string             $publisher_id  Primary key of the publisher in UUID format.
 * @property string             $name          Name of the publisher.
 * @property string             $address       Address of the publisher.
 * @property string             $website       Website of the publisher.
 * @property-read HasMany<Book> $books         The books associated with the publisher.
 *
 * @mixin Builder
 *
 * @OA\Schema(
 *     title="Publisher",
 *     description="A publisher model",
 *     required={"name"},
 *     @OA\Property(property="publisher_id", type="string", description="Primary key of the publisher in UUID format"),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the publisher",
 *         uniqueItems=true
 *     ),
 *     @OA\Property(property="address", type="string", description="Address of the publisher"),
 *     @OA\Property(property="website", type="string", description="Website of the publisher"),
 *     @OA\Property(
 *         property="books",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Book"),
 *         description="The books associated with the publisher"
 *     )
 * )
 */
class Publisher extends Model
{
    use HasUuids;
    use HasFactory;

    public const TABLE_NAME = BibliotecaConstants::TABLE_PREFIX . 'publishers';

    protected $table = self::TABLE_NAME;

    protected $primaryKey = 'publisher_id';

    protected $fillable = [
        'name',
        'address',
        'website',
    ];

    /**
     * Get the publisher ID attribute.
     *
     * @return string
     */
    public function getPublisherIdAttribute(): string
    {
        if (empty($this->attributes['publisher_id'])) {
            $distinguishedName = "cn=$this->name";
            $this->attributes['publisher_id'] = UuidUtil::generateX500Uuid($distinguishedName);
        }

        return $this->attributes['publisher_id'];
    }

    /**
     * Get the books associated with the publisher.
     *
     * @return HasMany<Book>
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'publisher_id');
    }
}
