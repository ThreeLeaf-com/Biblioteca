<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Http\Resources\ChapterResource;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Chapter;
use PHPUnit\Framework\Attributes\Test;

/** Test {@link ChapterController}. */
class ChapterControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * {@link ChapterController::index()}.
     * @see {@link ChapterResource::collection()}
     */
    #[Test]
    public function indexChapter(): void
    {
        $chapters = Chapter::factory()->count(3)->create();

        $expectedData = ChapterResource::collection($chapters)->response()->getData(true);
        $expectedData['data'] = ksort($expectedData['data']);

        $response = $this->getJson(route('chapters.index'));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
    }

    /**
     * {@link ChapterController::store()}.
     * @see {@link ChapterRequest::rules()}
     * @see {@link ChapterResource::toArray()}
     */
    #[Test]
    public function storeChapter(): void
    {
        $book = Book::factory()->create();
        $data = [
            'book_id' => $book->book_id,
            'chapter_number' => $this->faker->unique()->numberBetween(1, 1000),
            'title' => $this->faker->sentence(),
            'summary' => $this->faker->paragraph(),
            'chapter_image_url' => $this->faker->url(),
        ];

        $response = $this->postJson(route('chapters.store'), $data);
        $response->assertStatus(HttpCodes::HTTP_CREATED);

        $chapter = Chapter::latest()->first();
        $expectedData = (new ChapterResource($chapter))->response()->getData(true);

        $response->assertJson($expectedData);
        $this->assertDatabaseHas(Chapter::TABLE_NAME, $data);
    }

    /**
     * {@link ChapterController::show()}.
     * @see {@link ChapterResource::toArray()}
     */
    #[Test]
    public function showChapter(): void
    {
        $chapter = Chapter::factory()->create();

        $expectedData = (new ChapterResource($chapter))->response()->getData(true);

        $response = $this->getJson(route('chapters.show', $chapter->chapter_id));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
    }

    /**
     * {@link ChapterController::update()}.
     * @see {@link ChapterRequest::rules()}
     * @see {@link ChapterResource::toArray()}
     */
    #[Test]
    public function updateChapter(): void
    {
        $chapter = Chapter::factory()->create(['title' => 'Original Title']);
        $updatedData = [
            'book_id' => $chapter->book_id,
            'chapter_number' => $this->faker->unique()->numberBetween(1, 1000),
            'title' => 'Updated Chapter Title',
            'summary' => 'Updated summary for the chapter.',
            'chapter_image_url' => $this->faker->url(),
        ];

        $response = $this->putJson(route('chapters.update', $chapter->chapter_id), $updatedData);
        $response->assertStatus(HttpCodes::HTTP_OK);

        $chapter->refresh();
        $expectedData = (new ChapterResource($chapter))->response()->getData(true);

        $response->assertJson($expectedData);
        $this->assertDatabaseHas(Chapter::TABLE_NAME, $updatedData);
    }

    /** {@link ChapterController::destroy()}. */
    #[Test]
    public function destroyChapter(): void
    {
        $chapter = Chapter::factory()->create();

        $response = $this->deleteJson(route('chapters.destroy', $chapter->chapter_id));

        $response->assertStatus(HttpCodes::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(Chapter::TABLE_NAME, [
            'chapter_id' => $chapter->chapter_id,
        ]);
    }
}
