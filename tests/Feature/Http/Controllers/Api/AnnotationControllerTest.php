<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Http\Controllers\Api\AnnotationController;
use ThreeLeaf\Biblioteca\Http\Resources\AnnotationResource;
use ThreeLeaf\Biblioteca\Models\Annotation;
use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Models\Sentence;
use PHPUnit\Framework\Attributes\Test;

/** Test {@link AnnotationController}. */
class AnnotationControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Clear the morph map between tests.
     *
     * {@link Relation::morphMap()} writes to a static, so a test that registers one would
     * otherwise change how every later test in the process resolves its relations.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        Relation::morphMap([], false);

        parent::tearDown();
    }

    /**
     * {@link AnnotationController::show()}.
     * @see {@link AnnotationResource::toArray()}
     */
    #[Test]
    public function showAnnotation(): void
    {
        $annotation = Annotation::factory()->paragraph()->create();

        $expectedData = (new AnnotationResource($annotation))->response()->getData(true);

        $response = $this->getJson(route('annotations.show', $annotation));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);

        /* Pinned literally, because $expectedData comes from the resource and would agree
           with it whatever the resource returned. */
        $response->assertJsonPath('data.reference_type', Paragraph::TABLE_NAME);
    }

    /**
     * {@link AnnotationController::index()}.
     * @see {@link AnnotationResource::collection()}
     */
    #[Test]
    public function indexAnnotation(): void
    {
        $annotations = Annotation::factory()->count(3)->create();

        $expectedData = AnnotationResource::collection($annotations)->response()->getData(true);

        $response = $this->getJson(route('annotations.index'));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
    }

    /**
     * {@link AnnotationController::store()}.
     * @see {@link AnnotationRequest::rules()}
     * @see {@link AnnotationResource::toArray()}
     */
    #[Test]
    public function storeAnnotation(): void
    {
        $paragraph = Paragraph::factory()->create();

        $data = [
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => Paragraph::class,
            'content' => 'This is a test annotation',
        ];

        $response = $this->postJson(route('annotations.store'), $data);
        $response->assertStatus(HttpCodes::HTTP_CREATED);

        $annotation = Annotation::latest()->first();
        $expectedData = (new AnnotationResource($annotation))->response()->getData(true);

        $response->assertJson($expectedData);

        $this->assertDatabaseHas(Annotation::TABLE_NAME, [
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => Paragraph::TABLE_NAME,
            'content' => 'This is a test annotation',
        ]);
    }

    /**
     * {@link AnnotationController::update()}.
     * @see {@link AnnotationRequest::rules()}
     * @see {@link AnnotationResource::toArray()}
     */
    #[Test]
    public function updateAnnotation(): void
    {
        $paragraph = Paragraph::factory()->create();
        $annotation = Annotation::factory()->create([
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => Paragraph::class,
            'content' => 'Original content',
        ]);

        $updatedData = [
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => Paragraph::class,
            'content' => 'Updated annotation content',
        ];

        $response = $this->putJson(route('annotations.update', $annotation), $updatedData);
        $response->assertStatus(HttpCodes::HTTP_OK);

        $annotation->refresh();  // Reload the annotation from the database
        $expectedData = (new AnnotationResource($annotation))->response()->getData(true);

        $response->assertJson($expectedData);

        $this->assertDatabaseHas(Annotation::TABLE_NAME, [
            'annotation_id' => $annotation->annotation_id,
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => Paragraph::TABLE_NAME,
            'content' => 'Updated annotation content',
        ]);
    }

    /** {@link AnnotationController::destroy()}. */
    #[Test]
    public function destroyAnnotation(): void
    {
        $paragraph = Paragraph::factory()->create();
        $annotation = Annotation::factory()->create([
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => Paragraph::class,
            'content' => 'Content to be deleted',
        ]);

        $response = $this->deleteJson(route('annotations.destroy', $annotation));

        $response->assertStatus(HttpCodes::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(Annotation::TABLE_NAME, [
            'annotation_id' => $annotation->annotation_id,
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => Paragraph::TABLE_NAME,
            'content' => 'Content to be deleted',
        ]);

        $response = $this->deleteJson(route('annotations.destroy', $annotation));

        $response->assertStatus(HttpCodes::HTTP_NOT_FOUND);
    }

    /**
     * An impermissible reference_type is rejected rather than stored and later resolved.
     *
     * @see {@link \ThreeLeaf\Biblioteca\Http\Requests\AnnotationRequest::rules()}
     */
    #[Test]
    public function storeRejectsUnpermittedReferenceType(): void
    {
        $paragraph = Paragraph::factory()->create();
        $referenceTypes = [
            'Illuminate\\Foundation\\Auth\\User',
            Annotation::class,
            'ThreeLeaf\\Biblioteca\\Models\\Book',
            'not-a-class',
            '',
        ];

        foreach ($referenceTypes as $referenceType) {
            $response = $this->postJson(route('annotations.store'), [
                'reference_id' => $paragraph->paragraph_id,
                'reference_type' => $referenceType,
                'content' => 'This annotation should never be stored',
            ]);

            $response->assertStatus(HttpCodes::HTTP_UNPROCESSABLE_ENTITY)
                ->assertJsonValidationErrors('reference_type');
        }

        $this->assertDatabaseMissing(Annotation::TABLE_NAME, [
            'content' => 'This annotation should never be stored',
        ]);
    }

    /**
     * A reference_id that does not exist in the referenced table is rejected.
     *
     * @see {@link \ThreeLeaf\Biblioteca\Http\Requests\AnnotationRequest::rules()}
     */
    #[Test]
    public function storeRejectsUnknownReferenceId(): void
    {
        $response = $this->postJson(route('annotations.store'), [
            'reference_id' => fake()->uuid(),
            'reference_type' => Paragraph::class,
            'content' => 'This annotation should never be stored',
        ]);

        $response->assertStatus(HttpCodes::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('reference_id');

        $this->assertDatabaseMissing(Annotation::TABLE_NAME, [
            'content' => 'This annotation should never be stored',
        ]);
    }

    /**
     * A reference_id belonging to the other permitted model is rejected.
     *
     * The identifier is a real UUID, but it is not a row in the table the submitted
     * reference_type names.
     *
     * @see {@link \ThreeLeaf\Biblioteca\Http\Requests\AnnotationRequest::rules()}
     */
    #[Test]
    public function storeRejectsReferenceIdFromTheWrongTable(): void
    {
        $sentence = Sentence::factory()->create();

        $response = $this->postJson(route('annotations.store'), [
            'reference_id' => $sentence->sentence_id,
            'reference_type' => Paragraph::class,
            'content' => 'This annotation should never be stored',
        ]);

        $response->assertStatus(HttpCodes::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('reference_id');
    }

    /**
     * An impermissible reference_type is rejected on update as well as on store.
     *
     * @see {@link \ThreeLeaf\Biblioteca\Http\Requests\AnnotationRequest::rules()}
     */
    #[Test]
    public function updateRejectsUnpermittedReferenceType(): void
    {
        $paragraph = Paragraph::factory()->create();
        $annotation = Annotation::factory()->create([
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => Paragraph::class,
            'content' => 'Original content',
        ]);

        $response = $this->putJson(route('annotations.update', $annotation), [
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => 'Illuminate\\Foundation\\Auth\\User',
            'content' => 'Updated annotation content',
        ]);

        $response->assertStatus(HttpCodes::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('reference_type');

        $this->assertDatabaseHas(Annotation::TABLE_NAME, [
            'annotation_id' => $annotation->annotation_id,
            'reference_type' => Paragraph::TABLE_NAME,
            'content' => 'Original content',
        ]);
    }

    /**
     * A leading backslash on the class name is accepted and stripped.
     *
     * @see {@link \ThreeLeaf\Biblioteca\Http\Requests\AnnotationRequest::prepareForValidation()}
     */
    #[Test]
    public function storeAcceptsLeadingBackslash(): void
    {
        $sentence = Sentence::factory()->create();

        $response = $this->postJson(route('annotations.store'), [
            'reference_id' => $sentence->sentence_id,
            'reference_type' => '\\' . Sentence::class,
            'content' => 'Posted with a leading backslash',
        ]);

        $response->assertStatus(HttpCodes::HTTP_CREATED);

        $this->assertDatabaseHas(Annotation::TABLE_NAME, [
            'reference_id' => $sentence->sentence_id,
            'reference_type' => Sentence::TABLE_NAME,
            'content' => 'Posted with a leading backslash',
        ]);
    }

    /** A non-string reference_type is rejected rather than raising a type error. */
    #[Test]
    public function storeRejectsNonStringReferenceType(): void
    {
        $paragraph = Paragraph::factory()->create();

        foreach ([['array'], 42, null] as $referenceType) {
            $response = $this->postJson(route('annotations.store'), [
                'reference_id' => $paragraph->paragraph_id,
                'reference_type' => $referenceType,
                'content' => 'This annotation should never be stored',
            ]);

            $response->assertStatus(HttpCodes::HTTP_UNPROCESSABLE_ENTITY)
                ->assertJsonValidationErrors('reference_type');
        }
    }


    /**
     * A host morph map alias is accepted by the API and stored as the alias.
     *
     * Eloquent writes `getMorphClass()` under such a map, so an API that rejected the alias
     * would leave the host with no route that produces a row its own relations can read.
     */
    #[Test]
    public function storeAcceptsHostMorphMapAlias(): void
    {
        Relation::morphMap(['paragraph' => Paragraph::class]);

        $paragraph = Paragraph::factory()->create();

        $response = $this->postJson(route('annotations.store'), [
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => 'paragraph',
            'content' => 'Posted with a host morph alias',
        ]);

        $response->assertStatus(HttpCodes::HTTP_CREATED);

        $this->assertDatabaseHas(Annotation::TABLE_NAME, [
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => 'paragraph',
            'content' => 'Posted with a host morph alias',
        ]);

        $paragraph->refresh();
        $this->assertCount(1, $paragraph->annotations);
    }

    /** An alias for a model outside the permitted set is still rejected. */
    #[Test]
    public function storeRejectsAliasForAnImpermissibleModel(): void
    {
        Relation::morphMap(['book' => \ThreeLeaf\Biblioteca\Models\Book::class]);

        $paragraph = Paragraph::factory()->create();

        $response = $this->postJson(route('annotations.store'), [
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => 'book',
            'content' => 'This annotation should never be stored',
        ]);

        $response->assertStatus(HttpCodes::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('reference_type');
    }

    /** A class name in a different letter case is accepted. */
    #[Test]
    public function storeAcceptsMisCasedClassName(): void
    {
        $sentence = Sentence::factory()->create();

        $response = $this->postJson(route('annotations.store'), [
            'reference_id' => $sentence->sentence_id,
            'reference_type' => strtolower(Sentence::class),
            'content' => 'Posted with a mis-cased class name',
        ]);

        $response->assertStatus(HttpCodes::HTTP_CREATED);
    }

    /**
     * The API accepts the package's own alias and returns it.
     *
     * This is the 3.0.0 shape: no request or response carries the package's class names.
     */
    #[Test]
    public function storeAcceptsAndReturnsThePackageAlias(): void
    {
        $sentence = Sentence::factory()->create();

        $response = $this->postJson(route('annotations.store'), [
            'reference_id' => $sentence->sentence_id,
            'reference_type' => Sentence::TABLE_NAME,
            'content' => 'Posted with the package alias',
        ]);

        $response->assertStatus(HttpCodes::HTTP_CREATED)
            ->assertJsonPath('data.reference_type', Sentence::TABLE_NAME);

        $this->assertDatabaseHas(Annotation::TABLE_NAME, [
            'reference_id' => $sentence->sentence_id,
            'reference_type' => Sentence::TABLE_NAME,
            'content' => 'Posted with the package alias',
        ]);
    }

    /**
     * A 2.x client submitting a class name still succeeds, and is answered with the alias.
     *
     * Writes stay compatible across the major bump; only the returned and stored value
     * changes.
     */
    #[Test]
    public function storeAnswersALegacyClassNameWithTheAlias(): void
    {
        $paragraph = Paragraph::factory()->create();

        $response = $this->postJson(route('annotations.store'), [
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => Paragraph::class,
            'content' => 'Posted by a 2.x client',
        ]);

        $response->assertStatus(HttpCodes::HTTP_CREATED)
            ->assertJsonPath('data.reference_type', Paragraph::TABLE_NAME);
    }
}
