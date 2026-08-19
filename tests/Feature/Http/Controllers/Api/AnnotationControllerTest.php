<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Http\Controllers\Api\AnnotationController;
use ThreeLeaf\Biblioteca\Http\Resources\AnnotationResource;
use ThreeLeaf\Biblioteca\Models\Annotation;
use ThreeLeaf\Biblioteca\Models\Paragraph;
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
            'reference_type' => Paragraph::class,
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
            'reference_type' => Paragraph::class,
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
            'reference_type' => Paragraph::class,
            'content' => 'Content to be deleted',
        ]);

        $response = $this->deleteJson(route('annotations.destroy', $annotation));

        $response->assertStatus(HttpCodes::HTTP_NOT_FOUND);
    }
}
