<?php

namespace Tests\Feature\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Services\ChapterService;

/** Test {@link ChapterService}. */
class ChapterServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @var ChapterService */
    protected ChapterService $chapterService;

    /** @test {@link ChapterService::parseChapterContents()}. */
    public function testParseChapterToParagraphs()
    {
        $chapter = Chapter::factory()->create([
            'content' => "This is the first paragraph.\r\n\r\nThis is the second paragraph.\n\nAnd the third paragraph.\n",
        ]);

        $paragraphs = $this->chapterService->parseChapterContents($chapter);

        $this->assertCount(3, $paragraphs);

        $retrievedParagraphs = Paragraph::where('chapter_id', $chapter->chapter_id)
            ->orderBy('paragraph_number')
            ->pluck('content')
            ->toArray();

        $expectedParagraphs = [
            'This is the first paragraph.',
            'This is the second paragraph.',
            'And the third paragraph.',
        ];
        $this->assertEquals($expectedParagraphs, $retrievedParagraphs);

        $combinedContent = $this->chapterService->combineIntoChapter($retrievedParagraphs);

        $expectedCombinedContent = "This is the first paragraph.\nThis is the second paragraph.\nAnd the third paragraph.";
        $this->assertEquals($expectedCombinedContent, $combinedContent);
    }

    /** @test {@link ChapterService::assignChapterNumber()} defaults to 1. */
    public function assignChapterNumberDefault()
    {
        $data = ['book_id' => fake()->uuid()];
        $result = $this->chapterService->assignChapterNumber($data);

        $this->assertEquals(1, $result['chapter_number']);
    }

    /** @test {@link ChapterService::assignChapterNumber()} replaces zero chapter number. */
    public function assignChapterNumberZero()
    {
        $data = [
            'book_id' => fake()->uuid(),
            'chapter_number' => 0,
        ];

        $result = $this->chapterService->assignChapterNumber($data);

        $this->assertEquals(1, $result['chapter_number']);
    }

    /** @test {@link ChapterService::assignChapterNumber()} replaces negative chapter number. */
    public function assignChapterNumberNegative()
    {
        $data = [
            'book_id' => fake()->uuid(),
            'chapter_number' => -1,
        ];

        $result = $this->chapterService->assignChapterNumber($data);

        $this->assertEquals(1, $result['chapter_number']);
    }

    /** @test {@link ChapterService::assignChapterNumber()} replaces non-numeric chapter number. */
    public function assignChapterNumberNaN()
    {
        $data = [
            'book_id' => fake()->uuid(),
            'chapter_number' => 'non-numeric',
        ];

        $result = $this->chapterService->assignChapterNumber($data);

        $this->assertEquals(1, $result['chapter_number']);
    }

    /** @test {@link ChapterService::assignChapterNumber()} increments last chapter number. */
    public function assignChapterNumberIncrement()
    {
        $chapterService = app(ChapterService::class);
        $bookId = Book::factory()->create()->book_id;

        for ($chapterNumber = 1; $chapterNumber <= 10; $chapterNumber++) {
            Chapter::factory()->create(['book_id' => $bookId, 'chapter_number' => $chapterNumber]);
        }

        $data = ['book_id' => $bookId];
        $result = $chapterService->assignChapterNumber($data);

        $this->assertEquals(11, $result['chapter_number']);
    }

    /** @test {@link ChapterService::updateOrCreate()} creates new chapter. */
    public function updateOrCreateNew()
    {
        $book = Book::factory()->create();
        $data = [
            'book_id' => $book->book_id,
            'content' => "This is the first paragraph.\nThis is the second paragraph.\nAnd the third paragraph.",
        ];

        $chapter = $this->chapterService->updateOrCreate($data);

        $this->assertEquals($data['book_id'], $chapter->book_id);
        $this->assertEquals(1, $chapter->chapter_number);

        $retrievedParagraphs = Paragraph::where('chapter_id', $chapter->chapter_id)
            ->orderBy('paragraph_number')
            ->pluck('content')
            ->toArray();

        $expectedParagraphs = [
            'This is the first paragraph.',
            'This is the second paragraph.',
            'And the third paragraph.',
        ];
        $this->assertEquals($expectedParagraphs, $retrievedParagraphs);

        $combinedContent = $this->chapterService->combineIntoChapter($retrievedParagraphs);

        $expectedCombinedContent = "This is the first paragraph.\nThis is the second paragraph.\nAnd the third paragraph.";
        $this->assertEquals($expectedCombinedContent, $combinedContent);
    }

    /** @test {@link ChapterService::updateOrCreate()} updates existing chapter. */
    public function updateOrCreateExisting()
    {
        $chapter = Chapter::factory()->create();
        $chapter_number = $chapter->chapter_number;

        $retrievedParagraphs = Paragraph::where('chapter_id', $chapter->chapter_id)
            ->orderBy('paragraph_number')
            ->pluck('content')
            ->toArray();
        $oldParagraphs = $this->chapterService->combineIntoChapter($retrievedParagraphs);

        $data = [
            'book_id' => $chapter->book_id,
            'chapter_number' => $chapter_number,
            'content' => "This is the first paragraph.\nThis is the second paragraph.\nAnd the third paragraph.",
        ];

        $chapter = $this->chapterService->updateOrCreate($data);

        $this->assertEquals($data['book_id'], $chapter->book_id);
        $this->assertEquals($chapter_number, $chapter->chapter_number);

        $retrievedParagraphs = Paragraph::where('chapter_id', $chapter->chapter_id)
            ->orderBy('paragraph_number')
            ->pluck('content')
            ->toArray();

        $expectedParagraphs = [
            'This is the first paragraph.',
            'This is the second paragraph.',
            'And the third paragraph.',
        ];
        $this->assertEquals($expectedParagraphs, $retrievedParagraphs);

        $combinedContent = $this->chapterService->combineIntoChapter($retrievedParagraphs);

        $expectedCombinedContent = "This is the first paragraph.\nThis is the second paragraph.\nAnd the third paragraph.";
        $this->assertEquals($expectedCombinedContent, $combinedContent);
        $this->assertNotEquals($oldParagraphs, $combinedContent);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->chapterService = app(ChapterService::class);
    }
}
