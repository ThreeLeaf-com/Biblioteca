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

/** A host subclass that sets its discriminator by overriding the accessor, not by aliasing. */
class OverridingParagraph extends Paragraph
{

    /**
     * Report a discriminator of the subclass's own choosing.
     *
     * @return string The morph class.
     */
    public function getMorphClass(): string
    {
        return 'overriding_paragraph';
    }
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
     * {@link Relation::morphMap()} writes to a static, so a test that registers one would
     * otherwise change how every later test in the process resolves its polymorphic
     * relations. The map is restored rather than emptied, because emptying it would also
     * discard the entries the package's own service provider registered — state this test
     * class did not create and must not destroy.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        Relation::morphMap($this->originalMorphMap, false);

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

        /* The leading backslash is stripped and the class name is aliased, so the column
           holds one form. */
        $this->assertDatabaseHas(Annotation::TABLE_NAME, [
            'annotation_id' => $onSentence->annotation_id,
            'reference_type' => Sentence::TABLE_NAME,
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
            'reference_type' => Paragraph::TABLE_NAME,
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

    /**
     * A mis-cased class name is stored as the alias, so the parent relation still finds it.
     *
     * {@link \Illuminate\Database\Eloquent\Relations\MorphOneOrMany} constrains on the
     * parent's `getMorphClass()`, and that comparison is case-sensitive on most engines.
     * Storing the submitted value would leave the annotation readable through its own
     * `reference` yet absent from `$paragraph->annotations()`.
     */
    #[Test]
    public function misCasedClassNameIsStoredAsTheAlias(): void
    {
        $paragraph = Paragraph::factory()->create();

        $annotation = Annotation::create([
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => strtolower(Paragraph::class),
            'content' => 'Written with a mis-cased class name.',
        ]);

        $this->assertDatabaseHas(Annotation::TABLE_NAME, [
            'annotation_id' => $annotation->annotation_id,
            'reference_type' => Paragraph::TABLE_NAME,
        ]);

        $paragraph->refresh();
        $this->assertCount(1, $paragraph->annotations);
    }

    /** A subclass is stored as itself, so its own relation still finds it. */
    #[Test]
    public function subclassIsStoredAsItself(): void
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

        $paragraph->refresh();
        $this->assertCount(1, $paragraph->annotations);
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

    /** The package registers its own aliases, so the models report them as their morph class. */
    #[Test]
    public function packageMorphMapIsRegistered(): void
    {
        $this->assertSame(Paragraph::class, Relation::getMorphedModel(Paragraph::TABLE_NAME));
        $this->assertSame(Sentence::class, Relation::getMorphedModel(Sentence::TABLE_NAME));

        $this->assertSame(Paragraph::TABLE_NAME, (new Paragraph())->getMorphClass());
        $this->assertSame(Sentence::TABLE_NAME, (new Sentence())->getMorphClass());
    }

    /**
     * The morph map is registered without requiring one.
     *
     * {@link Relation::enforceMorphMap()} also sets {@link Relation::requireMorphMap()}, a
     * process-global flag that would make every unmapped morph in the host application
     * throw. Registering the map must not impose that.
     */
    #[Test]
    public function morphMapIsNotRequiredOfTheHost(): void
    {
        $this->assertFalse(Relation::requiresMorphMap());
    }

    /** An alias is accepted on write and stored as given. */
    #[Test]
    public function aliasIsAcceptedOnWrite(): void
    {
        $sentence = Sentence::factory()->create();

        $annotation = Annotation::create([
            'reference_id' => $sentence->sentence_id,
            'reference_type' => Sentence::TABLE_NAME,
            'content' => 'Written with the alias.',
        ]);

        $this->assertDatabaseHas(Annotation::TABLE_NAME, [
            'annotation_id' => $annotation->annotation_id,
            'reference_type' => Sentence::TABLE_NAME,
        ]);
        $this->assertTrue($annotation->reference->is($sentence));
    }

    /** A class name submitted by a 2.x client is normalized to the alias. */
    #[Test]
    public function legacyClassNameIsNormalizedToTheAlias(): void
    {
        $this->assertSame(Paragraph::TABLE_NAME, Annotation::assertReferenceType(Paragraph::class));
        $this->assertSame(Sentence::TABLE_NAME, Annotation::assertReferenceType('\\' . Sentence::class));
        $this->assertSame(Paragraph::TABLE_NAME, Annotation::assertReferenceType(strtolower(Paragraph::class)));
    }

    /**
     * A stored alias resolves even where the morph map was never registered.
     *
     * A process that boots no service providers — a migration run through a bare kernel, for
     * one — must still read rows this package wrote.
     */
    #[Test]
    public function aliasResolvesWithoutTheMorphMap(): void
    {
        $paragraph = Paragraph::factory()->create();
        $annotationId = $this->plantAnnotation($paragraph->paragraph_id, Paragraph::TABLE_NAME);

        Relation::morphMap([], false);

        $this->assertNull(Relation::getMorphedModel(Paragraph::TABLE_NAME));

        $this->assertSame(Paragraph::class, Annotation::resolveReferenceType(Paragraph::TABLE_NAME));
        $this->assertSame(Sentence::class, Annotation::resolveReferenceType(Sentence::TABLE_NAME));

        /* The eager path resolves through the fallback too, not only the static call. */
        $eager = Annotation::with('reference')->find($annotationId);
        $this->assertInstanceOf(Paragraph::class, $eager->reference);
        $this->assertTrue($eager->reference->is($paragraph));
    }

    /**
     * A host alias for a permitted model wins over the package's own.
     *
     * {@link \Illuminate\Database\Eloquent\Relations\MorphOneOrMany} constrains on the
     * parent's `getMorphClass()`, so the stored value has to follow the host's map rather
     * than {@link Annotation::REFERENCE_TYPES}, or the annotation would go missing from
     * `$paragraph->annotations()`.
     */
    #[Test]
    public function hostAliasTakesPrecedenceOverThePackageAlias(): void
    {
        Relation::morphMap(['paragraph' => Paragraph::class]);

        $paragraph = Paragraph::factory()->create();

        $annotation = Annotation::create([
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => Paragraph::class,
            'content' => 'Under a host morph map.',
        ]);

        $this->assertDatabaseHas(Annotation::TABLE_NAME, [
            'annotation_id' => $annotation->annotation_id,
            'reference_type' => 'paragraph',
        ]);

        $paragraph->refresh();
        $this->assertCount(1, $paragraph->annotations);
    }

    /**
     * A case variant of an alias does not slip past a host's own morph map.
     *
     * {@link Relation::getMorphedModel()} matches exactly, so a mis-cased alias reaches this
     * package's fallback. If that fallback matched loosely it would answer with the
     * package's class, letting a caller choose the model — and any global scopes on it — by
     * varying the letter case of an otherwise valid alias.
     */
    #[Test]
    public function misCasedAliasDoesNotBypassAHostMorphMap(): void
    {
        Relation::morphMap([Paragraph::TABLE_NAME => HostParagraph::class]);

        $this->assertSame(HostParagraph::class, Annotation::resolveReferenceType(Paragraph::TABLE_NAME));

        $this->expectException(InvalidReferenceTypeException::class);

        Annotation::resolveReferenceType(strtoupper(Paragraph::TABLE_NAME));
    }

    /**
     * A host repointing the package's own alias cannot widen the allow-list.
     *
     * This is the case the fallback could have rescued: the alias is one the package knows,
     * but the application map answers first and points it somewhere impermissible.
     */
    #[Test]
    public function hostCannotRepointThePackageAliasAtAnotherModel(): void
    {
        Relation::morphMap([Paragraph::TABLE_NAME => Book::class]);

        $this->expectException(InvalidReferenceTypeException::class);

        Annotation::resolveReferenceType(Paragraph::TABLE_NAME);
    }

    /**
     * Writes still work when a host removes the package's entries from the morph map.
     *
     * <code>morphMap($map, false)</code> replaces the map outright. The stored value then
     * degrades to the class name, which is what <code>getMorphClass()</code> returns in that
     * process — so the parent relation, which constrains on the same method, still finds the
     * row. The column is no longer in the 3.0.0 shape, but nothing breaks silently.
     */
    #[Test]
    public function writesDegradeGracefullyWhenTheMapIsRemoved(): void
    {
        $paragraph = Paragraph::factory()->create();

        Relation::morphMap([], false);

        $annotation = Annotation::create([
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => Paragraph::class,
            'content' => 'Written with no morph map.',
        ]);

        $this->assertDatabaseHas(Annotation::TABLE_NAME, [
            'annotation_id' => $annotation->annotation_id,
            'reference_type' => Paragraph::class,
        ]);

        $paragraph->refresh();
        $this->assertCount(1, $paragraph->annotations);
    }

    /**
     * A subclass that overrides getMorphClass() has that override stored.
     *
     * Overriding the accessor is the other way a host sets a discriminator, and it is
     * invisible to the morph map. The parent relation constrains on the same method, so
     * storing anything else would leave the annotation out of
     * <code>$paragraph->annotations()</code>.
     */
    #[Test]
    public function subclassMorphClassOverrideIsStored(): void
    {
        $paragraph = OverridingParagraph::find(Paragraph::factory()->create()->paragraph_id);

        $annotation = Annotation::create([
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => OverridingParagraph::class,
            'content' => 'On a subclass that overrides its morph class.',
        ]);

        $this->assertDatabaseHas(Annotation::TABLE_NAME, [
            'annotation_id' => $annotation->annotation_id,
            'reference_type' => 'overriding_paragraph',
        ]);

        $this->assertCount(1, $paragraph->annotations);
    }

    /** The aliases are a stored-data contract, so they are pinned to their literal values. */
    #[Test]
    public function aliasesAreTheFrozenLiteralValues(): void
    {
        $this->assertSame(
            [
                'b_paragraphs' => Paragraph::class,
                'b_sentences' => Sentence::class,
            ],
            Annotation::REFERENCE_TYPES,
        );
    }

    /** The exception names the aliases, which is what the column actually holds. */
    #[Test]
    public function exceptionMessageNamesThePermittedAliases(): void
    {
        $this->expectException(InvalidReferenceTypeException::class);
        $this->expectExceptionMessage(Paragraph::TABLE_NAME);

        Annotation::resolveReferenceType('Illuminate\\Foundation\\Auth\\User');
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
