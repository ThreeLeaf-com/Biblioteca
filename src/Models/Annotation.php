<?php

namespace ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use ThreeLeaf\Biblioteca\Constants\BibliotecaConstants;
use ThreeLeaf\Biblioteca\Exceptions\InvalidReferenceTypeException;
use ThreeLeaf\Biblioteca\Relations\ReferenceMorphTo;

/**
 * Represents an annotation that can be applied to either a paragraph or sentence.
 *
 * @property string                  $annotation_id   Unique identifier for the annotation in UUID format.
 * @property string                  $reference_id    Reference UUID for the associated paragraph or sentence.
 * @property string                  $reference_type  The type/class of the referenced entity (paragraph or sentence).
 * @property string                  $content         The content of the annotation.
 * @property-read Paragraph|Sentence $reference       Reference to the paragraph or sentence associated with this annotation.
 *
 * @mixin Builder
 *
 * @OA\Schema(
 *     title="Annotation",
 *     description="An annotation applied to a paragraph or sentence",
 *     @OA\Property(property="annotation_id", type="string", description="Unique identifier for the annotation in UUID format"),
 *     @OA\Property(property="reference_id", type="string", description="Reference UUID for the associated paragraph or sentence"),
 *     @OA\Property(property="reference_type", type="string", example="ThreeLeaf\Biblioteca\Models\Sentence", description="The referenced entity: the canonical class name of a Paragraph or Sentence, a subclass of one, or a morph alias the host application has registered"),
 *     @OA\Property(property="content", type="string", description="The content of the annotation"),
 *     @OA\Property(
 *         property="reference",
 *         oneOf={
 *             @OA\Schema(ref="#/components/schemas/Sentence"),
 *             @OA\Schema(ref="#/components/schemas/Paragraph")
 *         },
 *         description="Reference to the paragraph or sentence associated with this annotation"
 *     )
 * )
 */
class Annotation extends Model
{

    use HasUuids;
    use HasFactory;

    public const TABLE_NAME = BibliotecaConstants::TABLE_PREFIX . 'annotations';

    /**
     * The only classes an annotation may reference.
     *
     * <code>reference_type</code> is the class Eloquent resolves when the polymorphic
     * reference is read, so the column decides what gets instantiated. Restricting it here
     * rather than only in {@link \ThreeLeaf\Biblioteca\Http\Requests\AnnotationRequest}
     * covers writes that never touch HTTP — <code>Annotation::create()</code> in host code,
     * for one — and rows written by a release that did not constrain the column.
     *
     * @var array<int, class-string<Model>>
     */
    public const REFERENCE_TYPES = [
        Paragraph::class,
        Sentence::class,
    ];

    public $timestamps = false;

    protected $table = self::TABLE_NAME;

    protected $primaryKey = 'annotation_id';

    protected $fillable = [
        'reference_id',
        'reference_type',
        'content',
    ];

    /**
     * Get the reference (paragraph or sentence) to which this annotation is attached.
     *
     * @return MorphTo<Paragraph|Sentence> The referenced paragraph or sentence.
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Reject a reference type that does not denote a permitted model.
     *
     * A leading backslash is accepted and stripped, because <code>\Foo\Bar</code> and
     * <code>Foo\Bar</code> name the same class. The value is then resolved through the
     * application's morph map before it is checked, so a host that has aliased
     * {@link Paragraph} or {@link Sentence} keeps working: Eloquent writes
     * <code>getMorphClass()</code>, which is the alias under such a map.
     *
     * A morph alias is stored exactly as given, because it is the host's own discriminator
     * and {@link Model::getMorphClass()} writes that same alias. Anything else is stored in
     * its canonical form: {@link MorphOneOrMany} constrains on `getMorphClass()` with a
     * case-sensitive comparison on most engines, so storing a case variant would leave the
     * annotation readable through its own `reference` yet missing from
     * `$paragraph->annotations()`.
     *
     * @param string $referenceType The reference type to check.
     *
     * @return string The reference type as it should be stored.
     *
     * @throws InvalidReferenceTypeException If the type does not denote a permitted model.
     */
    public static function assertReferenceType(string $referenceType): string
    {
        $normalized = ltrim($referenceType, '\\');
        $isAlias = Relation::getMorphedModel($normalized) !== null;
        $resolved = self::resolveReferenceType($normalized);

        return $isAlias ? $normalized : $resolved;
    }

