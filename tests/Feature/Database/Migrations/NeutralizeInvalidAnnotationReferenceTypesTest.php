<?php

namespace Tests\Feature\Database\Migrations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Annotation;
use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Models\Sentence;
use PHPUnit\Framework\Attributes\Test;

/** A model whose construction proves an impermissible reference type was resolved. */
class MigrationCanary extends Model
{

    /** @var bool Whether the class has been instantiated. */
    public static bool $constructed = false;

    /**
     * Record that the class was instantiated.
     *
     * @param array<string, mixed> $attributes The model attributes.
     */
    public function __construct(array $attributes = [])
    {
        self::$constructed = true;

        parent::__construct($attributes);
    }
}

/** Test the migration that clears impermissible annotation reference types. */
class NeutralizeInvalidAnnotationReferenceTypesTest extends TestCase
{

    use RefreshDatabase;

    /**
     * Load the migration under test.
     *
     * The package migrations have already run when a test starts, so the migration is
     * required directly and re-run against rows written in the unconstrained format.
     *
     * @return Migration The migration under test.
     */
    private function migration(): Migration
    {
        return require __DIR__ . '/../../../../database/migrations/2026_09_05_000000_neutralize_invalid_annotation_reference_types.php';
    }

    /**
     * Insert an annotation row directly, bypassing the model.
     *
     * @param string $referenceId   The referenced row identifier.
     * @param string $referenceType The raw reference type to store.
     *
     * @return string The identifier of the inserted annotation.
     */
    private function plantAnnotation(string $referenceId, string $referenceType): string
    {
        $annotationId = fake()->uuid();

        DB::table(Annotation::TABLE_NAME)->insert([
            'annotation_id' => $annotationId,
            'reference_id' => $referenceId,
            'reference_type' => $referenceType,
            'content' => 'Written before the column was constrained.',
        ]);

        return $annotationId;
    }

    /** An impermissible reference type is cleared, and the annotation content is kept. */
    #[Test]
    public function upClearsImpermissibleReferenceTypes(): void
    {
        $paragraph = Paragraph::factory()->create();
        $annotationId = $this->plantAnnotation($paragraph->paragraph_id, MigrationCanary::class);

        $this->migration()->up();

        $this->assertNull(
            DB::table(Annotation::TABLE_NAME)->where('annotation_id', $annotationId)->value('reference_type')
        );
        $this->assertDatabaseHas(Annotation::TABLE_NAME, [
            'annotation_id' => $annotationId,
            'content' => 'Written before the column was constrained.',
        ]);
    }

    /** Permitted reference types are left exactly as they were. */
    #[Test]
    public function upLeavesPermittedReferenceTypesAlone(): void
    {
        $paragraph = Paragraph::factory()->create();
        $sentence = Sentence::factory()->create();
        $onParagraph = $this->plantAnnotation($paragraph->paragraph_id, Paragraph::class);
        $onSentence = $this->plantAnnotation($sentence->sentence_id, Sentence::class);

        $this->migration()->up();

        $this->assertDatabaseHas(Annotation::TABLE_NAME, [
            'annotation_id' => $onParagraph,
            'reference_type' => Paragraph::class,
        ]);
        $this->assertDatabaseHas(Annotation::TABLE_NAME, [
            'annotation_id' => $onSentence,
            'reference_type' => Sentence::class,
        ]);

        $this->assertTrue(Annotation::find($onParagraph)->reference->is($paragraph));
        $this->assertTrue(Annotation::find($onSentence)->reference->is($sentence));
    }

    /**
     * The query paths that read the column directly no longer instantiate anything.
     *
     * <code>has()</code>, <code>doesntHave()</code> and <code>whereHasMorph()</code> pluck
     * reference types straight from the table, so they are safe only once the column holds
     * nothing impermissible. This is the test that would fail if the migration were dropped.
     */
    #[Test]
    public function relationshipExistenceQueriesDoNotResolveClearedRows(): void
    {
        $paragraph = Paragraph::factory()->create();
        $this->plantAnnotation($paragraph->paragraph_id, MigrationCanary::class);

        $this->migration()->up();

        MigrationCanary::$constructed = false;

        Annotation::has('reference')->get();
        Annotation::doesntHave('reference')->get();
        Annotation::whereHasMorph('reference', '*', fn($query) => $query)->get();

        $this->assertFalse(
            MigrationCanary::$constructed,
            'A relationship-existence query instantiated a cleared reference type.',
        );
    }

    /** The migration is safe to run twice. */
    #[Test]
    public function upIsIdempotent(): void
    {
        $paragraph = Paragraph::factory()->create();
        $annotationId = $this->plantAnnotation($paragraph->paragraph_id, MigrationCanary::class);

        $this->migration()->up();
        $this->migration()->up();

        $this->assertNull(
            DB::table(Annotation::TABLE_NAME)->where('annotation_id', $annotationId)->value('reference_type')
        );
    }

    /** The rollback restores the NOT NULL column when nothing was cleared. */
    #[Test]
    public function downRestoresNotNullWhenNothingWasCleared(): void
    {
        $paragraph = Paragraph::factory()->create();
        $this->plantAnnotation($paragraph->paragraph_id, Paragraph::class);

        $this->migration()->up();
        $this->migration()->down();

        $this->assertDatabaseHas(Annotation::TABLE_NAME, ['reference_type' => Paragraph::class]);
    }
}
