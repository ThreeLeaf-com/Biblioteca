<?php

namespace ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ThreeLeaf\Biblioteca\Constants\BibliotecaConstants;
use ThreeLeaf\Biblioteca\Traits\Equals;
use ThreeLeaf\Biblioteca\Utils\UuidUtil;

/**
 * A chapter associated with a book.
 *
 * @property string                  $chapter_id        The chapter's unique ID.
 * @property string                  $book_id           The unique ID of the associated book.
 * @property int                     $chapter_number    Number of the chapter in the book.
 * @property string                  $title             Title of the chapter.
 * @property string                  $summary           A brief summary of the chapter.
 * @property string                  $chapter_image_url URL of the chapter’s image.
 * @property string                  $content           Content of the chapter.
 * @property-read Book               $book              The book associated with the chapter.
 * @property-read HasMany<Paragraph> $paragraphs        The paragraphs associated with the chapter.
 * @property-read HasMany<Figure>    $figures           The figures associated with the chapter.
 *
 * @mixin Builder
 *
 * @OA\Schema(
 *     title="Chapter",
 *     description="A chapter model",
 *     required={"book_id"},
 *     @OA\Property(property="chapter_id", type="string", description="Primary key of the chapter in UUID format"),
 *     @OA\Property(property="book_id", type="string", description="UUID of the associated book"),
 *     @OA\Property(property="chapter_number", type="integer", description="Number of the chapter in the book"),
 *     @OA\Property(property="title", type="string", description="Title of the chapter (Defaults to Chapter $chapter_number)"),
 *     @OA\Property(property="summary", type="string", description="A brief summary of the chapter"),
 *     @OA\Property(property="chapter_image_url", type="string", description="URL of the chapter’s image"),
 *     @OA\Property(property="content", type="string", description="Content of the chapter"),
 *     @OA\Property(
 *         property="book",
 *         ref="#/components/schemas/Book",
 *         description="The book associated with the chapter"
 *     ),
 *     @OA\Property(
 *         property="paragraphs",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Paragraph"),
 *         description="The paragraphs associated with the chapter"
 *     )
 * )
 */
class Chapter extends Model
{
    use HasUuids;
    use HasFactory;
    use Equals;

    public const TABLE_NAME = BibliotecaConstants::TABLE_PREFIX . 'chapters';

    protected $table = self::TABLE_NAME;

    protected $primaryKey = 'chapter_id';

    protected $fillable = [
        'book_id',
        'chapter_number',
        'title',
        'summary',
        'chapter_image_url',
        'content',
    ];

    /**
     * Get the chapter ID attribute.
     *
     * @return string
     */
    public function getChapterIdAttribute(): string
    {
        if (empty($this->attributes['chapter_id'])) {
            $distinguishedName = "cn=$this->title,o=$this->book_id,ou=$this->chapter_number";
            $this->attributes['chapter_id'] = UuidUtil::generateX500Uuid($distinguishedName);
        }

        return $this->attributes['chapter_id'];
    }

    /**
     * Get the book associated with the chapter.
     *
     * @return BelongsTo<Book>
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    /**
     * Get the paragraphs associated with the chapter.
     *
     * @return HasMany<Paragraph>
     */
    public function paragraphs(): HasMany
    {
        return $this->hasMany(Paragraph::class, 'chapter_id')->orderBy('paragraph_number');
    }
}
