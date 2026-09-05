<?php

namespace Tests\Feature\Database\Migrations;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Models\HostParagraph;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Annotation;
use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Models\Sentence;

/**
 * Test the migration that rewrites {@link Annotation} reference types to morph aliases.
 *
 * The migration has already run by the time these tests execute, so each one plants rows in
 * the shape a 2.x release wrote and re-runs the migration over them.
 */
class AliasAnnotationReferenceTypesTest extends TestCase
{

    use RefreshDatabase;

    /**
     * The morph map as it stood before the test ran.
     *
     * @var array<string, class-string>
     */
    private array $originalMorphMap = [];

    /**
     * Remember the morph map before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->originalMorphMap = Relation::morphMap();
    }

    /**
     * Restore the morph map between tests.
     *
     * {@link Relation::morphMap()} writes to a static, so a test that registers a host alias
     * would otherwise change how every later test in the process resolves its relations.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        Relation::morphMap($this->originalMorphMap, false);

        parent::tearDown();
    }

    /** The migration file name, without its date prefix or extension. */
    private const MIGRATION = 'alias_annotation_reference_types';

    /**
     * Load the migration under test.
     *
     * The path is resolved by glob rather than written out, so renaming the date prefix
     * — which reordering the migration would require — does not fatal this class.
     *
     * @return Migration The migration instance.
     */
    private function migration(): Migration
    {
        $matches = glob(__DIR__ . '/../../../../database/migrations/*_' . self::MIGRATION . '.php');

        $this->assertNotEmpty($matches, 'The migration file was not found.');

        return require $matches[0];
    }

    /**
     * Insert an annotation row directly, in the shape a release before 3.0.0 wrote it.
     *
     * @param string $referenceType The raw reference type to store.
     *
     * @return string The identifier of the inserted annotation.
     */
    private function plantAnnotation(string $referenceType): string
    {
        $annotationId = fake()->uuid();

        DB::table(Annotation::TABLE_NAME)->insert([
            'annotation_id' => $annotationId,
            'reference_id' => fake()->uuid(),
            'reference_type' => $referenceType,
            'content' => 'Written before the column was aliased.',
        ]);

        return $annotationId;
    }

    /**
     * Read one row's reference type back.
     *
     * @param string $annotationId The annotation identifier.
     *
     * @return string|null The stored reference type.
     */
    private function storedReferenceType(string $annotationId): ?string
    {
        return DB::table(Annotation::TABLE_NAME)
            ->where('annotation_id', $annotationId)
            ->value('reference_type');
    }

    /** Stored class names are rewritten to their aliases. */
    #[Test]
    public function upRewritesClassNamesToAliases(): void
    {
        $onParagraph = $this->plantAnnotation(Paragraph::class);
        $onSentence = $this->plantAnnotation(Sentence::class);

        $this->migration()->up();

        $this->assertSame(Paragraph::TABLE_NAME, $this->storedReferenceType($onParagraph));
        $this->assertSame(Sentence::TABLE_NAME, $this->storedReferenceType($onSentence));
    }

    /**
     * A mis-cased class name is rewritten too.
     *
     * PHP resolves class names case-insensitively, so such a row named a permitted model and
     * worked before the upgrade. Leaving it behind would break it.
     */
    #[Test]
    public function upRewritesMisCasedClassNames(): void
    {
        $annotationId = $this->plantAnnotation(strtolower(Paragraph::class));

        $this->migration()->up();

        $this->assertSame(Paragraph::TABLE_NAME, $this->storedReferenceType($annotationId));
    }

    /** Rows already holding the alias are left as they are. */
    #[Test]
    public function upLeavesAliasedRowsAlone(): void
    {
        $annotationId = $this->plantAnnotation(Paragraph::TABLE_NAME);

        $this->migration()->up();

        $this->assertSame(Paragraph::TABLE_NAME, $this->storedReferenceType($annotationId));
    }

    /**
     * A value the package never wrote is preserved rather than cleared.
     *
     * It cannot be told apart from a host application's own discriminator, and the model
     * refuses to resolve an impermissible one in any case. Auditing such rows is the
     * operator's decision, not the migration's.
     */
    #[Test]
    public function upPreservesUnrecognisedReferenceTypes(): void
    {
        $annotationId = $this->plantAnnotation('Illuminate\\Foundation\\Auth\\User');

        $this->migration()->up();

        $this->assertSame('Illuminate\\Foundation\\Auth\\User', $this->storedReferenceType($annotationId));
    }

