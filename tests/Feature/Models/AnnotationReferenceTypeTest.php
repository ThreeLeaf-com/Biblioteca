<?php

namespace Tests\Feature\Models;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Exceptions\InvalidReferenceTypeException;
use ThreeLeaf\Biblioteca\Models\Annotation;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Models\Sentence;
use PHPUnit\Framework\Attributes\Test;

/** A host application's own subclass of a permitted model. */
class HostParagraph extends Paragraph
{
}

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
     * Clear the morph map between tests.
     *
     * {@link Relation::morphMap()} writes to a static, so a test that registers one would
     * otherwise change how every later test in the process resolves its polymorphic
     * relations.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        Relation::morphMap([], false);

        parent::tearDown();
    }

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

    /**
     * A mis-cased class name is accepted, because PHP resolves class names that way.
     *
     * Such a value worked before the check existed and denotes the same model, so
     * rejecting it would break data rather than protect it.
     */
    #[Test]
    public function misCasedClassNameIsAccepted(): void
    {
        $paragraph = Paragraph::factory()->create();
        $annotationId = $this->plantAnnotation($paragraph->paragraph_id, strtolower(Paragraph::class));

        $this->assertSame(Paragraph::class, Annotation::resolveReferenceType(strtolower(Paragraph::class)));
        $this->assertTrue(Annotation::find($annotationId)->reference->is($paragraph));
    }

    /** A host subclass of a permitted model is accepted. */
    #[Test]
    public function hostSubclassIsAccepted(): void
    {
        $paragraph = HostParagraph::find(Paragraph::factory()->create()->paragraph_id);

        $annotation = Annotation::create([
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => HostParagraph::class,
            'content' => 'On a host subclass.',
        ]);

        $this->assertDatabaseHas(Annotation::TABLE_NAME, [
            'annotation_id' => $annotation->annotation_id,
            'reference_type' => HostParagraph::class,
        ]);
        $this->assertInstanceOf(HostParagraph::class, $annotation->reference);
    }

    /** A class unrelated to the permitted models is still rejected. */
    #[Test]
    public function unrelatedClassIsRejected(): void
    {
        $this->expectException(InvalidReferenceTypeException::class);

        Annotation::resolveReferenceType(Book::class);
    }

    /**
     * A host that aliases the permitted models through a morph map keeps working.
     *
     * Eloquent writes <code>getMorphClass()</code>, which is the alias under such a map, so
     * a guard that compared class names literally would break the relation.
     */
    #[Test]
    public function hostMorphMapIsHonoured(): void
    {
        Relation::morphMap(['paragraph' => Paragraph::class]);

        $paragraph = Paragraph::factory()->create();
        $annotation = $paragraph->annotations()->create(['content' => 'Under a host morph map.']);

        $this->assertDatabaseHas(Annotation::TABLE_NAME, [
            'annotation_id' => $annotation->annotation_id,
            'reference_type' => 'paragraph',
        ]);

        $this->assertTrue(Annotation::find($annotation->annotation_id)->reference->is($paragraph));

        $eager = Annotation::with('reference')->find($annotation->annotation_id);
        $this->assertTrue($eager->reference->is($paragraph));
    }

    /** An alias that maps to a model outside the allow-list is still rejected. */
    #[Test]
    public function hostMorphMapCannotWidenTheAllowList(): void
    {
        Relation::morphMap(['book' => Book::class]);

        $this->expectException(InvalidReferenceTypeException::class);

        Annotation::assertReferenceType('book');
    }

}