    /**
     * Resolve a reference type to the class it denotes, rejecting anything impermissible.
     *
     * This is the read-side counterpart of {@link Annotation::assertReferenceType()}: it
     * returns a class name rather than the stored discriminator, so callers instantiate a
     * class this model permits rather than re-resolving the raw string.
     *
     * Matching is deliberately tolerant in two ways that cost nothing in safety, because
     * the value must still denote {@link Paragraph} or {@link Sentence} either way:
     *
     * - **Case-insensitive**, since PHP resolves class names case-insensitively. A row
     *   holding a differently-cased class name worked before this check existed and still
     *   names the same class.
     * - **Subclasses are accepted**, so a host that extends {@link Paragraph} or
     *   {@link Sentence} can annotate its own model.
     *
     * The case-insensitive comparison runs first and never autoloads, so the ordinary
     * values resolve without touching the autoloader at all. Only an unrecognised name
     * reaches the subclass check, which does autoload — that is strictly less than the
     * unguarded behaviour, which autoloaded *and* constructed, and Composer can only
     * resolve a name to a file the application already ships.
     *
     * @param string $referenceType The stored reference type.
     *
     * @return class-string<Model> The class the reference type denotes.
     *
     * @throws InvalidReferenceTypeException If the type does not denote a permitted model.
     */
    public static function resolveReferenceType(string $referenceType): string
    {
        $normalized = ltrim($referenceType, '\\');
        $resolved = Relation::getMorphedModel($normalized) ?? $normalized;

        foreach (self::REFERENCE_TYPES as $permitted) {
            if (strcasecmp($resolved, $permitted) === 0) {
                return $permitted;
            }
        }

        foreach (self::REFERENCE_TYPES as $permitted) {
            if (is_subclass_of($resolved, $permitted, true)) {
                return $resolved;
            }
        }

        throw new InvalidReferenceTypeException($referenceType);
    }

    /**
     * Reject an impermissible reference type as it is written.
     *
     * Eloquent does not run mutators when it hydrates a model from the database, nor when
     * a write is issued through the query builder or through <code>insert()</code> and
     * <code>upsert()</code>, so this guards ordinary model writes only. See the security
     * concept for what that does and does not cover.
     *
     * @param string|null $value The reference type being assigned.
     *
     * @return void
     *
     * @throws InvalidReferenceTypeException If the type does not denote a permitted model.
     */
    public function setReferenceTypeAttribute(?string $value): void
    {
        $this->attributes['reference_type'] = $value === null
            ? null
            : self::assertReferenceType($value);
    }

    /**
     * Resolve a stored reference type to a class, for the lazy-loading path.
     *
     * Called through {@link \Illuminate\Database\Eloquent\Concerns\HasRelationships::morphInstanceTo()}
     * when <code>$annotation-&gt;reference</code> is read.
     *
     * @param string $class The stored reference type.
     *
     * @return class-string<Model> The resolved class name.
     *
     * @throws InvalidReferenceTypeException If the stored type is not permitted.
     */
    public static function getActualClassNameForMorph($class): string
    {
        return self::resolveReferenceType($class);
    }

    /**
     * Build the polymorphic relation, guarding the eager-loading path.
     *
     * @param Builder<Model> $query      The relation query.
     * @param Model          $parent     The parent model.
     * @param string         $foreignKey The foreign key column.
     * @param string         $ownerKey   The owner key column.
     * @param string         $type       The morph type column.
     * @param string         $relation   The relation name.
     *
     * @return MorphTo<Paragraph|Sentence> The guarded relation.
     */
    protected function newMorphTo(Builder $query, Model $parent, $foreignKey, $ownerKey, $type, $relation): MorphTo
    {
        return new ReferenceMorphTo($query, $parent, $foreignKey, $ownerKey, $type, $relation);
    }
}
