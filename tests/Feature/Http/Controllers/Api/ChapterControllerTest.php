<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Http\Resources\ChapterResource;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Services\ChapterService;
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

    /**
     * {@link ChapterController::store()} rejects a chapter number that the book already uses.
     * @see {@link ChapterRequest::rules()}
     */
    #[Test]
    public function storeDuplicateChapterNumberIsRejected(): void
    {
        $existing = Chapter::factory()->create();

        $response = $this->postJson(route('chapters.store'), [
            'book_id' => $existing->book_id,
            'chapter_number' => $existing->chapter_number,
            'title' => 'Another Chapter',
        ]);

        $response->assertStatus(HttpCodes::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['chapter_number' => 'This book already has a chapter with this number.']);
        $this->assertSame(1, Chapter::where('book_id', $existing->book_id)->count());
    }

    /**
     * {@link ChapterController::store()} accepts a chapter number that only another book uses.
     * @see {@link ChapterRequest::rules()}
     */
    #[Test]
    public function storeChapterNumberUsedByOtherBook(): void
    {
        $existing = Chapter::factory()->create();
        $book = Book::factory()->create();

        $response = $this->postJson(route('chapters.store'), [
            'book_id' => $book->book_id,
            'chapter_number' => $existing->chapter_number,
            'title' => 'Same Number, Other Book',
        ]);

        $response->assertStatus(HttpCodes::HTTP_CREATED);
    }

    /**
     * {@link ChapterController::update()} rejects a chapter number that another chapter of the book uses.
     * @see {@link ChapterRequest::rules()}
     */
    #[Test]
    public function updateChapterToDuplicateNumberIsRejected(): void
    {
        $book = Book::factory()->create();
        $first = Chapter::factory()->create(['book_id' => $book->book_id, 'chapter_number' => 1]);
        $second = Chapter::factory()->create(['book_id' => $book->book_id, 'chapter_number' => 2]);

        $response = $this->putJson(route('chapters.update', $second->chapter_id), [
            'book_id' => $book->book_id,
            'chapter_number' => $first->chapter_number,
            'title' => 'Renumbered',
        ]);

        $response->assertStatus(HttpCodes::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['chapter_number']);
        $this->assertDatabaseHas(Chapter::TABLE_NAME, ['chapter_id' => $second->chapter_id, 'chapter_number' => 2]);
    }

    /**
     * {@link ChapterController::update()} accepts a chapter that keeps its own number.
     * @see {@link ChapterRequest::rules()}
     */
    #[Test]
    public function updateChapterKeepingItsOwnNumber(): void
    {
        $chapter = Chapter::factory()->create();

        $response = $this->putJson(route('chapters.update', $chapter->chapter_id), [
            'book_id' => $chapter->book_id,
            'chapter_number' => $chapter->chapter_number,
            'title' => 'Retitled',
        ]);

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJsonPath('data.title', 'Retitled');
    }

    /**
     * {@link ChapterController::store()} answers 422, not 500, when a key input is an array.
     * @see {@link ChapterRequest::rules()}
     */
    #[Test]
    public function storeChapterWithArrayBookIdIsRejected(): void
    {
        $existing = Chapter::factory()->create();

        $response = $this->postJson(route('chapters.store'), [
            'book_id' => [[$existing->book_id, $existing->chapter_id]],
            'chapter_number' => $existing->chapter_number,
        ]);

        $response->assertStatus(HttpCodes::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['book_id'])
            ->assertJsonMissingValidationErrors(['chapter_number']);
    }

    /**
     * {@link ChapterController::update()} rejects a move to another book that already uses the chapter's
     * stored number, when the request omits chapter_number.
     * @see {@link ChapterRequest::rules()}
     */
    #[Test]
    public function updateChapterMovedToBookWithSameNumberIsRejected(): void
    {
        $target = Chapter::factory()->create(['chapter_number' => 3]);
        $chapter = Chapter::factory()->create(['chapter_number' => 3]);

        $response = $this->putJson(route('chapters.update', $chapter->chapter_id), [
            'book_id' => $target->book_id,
            'title' => 'Moved',
        ]);

        $response->assertStatus(HttpCodes::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'book_id' => 'The target book already has a chapter with this chapter\'s number.',
            ]);
    }

    /**
     * {@link ChapterController::update()} accepts a move to another book where the chapter's stored
     * number is free, when the request omits chapter_number.
     * @see {@link ChapterRequest::rules()}
     */
    #[Test]
    public function updateChapterMovedToBookWithFreeNumber(): void
    {
        $target = Chapter::factory()->create(['chapter_number' => 1]);
        $chapter = Chapter::factory()->create(['chapter_number' => 2]);

        $response = $this->putJson(route('chapters.update', $chapter->chapter_id), [
            'book_id' => $target->book_id,
            'title' => 'Moved',
        ]);

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJsonPath('data.book_id', $target->book_id)
            ->assertJsonPath('data.chapter_number', 2);
    }

    /**
     * {@link ChapterController::store()} answers 422, not 500, when a key input carries quotes and
     * backslashes that would otherwise add a column to the unique rule's scope.
     * The added column is qualified, because SQLite reads an unknown quoted column name as a string.
     * @see {@link ChapterRequest::rules()}
     */
    #[Test]
    public function storeChapterWithCraftedBookIdIsRejected(): void
    {
        $existing = Chapter::factory()->create();

        $response = $this->postJson(route('chapters.store'), [
            'book_id' => '\\",nope.zzz,"1',
            'chapter_number' => 1,
        ]);

        $response->assertStatus(HttpCodes::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['book_id']);
    }

    /**
     * {@link ChapterController::update()} rejects a chapter_number that is sent empty.
     * @see {@link ChapterRequest::rules()}
     */
    #[Test]
    public function updateChapterWithEmptyChapterNumberIsRejected(): void
    {
        $chapter = Chapter::factory()->create();

        $response = $this->putJson(route('chapters.update', $chapter->chapter_id), [
            'book_id' => $chapter->book_id,
            'chapter_number' => null,
            'title' => 'Retitled',
        ]);

        $response->assertStatus(HttpCodes::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['chapter_number']);
    }

    /**
     * {@link ChapterController::update()} accepts an update that omits chapter_number and keeps the
     * chapter in its own book.
     * @see {@link ChapterRequest::rules()}
     */
    #[Test]
    public function updateChapterOmittingNumberInSameBook(): void
    {
        $chapter = Chapter::factory()->create(['chapter_number' => 5]);

        $response = $this->putJson(route('chapters.update', $chapter->chapter_id), [
            'book_id' => $chapter->book_id,
            'title' => 'Retitled',
        ]);

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJsonPath('data.chapter_number', 5)
            ->assertJsonPath('data.title', 'Retitled');
    }

    /**
     * {@link ChapterController::update()} accepts a move to another book that already uses the chapter's
     * current number, when the request sends a new chapter_number that is free in that book.
     * @see {@link ChapterRequest::rules()}
     */
    #[Test]
    public function updateChapterMovedWithNewNumber(): void
    {
        $target = Chapter::factory()->create(['chapter_number' => 3]);
        $chapter = Chapter::factory()->create(['chapter_number' => 3]);

        $response = $this->putJson(route('chapters.update', $chapter->chapter_id), [
            'book_id' => $target->book_id,
            'chapter_number' => 4,
            'title' => 'Moved',
        ]);

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJsonPath('data.book_id', $target->book_id)
            ->assertJsonPath('data.chapter_number', 4);
    }

    /**
     * {@link ChapterController::store()} assigns one more than the book's highest chapter_number, or 1 for a
     * book with no chapters, when chapter_number is omitted.
     * @see {@link ChapterService::assignChapterNumber()}
     */
    #[Test]
    public function storeChapterWithoutNumberIsAutoNumbered(): void
    {
        $existing = Chapter::factory()->create(['chapter_number' => 3]);
        $emptyBook = Book::factory()->create();

        $this->postJson(route('chapters.store'), ['book_id' => $existing->book_id, 'title' => 'Next'])
            ->assertStatus(HttpCodes::HTTP_CREATED)
            ->assertJsonPath('data.chapter_number', 4);
        $this->postJson(route('chapters.store'), ['book_id' => $emptyBook->book_id, 'title' => 'First'])
            ->assertStatus(HttpCodes::HTTP_CREATED)
            ->assertJsonPath('data.chapter_number', 1);
    }
}
