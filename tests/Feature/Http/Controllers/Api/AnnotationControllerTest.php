<?php

namespace Tests\Feature\Http\Controllers\Api;

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
     * {@link AnnotationController::show()}.
     * @see {@link AnnotationResource::toArray()}
     */
    #[Test]
    public function showAnnotation(): void
    {
        $annotation = Annotation::factory()->create();

        $expectedData = (new AnnotationResource($annotation))->response()->getData(true);

        $response = $this->getJson(route('annotations.show', $annotation));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
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
            'reference_type' => Paragraph::TABLE_NAME,
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
            'reference_type' => Paragraph::TABLE_NAME,
            'content' => 'Original content',
        ]);

        $updatedData = [
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => Paragraph::TABLE_NAME,
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
            'reference_type' => Paragraph::TABLE_NAME,
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
     * An unregistered reference_type is rejected rather than stored and later resolved.
     *
     * @see {@link AnnotationRequest::rules()}
     */
    #[Test]
    public function storeRejectsUnregisteredReferenceType(): void
    {
        $paragraph = Paragraph::factory()->create();

        foreach (['App\\Models\\User', Annotation::class, 'b_books', '', 'not-a-class'] as $referenceType) {
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
     * @see {@link AnnotationRequest::rules()}
     */
    #[Test]
    public function storeRejectsUnknownReferenceId(): void
    {
        $response = $this->postJson(route('annotations.store'), [
            'reference_id' => fake()->uuid(),
            'reference_type' => Paragraph::TABLE_NAME,
            'content' => 'This annotation should never be stored',
        ]);

        $response->assertStatus(HttpCodes::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('reference_id');

        $this->assertDatabaseMissing(Annotation::TABLE_NAME, [
            'content' => 'This annotation should never be stored',
        ]);
    }

    /**
     * A reference_id belonging to the other mapped model is rejected.
     *
     * The identifier is a real UUID, but it is not a row in the table the submitted
     * reference_type resolves to.
     *
     * @see {@link AnnotationRequest::rules()}
     */
    #[Test]
    public function storeRejectsReferenceIdFromTheWrongTable(): void
    {
        $sentence = Sentence::factory()->create();

        $response = $this->postJson(route('annotations.store'), [
            'reference_id' => $sentence->sentence_id,
            'reference_type' => Paragraph::TABLE_NAME,
            'content' => 'This annotation should never be stored',
        ]);

        $response->assertStatus(HttpCodes::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('reference_id');
    }

    /**
     * An unregistered reference_type is rejected on update as well as on store.
     *
     * @see {@link AnnotationRequest::rules()}
     */
    #[Test]
    public function updateRejectsUnregisteredReferenceType(): void
    {
        $paragraph = Paragraph::factory()->create();
        $annotation = Annotation::factory()->create([
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => Paragraph::TABLE_NAME,
            'content' => 'Original content',
        ]);

        $response = $this->putJson(route('annotations.update', $annotation), [
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => 'App\\Models\\User',
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
     * The legacy fully-qualified class name is still accepted and stored as its alias.
     *
     * @see {@link AnnotationRequest::prepareForValidation()}
     */
    #[Test]
    public function storeAcceptsLegacyClassNameAndStoresTheAlias(): void
    {
        $sentence = Sentence::factory()->create();

        foreach ([Sentence::class, '\\' . Sentence::class] as $index => $referenceType) {
            $content = "Posted with a legacy class name $index";

            $response = $this->postJson(route('annotations.store'), [
                'reference_id' => $sentence->sentence_id,
                'reference_type' => $referenceType,
                'content' => $content,
            ]);

            $response->assertStatus(HttpCodes::HTTP_CREATED);

            $this->assertDatabaseHas(Annotation::TABLE_NAME, [
                'reference_id' => $sentence->sentence_id,
                'reference_type' => Sentence::TABLE_NAME,
                'content' => $content,
            ]);
        }
    }
}
