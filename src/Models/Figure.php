<?php

namespace ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ThreeLeaf\Biblioteca\Constants\Biblioteca;

/**
 * A figure associated with a chapter.
 *
 * @property string       $figure_id     Primary key of the figure in UUID format.
 * @property string       $chapter_id    UUID of the associated chapter.
 * @property string       $figure_label  Alphanumeric label of the figure.
 * @property string       $caption       Caption of the figure.
 * @property string       $image_url     URL of the figure image.
 * @property string       $description   Description of the figure.
 * @property-read Chapter $chapter       The chapter associated with the figure.
 *
 * @mixin Builder
 *
 * @OA\Schema(
 *     title="Figure",
 *     description="A figure model",
 *     @OA\Property(property="figure_id", type="string", description="Primary key of the figure in UUID format"),
 *     @OA\Property(property="chapter_id", type="string", description="UUID of the associated chapter"),
 *     @OA\Property(property="figure_label", type="string", description="Alphanumeric label of the figure"),
 *     @OA\Property(property="caption", type="string", description="Caption of the figure"),
 *     @OA\Property(property="image_url", type="string", description="URL of the figure image"),
 *     @OA\Property(property="description", type="string", description="Description of the figure"),
 *     @OA\Property(
 *         property="chapter",
 *         ref="#/components/schemas/Chapter",
 *         description="The chapter associated with the figure"
 *     )
 * )
 */
class Figure extends Model
{
    use HasUuids;
    use HasFactory;

    public const TABLE_NAME = Biblioteca::TABLE_PREFIX . 'figures';

    protected $table = self::TABLE_NAME;

    protected $primaryKey = 'figure_id';

    protected $fillable = [
        'chapter_id',
        'figure_label',
        'caption',
        'description',
        'image_url',
    ];

    /**
     * Get the chapter associated with the figure.
     *
     * @return BelongsTo<Chapter>
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }
}
