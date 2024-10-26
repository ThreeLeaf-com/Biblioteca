<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Http\Resources\ParagraphResource;
use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Models\Paragraph;

/** Test {@link ParagraphController}. */
class ParagraphControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test {@link ParagraphController::index()}.
     * @see {@link ParagraphResource::collection()}
     */
    public function indexParagraph(): void
    {
        $paragraphs = Paragraph::factory()->count(3)->create();

        $expectedData = ParagraphResource::collection($paragraphs)->response()->getData(true);

        $response = $this->getJson(route('paragraphs.index'));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
    }

    /**
     * @test {@link ParagraphController::store()}.
     * @see {@link ParagraphRequest::rules()}
     * @see {@link ParagraphResource::toArray()}
     */
    public function storeParagraph(): void
    {
        $chapter = Chapter::factory()->create();
        $data = [
            'chapter_id' => $chapter->chapter_id,
            'paragraph_number' => $this->faker->numberBetween(1, 100),
            'content' => $this->faker->paragraph(),
        ];

        $response = $this->postJson(route('paragraphs.store'), $data);
        $response->assertStatus(HttpCodes::HTTP_CREATED);

        $paragraph = Paragraph::latest()->first();
        $expectedData = (new ParagraphResource($paragraph))->response()->getData(true);

        $response->assertJson($expectedData);
        $this->assertDatabaseHas(Paragraph::TABLE_NAME, $data);
    }

    /**
     * @test {@link ParagraphController::show()}.
     * @see {@link ParagraphResource::toArray()}
     */
    public function showParagraph(): void
    {
        $paragraph = Paragraph::factory()->create();

        $expectedData = (new ParagraphResource($paragraph))->response()->getData(true);

        $response = $this->getJson(route('paragraphs.show', $paragraph->paragraph_id));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
    }

    /**
     * @test {@link ParagraphController::update()}.
     * @see {@link ParagraphRequest::rules()}
     * @see {@link ParagraphResource::toArray()}
     */
    public function updateParagraph(): void
    {
        $paragraph = Paragraph::factory()->create(['content' => 'Original content']);
        $updatedData = [
            'chapter_id' => $paragraph->chapter_id,
            'paragraph_number' => $this->faker->numberBetween(1, 100),
            'content' => 'Updated paragraph content',
        ];

        $response = $this->putJson(route('paragraphs.update', $paragraph->paragraph_id), $updatedData);
        $response->assertStatus(HttpCodes::HTTP_OK);

        $paragraph->refresh();
        $expectedData = (new ParagraphResource($paragraph))->response()->getData(true);

        $response->assertJson($expectedData);
        $this->assertDatabaseHas(Paragraph::TABLE_NAME, $updatedData);
    }

    /**
     * @test {@link ParagraphController::destroy()}.
     */
    public function destroyParagraph(): void
    {
        $paragraph = Paragraph::factory()->create();

        $response = $this->deleteJson(route('paragraphs.destroy', $paragraph->paragraph_id));

        $response->assertStatus(HttpCodes::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(Paragraph::TABLE_NAME, [
            'paragraph_id' => $paragraph->paragraph_id,
        ]);
    }
}