    /** Rolling back restores the class names, so a downgrade to 2.x reads its own rows. */
    #[Test]
    public function downRestoresClassNames(): void
    {
        $onParagraph = $this->plantAnnotation(Paragraph::TABLE_NAME);
        $onSentence = $this->plantAnnotation(Sentence::TABLE_NAME);
        $planted = $this->plantAnnotation('Illuminate\\Foundation\\Auth\\User');

        $this->migration()->down();

        $this->assertSame(Paragraph::class, $this->storedReferenceType($onParagraph));
        $this->assertSame(Sentence::class, $this->storedReferenceType($onSentence));
        $this->assertSame('Illuminate\\Foundation\\Auth\\User', $this->storedReferenceType($planted));
    }

    /**
     * The migration is actually shipped by the package and ordered after the schema.
     *
     * Every other test here calls `up()` directly, so without this one the file could be
     * moved out of the migrations directory and the suite would stay green.
     */
    #[Test]
    public function migrationIsRegisteredAfterTheSchemaMigration(): void
    {
        $ran = DB::table('migrations')->orderBy('id')->pluck('migration')->all();

        $schema = null;
        $alias = null;

        foreach ($ran as $index => $name) {
            if (str_contains($name, 'create_bibliotecha_tables')) {
                $schema = $index;
            }

            if (str_contains($name, self::MIGRATION)) {
                $alias = $index;
            }
        }

        $this->assertNotNull($schema, 'The schema migration did not run.');
        $this->assertNotNull($alias, 'The alias migration was not loaded from the package.');
        $this->assertGreaterThan($schema, $alias, 'The alias migration must run after the schema.');
    }

    /**
     * A backslash-prefixed class name is rewritten too.
     *
     * Releases before 2.2.0 did not constrain the column, so `\Foo\Bar` could be stored
     * verbatim. The model resolves it, so leaving it would make the row readable through its
     * own reference yet absent from the parent relation.
     */
    #[Test]
    public function upRewritesBackslashPrefixedClassNames(): void
    {
        $annotationId = $this->plantAnnotation('\\' . Paragraph::class);

        $this->migration()->up();

        $this->assertSame(Paragraph::TABLE_NAME, $this->storedReferenceType($annotationId));
    }

    /** Running up() twice, and up() after down(), leaves the same value. */
    #[Test]
    public function upIsIdempotentAndRoundTrips(): void
    {
        $annotationId = $this->plantAnnotation(Paragraph::class);

        $this->migration()->up();
        $this->migration()->up();

        $this->assertSame(Paragraph::TABLE_NAME, $this->storedReferenceType($annotationId));

        $this->migration()->down();
        $this->assertSame(Paragraph::class, $this->storedReferenceType($annotationId));

        $this->migration()->up();
        $this->assertSame(Paragraph::TABLE_NAME, $this->storedReferenceType($annotationId));
    }

    /**
     * A host subclass row is left alone.
     *
     * The subclass is a permitted reference type but not one this package aliases, so the
     * migration has nothing to rewrite it to.
     */
    #[Test]
    public function upPreservesHostSubclassReferenceTypes(): void
    {
        $annotationId = $this->plantAnnotation(HostParagraph::class);

        $this->migration()->up();

        $this->assertSame(HostParagraph::class, $this->storedReferenceType($annotationId));
    }

    /**
     * A host's own alias is written in preference to the package's.
     *
     * `$paragraph->annotations()` constrains on the host's alias, so writing the package
     * alias here would drop every pre-upgrade row from that relation.
     */
    #[Test]
    public function upWritesAHostAliasWhenOneIsRegistered(): void
    {
        Relation::morphMap(['paragraph' => Paragraph::class]);

        $paragraph = Paragraph::factory()->create();
        $annotationId = fake()->uuid();

        DB::table(Annotation::TABLE_NAME)->insert([
            'annotation_id' => $annotationId,
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => Paragraph::class,
            'content' => 'Written before the column was aliased.',
        ]);

        $this->migration()->up();

        $this->assertSame('paragraph', $this->storedReferenceType($annotationId));
        $this->assertCount(1, $paragraph->annotations);
    }

    /** An aliased row is readable through the model once the migration has run. */
    #[Test]
    public function migratedRowResolvesThroughTheModel(): void
    {
        $paragraph = Paragraph::factory()->create();
        $annotationId = fake()->uuid();

        DB::table(Annotation::TABLE_NAME)->insert([
            'annotation_id' => $annotationId,
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => Paragraph::class,
            'content' => 'Written before the column was aliased.',
        ]);

        $this->migration()->up();

        $annotation = Annotation::find($annotationId);
        $this->assertTrue($annotation->reference->is($paragraph));

        /* And the parent relation, which constrains on the alias, now finds it. */
        $this->assertCount(1, $paragraph->annotations);
    }

}
