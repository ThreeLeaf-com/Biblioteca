<?php

namespace Tests\Feature\Repositories;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Repositories\ChapterRepository;
use PHPUnit\Framework\Attributes\Test;

/** Test {@link ChapterRepository}. */
class ChapterRepositoryTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @var ChapterRepository The test repository. */
    private ChapterRepository $chapterRepository;

    /** {@link ChapterRepository::create()} with missing book ID. */
    #[Test]
    public function requireMissingBookId()
    {
        $data = [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
        ];

        $this->expectException(QueryException::class);
        $this->chapterRepository->create($data);
    }

    /** {@link ChapterRepository::create()} does not allow duplicate chapter number. */
    #[Test]
    public function disallowDuplicateChapterNumber()
    {
        $chapter1 = Chapter::factory()->create(['chapter_number' => 1]);
        $bookId = $chapter1->book_id;
        $chapter2 = Chapter::factory()->create([
            'book_id' => $bookId,
            'chapter_number' => 2,
        ]);
        $newChapterData = [
            'book_id' => $bookId,
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'chapter_number' => 2,
        ];

        $this->expectException(UniqueConstraintViolationException::class);

        $this->chapterRepository->create($newChapterData);
    }

    /** {@link ChapterRepository::read()}. */
    #[Test]
    public function read()
    {
        $chapter = Chapter::factory()->create();

        $chapterRetrieved = $this->chapterRepository->read($chapter->chapter_id);

        $this->assertTrue($chapter->equals($chapterRetrieved));
    }

    /** {@link ChapterRepository::read()} non-existent. */
    #[Test]
    public function readNonExistentChapterId()
    {
        $nonExistentChapterId = 'non-existent-chapter-id';

        $chapter = $this->chapterRepository->read($nonExistentChapterId);

        $this->assertNull($chapter);
    }

    /** {@link ChapterRepository::readOrFail()} non-existent. */
    #[Test]
    public function readOrFail()
    {
        $chapter = Chapter::factory()->create();

        $retrievedChapter = $this->chapterRepository->readOrFail($chapter->chapter_id);

        $this->assertTrue($chapter->equals($retrievedChapter));
    }

    /** {@link ChapterRepository::readOrFail()} non-existent. */
    #[Test]
    public function readOrFailDoesNotExist()
    {
        $nonExistentChapterId = 'non-existent-chapter-id';

        $this->expectException(ModelNotFoundException::class);

        $this->chapterRepository->readOrFail($nonExistentChapterId);
    }

    /** {@link ChapterRepository::readAll()} */
    #[Test]
    public function readAll()
    {
        $numberOfChapters = $this->faker->numberBetween(1, 10);
        Chapter::factory()->count($numberOfChapters)->create();

        $chapters = $this->chapterRepository->readAll();

        $this->assertCount($numberOfChapters, $chapters);
    }

    /** {@link ChapterRepository::readAll()} no chapters. */
    #[Test]
    public function readAllNoChapters()
    {
        $this->assertEmpty($this->chapterRepository->readAll());
    }

    /** {@link ChapterRepository::readAll()} with Book::$id. */
    #[Test]
    public function readAllBookId()
    {
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();
        Chapter::factory()->count(3)->create(['book_id' => $book1->book_id]);
        Chapter::factory()->count(2)->create(['book_id' => $book2->book_id]);

        $chaptersForBook1 = $this->chapterRepository->readAll($book1->book_id);

        $this->assertCount(3, $chaptersForBook1);

        $chapterIdsForBook1 = $book1->chapters()->pluck('chapter_id')->toArray();
        $chapterIdsForBook1Database = collect($chaptersForBook1)->pluck('chapter_id')->toArray();

        $this->assertEquals($chapterIdsForBook1Database, $chapterIdsForBook1);
    }

    /** {@link ChapterRepository::update()} no chapters. */
    #[Test]
    public function update()
    {
        $chapter = Chapter::factory()->create();
        $newData = [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'chapter_number' => $this->faker->unique()->numberBetween(1, 1000),
            'book_id' => $chapter->book_id,
        ];

        $this->assertNotEquals($newData, $chapter->only(['title', 'content', 'chapter_number', 'book_id']));

        $updatedChapter = $this->chapterRepository->update($chapter, $newData);

        $this->assertEquals($chapter->chapter_id, $updatedChapter->chapter_id);
        $this->assertEquals($newData, $chapter->only(['title', 'content', 'chapter_number', 'book_id']));
    }

    /** {@link ChapterRepository::updateOrCreate()}. */
    #[Test]
    public function updateOrCreateNew()
    {
        $book = Book::factory()->create();
        $newChapterData = [
            'book_id' => $book->book_id,
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'chapter_number' => $this->faker->unique()->numberBetween(1, 1000),
        ];

        $createdChapter = $this->chapterRepository->updateOrCreate($newChapterData);

        $this->assertNotNull($createdChapter->chapter_id);
        $this->assertEquals($newChapterData['book_id'], $createdChapter->book_id);
        $this->assertEquals($newChapterData['title'], $createdChapter->title);
        $this->assertEquals($newChapterData['content'], $createdChapter->content);
        $this->assertEquals($newChapterData['chapter_number'], $createdChapter->chapter_number);
    }

    /** {@link ChapterRepository::updateOrCreate()} existing. */
    #[Test]
    public function updateOrCreateExisting()
    {
        $chapter = Chapter::factory()->create();
        $newData = [
            'book_id' => $chapter->book_id,
            'chapter_number' => $chapter->chapter_number,
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
        ];

        $updatedChapter = $this->chapterRepository->updateOrCreate($newData);

        $this->assertEquals($chapter->chapter_id, $updatedChapter->chapter_id);
        $this->assertEquals($newData['book_id'], $updatedChapter->book_id);
        $this->assertEquals($newData['chapter_number'], $updatedChapter->chapter_number);
        $this->assertEquals($newData['title'], $updatedChapter->title);
        $this->assertEquals($newData['content'], $updatedChapter->content);
    }

    /** {@link ChapterRepository::delete()}. */
    #[Test]
    public function deleteChapterId()
    {
        $chapter = Chapter::factory()->create();

        $this->assertTrue($this->chapterRepository->delete($chapter->chapter_id));
    }

    /** {@link ChapterRepository::delete()} non-existent. */
    #[Test]
    public function deleteChapterDoesNotExist()
    {
        $nonExistentChapterId = 'non-existent-chapter-id';

        $this->assertFalse($this->chapterRepository->delete($nonExistentChapterId));
    }

    /** {@link ChapterRepository::deleteAllParagraphs()}. */
    #[Test]
    public function deleteAllParagraphs()
    {
        $chapter = Chapter::factory()->create();
        Paragraph::factory()->count(3)->create(['chapter_id' => $chapter->chapter_id]);

        $this->assertCount(3, $chapter->paragraphs);

        $this->chapterRepository->deleteAllParagraphs($chapter);

        $this->assertCount(0, Chapter::find($chapter->chapter_id)->paragraphs);
    }

    /** {@link ChapterRepository::addParagraphs()}. */
    #[Test]
    public function addParagraphs()
    {
        $chapter = Chapter::factory()->create();
        $paragraphs = Paragraph::factory()->count(3)->create()->all();

        $this->assertCount(0, $chapter->paragraphs);

        $this->chapterRepository->addParagraphs($chapter, $paragraphs);

        $this->assertCount(3, Chapter::find($chapter->chapter_id)->paragraphs);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->chapterRepository = app(ChapterRepository::class);
    }
}
