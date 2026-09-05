<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Exceptions\InvalidReferenceTypeException;
use ThreeLeaf\Biblioteca\Models\Annotation;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Models\Sentence;
use PHPUnit\Framework\Attributes\Test;

/**
 * Test the {@link Annotation} reference type allow-list.
 *
 * <code>reference_type</code> names the class Eloquent resolves, so the guard has to hold
 * on every write and on every read — not only on the HTTP routes.
 */
class AnnotationReferenceTypeTest extends TestCase
{

    use RefreshDatabase;

    /**
     * Insert an annotation row directly, bypassing the model.
     *
     * This is how a row written by a release that did not constrain the column looks.
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

    /** Host code cannot mass-assign a reference type outside the allow-list. */
    #[Test]
    public function createRejectsUnpermittedReferenceType(): void
    {
        $paragraph = Paragraph::factory()->create();

        $this->expectException(InvalidReferenceTypeException::class);

        Annotation::create([
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => 'Illuminate\\Foundation\\Auth\\User',
            'content' => 'This annotation should never be stored',
        ]);
    }

    /** A Biblioteca model that is not a valid reference target is rejected too. */
    #[Test]
    public function createRejectsAnotherBibliotecaModel(): void
    {
        $paragraph = Paragraph::factory()->create();

        $this->expectException(InvalidReferenceTypeException::class);

        Annotation::create([
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => Book::class,
            'content' => 'This annotation should never be stored',
        ]);
    }

    /** Direct attribute assignment is guarded, not only mass assignment. */
    #[Test]
    public function directAssignmentRejectsUnpermittedReferenceType(): void
    {
        $annotation = new Annotation();

        $this->expectException(InvalidReferenceTypeException::class);

        $annotation->reference_type = 'Illuminate\\Foundation\\Auth\\User';
    }

    /** Nothing is written when the guard rejects the type. */
    #[Test]
    public function rejectedCreateWritesNothing(): void
    {
        $paragraph = Paragraph::factory()->create();

        try {
            Annotation::create([
                'reference_id' => $paragraph->paragraph_id,
                'reference_type' => 'Illuminate\\Foundation\\Auth\\User',
                'content' => 'This annotation should never be stored',
            ]);
        } catch (InvalidReferenceTypeException) {
            /* Expected; the assertion below is the point of the test. */
        }

        $this->assertDatabaseMissing(Annotation::TABLE_NAME, [
            'content' => 'This annotation should never be stored',
        ]);
    }

    /** Both permitted types are accepted, with or without a leading backslash. */
    #[Test]
    public function permittedReferenceTypesAreAccepted(): void
    {
        $paragraph = Paragraph::factory()->create();
        $sentence = Sentence::factory()->create();

        $onParagraph = Annotation::create([
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => Paragraph::class,
            'content' => 'On a paragraph.',
        ]);
        $onSentence = Annotation::create([
            'reference_id' => $sentence->sentence_id,
            'reference_type' => '\\' . Sentence::class,
            'content' => 'On a sentence.',
        ]);

        $this->assertTrue($onParagraph->reference->is($paragraph));
        $this->assertTrue($onSentence->reference->is($sentence));

        /* The leading backslash is stripped, so the column holds one form. */
        $this->assertDatabaseHas(Annotation::TABLE_NAME, [
            'annotation_id' => $onSentence->annotation_id,
            'reference_type' => Sentence::class,
        ]);
    }

    /** Writing through the morphMany relation still works. */
    #[Test]
    public function morphManyWriteIsPermitted(): void
    {
        $paragraph = Paragraph::factory()->create();

        $annotation = $paragraph->annotations()->create(['content' => 'Through the relation.']);

        $this->assertDatabaseHas(Annotation::TABLE_NAME, [
            'annotation_id' => $annotation->annotation_id,
            'reference_type' => Paragraph::class,
        ]);
    }

    /** A row planted before the guard existed is not resolved when read lazily. */
    #[Test]
    public function lazyReadRejectsPlantedReferenceType(): void
    {
        $paragraph = Paragraph::factory()->create();
        $annotationId = $this->plantAnnotation($paragraph->paragraph_id, 'Illuminate\\Foundation\\Auth\\User');

        $annotation = Annotation::find($annotationId);

        $this->expectException(InvalidReferenceTypeException::class);

        $annotation->reference;
    }

    /** The same row is not resolved when it is eager loaded. */
    #[Test]
    public function eagerReadRejectsPlantedReferenceType(): void
    {
        $paragraph = Paragraph::factory()->create();
        $this->plantAnnotation($paragraph->paragraph_id, 'Illuminate\\Foundation\\Auth\\User');

        $this->expectException(InvalidReferenceTypeException::class);

        Annotation::with('reference')->get();
    }

    /** Loading the relation after the fact is rejected as well. */
    #[Test]
    public function deferredLoadRejectsPlantedReferenceType(): void
    {
        $paragraph = Paragraph::factory()->create();
        $annotationId = $this->plantAnnotation($paragraph->paragraph_id, 'Illuminate\\Foundation\\Auth\\User');

        $annotation = Annotation::find($annotationId);

        $this->expectException(InvalidReferenceTypeException::class);

        $annotation->load('reference');
    }

    /** Eager loading still resolves rows whose reference type is permitted. */
    #[Test]
    public function eagerReadResolvesPermittedReferenceTypes(): void
    {
        $paragraph = Paragraph::factory()->create();
        $sentence = Sentence::factory()->create();

        $onParagraph = Annotation::create([
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => Paragraph::class,
            'content' => 'On a paragraph.',
        ]);
        $onSentence = Annotation::create([
            'reference_id' => $sentence->sentence_id,
            'reference_type' => Sentence::class,
            'content' => 'On a sentence.',
        ]);

        $loaded = Annotation::with('reference')->get()->keyBy('annotation_id');

        $this->assertTrue($loaded[$onParagraph->annotation_id]->reference->is($paragraph));
        $this->assertTrue($loaded[$onSentence->annotation_id]->reference->is($sentence));
    }

    /** The exception names the rejected value and the permitted types. */
    #[Test]
    public function exceptionMessageNamesTheRejectedValue(): void
    {
        $this->expectException(InvalidReferenceTypeException::class);
        $this->expectExceptionMessage('Illuminate\\Foundation\\Auth\\User');

        Annotation::assertReferenceType('Illuminate\\Foundation\\Auth\\User');
    }

    /** A mis-cased class name fails closed rather than being normalized. */
    #[Test]
    public function misCasedClassNameIsRejected(): void
    {
        $this->expectException(InvalidReferenceTypeException::class);

        Annotation::assertReferenceType(strtolower(Paragraph::class));
    }
}
