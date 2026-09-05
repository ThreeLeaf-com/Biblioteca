<?php

namespace Tests\Feature\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
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
     * Load the migration under test.
     *
     * @return Migration The migration instance.
     */
    private function migration(): Migration
    {
        return require __DIR__ . '/../../../../database/migrations/2026_09_06_000000_alias_annotation_reference_types.php';
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
