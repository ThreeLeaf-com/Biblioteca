<?php

namespace Tests\Feature\Repositories;

use ErrorException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Repositories\ChapterRepository;

/** Test {@link ChapterRepository}. */
class ChapterRepositoryTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @var ChapterRepository The test repository. */
    private ChapterRepository $chapterRepository;

    /** @test {@link ChapterRepository::create()} with missing book ID. */
    public function createMissingBookId()
    {
        $data = [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
        ];

        $this->expectException(ErrorException::class);
        $this->chapterRepository->create($data);
    }

    /** @test {@link ChapterRepository::create()} autoincrement chapter number. */
    public function createAutoincrementChapterNumber()
    {
        $chapter1 = Chapter::factory()->create(['chapter_number' => 1]);
        $book = $chapter1->book;
        $bookId = $book->book_id;
        Chapter::factory()->create([
            'book_id' => $bookId,
            'chapter_number' => 2,
        ]);
        $newChapterData = [
            'book_id' => $bookId,
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
        ];

        $newChapter = $this->chapterRepository->create($newChapterData);

        $this->assertEquals(3, $newChapter->chapter_number);
    }

    /** @test {@link ChapterRepository::create()} override negative chapter number. */
    public function createNegativeChapterNumber()
    {
        $chapter1 = Chapter::factory()->create(['chapter_number' => 1]);
        $book = $chapter1->book;
        $bookId = $book->book_id;
        Chapter::factory()->create([
            'book_id' => $bookId,
            'chapter_number' => 2,
        ]);
        $newChapterData = [
            'book_id' => $bookId,
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'chapter_number' => $this->faker->numberBetween(-100, -1),
        ];

        $newChapter = $this->chapterRepository->create($newChapterData);

        $this->assertEquals(3, $newChapter->chapter_number);
    }

    /** @test {@link ChapterRepository::create()} override non-numeric chapter number. */
    public function createNanChapterNumber()
    {
        $chapter1 = Chapter::factory()->create(['chapter_number' => 1]);
        $book = $chapter1->book;
        $bookId = $book->book_id;
        $newChapterData = [
            'book_id' => $bookId,
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'chapter_number' => $this->faker->randomLetter(),
        ];

        $newChapter = $this->chapterRepository->create($newChapterData);

        $this->assertEquals(2, $newChapter->chapter_number);
    }

    /** @test {@link ChapterRepository::create()} first chapter number. */
    public function createFirstChapterNumber()
    {
        $book = Book::factory()->create();
        $bookId = $book->book_id;
        $newChapterData = [
            'book_id' => $bookId,
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
        ];

        $newChapter = $this->chapterRepository->create($newChapterData);

        $this->assertEquals(1, $newChapter->chapter_number);
    }

    /** @test {@link ChapterRepository::create()} allow duplicate chapter number. */
    public function createDuplicateChapterNumber()
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

        $newChapter = $this->chapterRepository->create($newChapterData);

        $this->assertEquals(2, $newChapter->chapter_number);
        $this->assertCount(3, $chapter2->book->chapters);
    }

    /** @test {@link ChapterRepository::read()}. */
    public function read()
    {
        $chapter = Chapter::factory()->create();

        $chapterRetrieved = $this->chapterRepository->read($chapter->chapter_id);

        $this->assertTrue($chapter->equals($chapterRetrieved));
    }

    /** @test {@link ChapterRepository::read()} non-existent. */
    public function readNonExistentChapterId()
    {
        $nonExistentChapterId = 'non-existent-chapter-id';

        $chapter = $this->chapterRepository->read($nonExistentChapterId);

        $this->assertNull($chapter);
    }

    /** @test {@link ChapterRepository::readOrFail()} non-existent. */
    public function readOrFail()
    {
        $chapter = Chapter::factory()->create();

        $retrievedChapter = $this->chapterRepository->readOrFail($chapter->chapter_id);

        $this->assertTrue($chapter->equals($retrievedChapter));
    }

    /** @test {@link ChapterRepository::readOrFail()} non-existent. */
    public function readOrFailDoesNotExist()
    {
        $nonExistentChapterId = 'non-existent-chapter-id';

        $this->expectException(ModelNotFoundException::class);

        $this->chapterRepository->readOrFail($nonExistentChapterId);
    }

    /** @test {@link ChapterRepository::readAll()} */
    public function readAll()
    {
        $numberOfChapters = $this->faker->numberBetween(1, 10);
        Chapter::factory()->count($numberOfChapters)->create();

        $chapters = $this->chapterRepository->readAll();

        $this->assertCount($numberOfChapters, $chapters);
    }

    /** @test {@link ChapterRepository::readAll()} no chapters. */
    public function readAllNoChapters()
    {
        $this->assertEmpty($this->chapterRepository->readAll());
    }

    /** @test {@link ChapterRepository::readAll()} with Book::$id. */
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

    /** @test {@link ChapterRepository::update()} no chapters. */
    public function update()
    {
        $chapter = Chapter::factory()->create();
        $newData = [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'chapter_number' => $this->faker->numberBetween(1, 10),
            'book_id' => $chapter->book_id,
        ];

        $this->assertNotEquals($newData, $chapter->only(['title', 'content', 'chapter_number', 'book_id']));

        $updatedChapter = $this->chapterRepository->update($chapter, $newData);

        $this->assertEquals($chapter->chapter_id, $updatedChapter->chapter_id);
        $this->assertEquals($newData, $chapter->only(['title', 'content', 'chapter_number', 'book_id']));
    }

    /** @test {@link ChapterRepository::delete()}. */
    public function deleteChapterId()
    {
        $chapter = Chapter::factory()->create();

        $this->assertTrue($this->chapterRepository->delete($chapter->chapter_id));
    }

    /** @test {@link ChapterRepository::delete()} non-existent. */
    public function deleteChapterDoesNotExist()
    {
        $nonExistentChapterId = 'non-existent-chapter-id';

        $this->assertFalse($this->chapterRepository->delete($nonExistentChapterId));
    }

    /** @test {@link ChapterRepository::deleteAllParagraphs()}. */
    public function deleteAllParagraphs()
    {
        $chapter = Chapter::factory()->create();
        Paragraph::factory()->count(3)->create(['chapter_id' => $chapter->chapter_id]);

        $this->assertCount(3, $chapter->paragraphs);

        $this->chapterRepository->deleteAllParagraphs($chapter);

        $this->assertCount(0, Chapter::find($chapter->chapter_id)->paragraphs);
    }

    /** @test {@link ChapterRepository::addParagraphs()}. */
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
