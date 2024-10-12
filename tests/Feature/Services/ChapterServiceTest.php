<?php

namespace Tests\Feature\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Services\ChapterService;

/** Test {@link ChapterService} */
class ChapterServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @var ChapterService */
    protected ChapterService $service;

    /**
     * Test parsing a chapter into paragraphs and verifying round-trip integrity.
     */
    public function testParseChapterToParagraphs()
    {
        $chapter = Chapter::factory()->create([
            'content' => "This is the first paragraph.\r\n\r\nThis is the second paragraph.\n\nAnd the third paragraph.\n",
        ]);

        $paragraphs = $this->service->parseChapterContents($chapter);

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

        $combinedContent = $this->service->combineIntoChapter($retrievedParagraphs);

        $expectedCombinedContent = "This is the first paragraph.\nThis is the second paragraph.\nAnd the third paragraph.";
        $this->assertEquals($expectedCombinedContent, $combinedContent);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ChapterService::class);
    }
}
