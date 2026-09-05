<?php

namespace Tests\Feature\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Annotation;
use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Models\Sentence;
use PHPUnit\Framework\Attributes\Test;

/** Test the migration that rewrites stored reference types to their morph aliases. */
class MapAnnotationReferenceTypesTest extends TestCase
{

    use RefreshDatabase;

    /**
     * Load the migration under test.
     *
     * The package migrations have already run by the time a test starts, so the migration
     * is required directly and re-run against rows written in the legacy format.
     *
     * @return Migration The migration under test.
     */
    private function migration(): Migration
    {
        return require __DIR__ . '/../../../../database/migrations/2026_09_05_000000_map_annotation_reference_types.php';
    }

    /**
     * Insert an annotation row with a reference type exactly as written.
     *
     * @param string $referenceId   The referenced row identifier.
     * @param string $referenceType The raw reference type to store.
     *
     * @return string The identifier of the inserted annotation.
     */
    private function insertLegacyAnnotation(string $referenceId, string $referenceType): string
    {
        $annotationId = fake()->uuid();

        DB::table(Annotation::TABLE_NAME)->insert([
            'annotation_id' => $annotationId,
            'reference_id' => $referenceId,
            'reference_type' => $referenceType,
            'content' => 'Written by an earlier release.',
        ]);

        return $annotationId;
    }

    /** Legacy class names are rewritten to their aliases, and the relation resolves again. */
    #[Test]
    public function upRewritesLegacyClassNames(): void
    {
        $paragraph = Paragraph::factory()->create();
        $sentence = Sentence::factory()->create();

        $paragraphAnnotationId = $this->insertLegacyAnnotation($paragraph->paragraph_id, Paragraph::class);
        $sentenceAnnotationId = $this->insertLegacyAnnotation($sentence->sentence_id, '\\' . Sentence::class);

        $this->migration()->up();

        $this->assertDatabaseHas(Annotation::TABLE_NAME, [
            'annotation_id' => $paragraphAnnotationId,
            'reference_type' => Paragraph::TABLE_NAME,
        ]);
        $this->assertDatabaseHas(Annotation::TABLE_NAME, [
            'annotation_id' => $sentenceAnnotationId,
            'reference_type' => Sentence::TABLE_NAME,
        ]);

        $this->assertTrue(Annotation::find($paragraphAnnotationId)->reference->is($paragraph));
        $this->assertTrue(Annotation::find($sentenceAnnotationId)->reference->is($sentence));
    }

    /** A reference type that is not one of the mapped class names is left alone. */
    #[Test]
    public function upLeavesUnmappedReferenceTypesUntouched(): void
    {
        $paragraph = Paragraph::factory()->create();
        $annotationId = $this->insertLegacyAnnotation($paragraph->paragraph_id, 'App\\Models\\User');

        $this->migration()->up();

        $this->assertDatabaseHas(Annotation::TABLE_NAME, [
            'annotation_id' => $annotationId,
            'reference_type' => 'App\\Models\\User',
        ]);
    }

    /** The rollback restores the fully-qualified class names. */
    #[Test]
    public function downRestoresClassNames(): void
    {
        $paragraph = Paragraph::factory()->create();
        $annotation = Annotation::factory()->create([
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => Paragraph::TABLE_NAME,
        ]);

        $this->migration()->down();

        $this->assertDatabaseHas(Annotation::TABLE_NAME, [
            'annotation_id' => $annotation->annotation_id,
            'reference_type' => Paragraph::class,
        ]);
    }
}
