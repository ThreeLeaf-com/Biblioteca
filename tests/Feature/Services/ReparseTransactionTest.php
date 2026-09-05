<?php

namespace Tests\Feature\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Models\Sentence;
use ThreeLeaf\Biblioteca\Repositories\ChapterRepository;
use ThreeLeaf\Biblioteca\Repositories\ParagraphRepository;
use ThreeLeaf\Biblioteca\Services\ChapterService;
use ThreeLeaf\Biblioteca\Services\ParagraphService;
use PHPUnit\Framework\Attributes\Test;

/** A paragraph service that fails after the delete, standing in for a mid-rebuild error. */
class FailingParagraphService extends ParagraphService
{

    /**
     * Fail as though the rebuild had died part-way through.
     *
     * @param Paragraph $paragraph The paragraph being parsed.
     *
     * @return Sentence[] Never returns.
     */
    public function parseParagraphContents(Paragraph $paragraph): array
    {
        throw new RuntimeException('Rebuild failed part-way through.');
    }
}

/**
 * A paragraph service that fails between the delete and the rebuild.
 *
 * The failure has to land here rather than on the repository's `addSentences()`: the
 * sentences are persisted individually by `Sentence::create()` inside the loop, so a
 * throw at `addSentences()` happens after they already exist and would prove nothing.
 */
class FailingSentenceParser extends ParagraphService
{

    /**
     * Fail after {@link ParagraphRepository::deleteAllSentences()} and before any rebuild.
     *
     * @param string $paragraph The paragraph content being split.
     *
     * @return string[] Never returns.
     */
    public function parseToSentences(string $paragraph): array
    {
        throw new RuntimeException('Rebuild failed part-way through.');
    }
}

/**
 * Test that a failed re-parse leaves the existing rows intact.
 *
 * Both services delete every child row before writing replacements, because the child tables
 * are unique on their position columns. Without a transaction a failure between the two
 * steps is unrecoverable data loss.
 */
class ReparseTransactionTest extends TestCase
{

    use RefreshDatabase;

    /**
     * Build a chapter whose content parses into several paragraphs.
     *
     * @return Chapter The persisted chapter.
     */
    private function chapterWithParagraphs(): Chapter
    {
        return Chapter::factory()->create([
            'content' => "First paragraph. It has two sentences.\n"
                . "Second paragraph. It also has two.\n"
                . 'Third paragraph. And so does this one.',
        ]);
    }

    /** A chapter re-parse that fails part-way through leaves the paragraphs intact. */
    #[Test]
    public function failedChapterReparseKeepsExistingParagraphs(): void
    {
        $chapter = $this->chapterWithParagraphs();
        app(ChapterService::class)->parseChapterContents($chapter);

        $before = Paragraph::where('chapter_id', $chapter->chapter_id)
            ->orderBy('paragraph_number')
            ->pluck('content', 'paragraph_number')
            ->all();
        $this->assertCount(3, $before);

        $service = new ChapterService(
            app(ChapterRepository::class),
            app(FailingParagraphService::class),
        );

        try {
            $service->parseChapterContents($chapter);
            $this->fail('The re-parse was expected to fail.');
        } catch (RuntimeException) {
            /* Expected; the assertions below are the point of the test. */
        }

        $after = Paragraph::where('chapter_id', $chapter->chapter_id)
            ->orderBy('paragraph_number')
            ->pluck('content', 'paragraph_number')
            ->all();

        $this->assertSame($before, $after, 'The paragraphs were not restored by the rollback.');
    }

    /** The sentences beneath those paragraphs survive the same failure. */
    #[Test]
    public function failedChapterReparseKeepsExistingSentences(): void
    {
        $chapter = $this->chapterWithParagraphs();
        app(ChapterService::class)->parseChapterContents($chapter);

        $before = Sentence::whereIn(
            'paragraph_id',
            Paragraph::where('chapter_id', $chapter->chapter_id)->pluck('paragraph_id')
        )->count();
        $this->assertGreaterThan(0, $before);

        $service = new ChapterService(
            app(ChapterRepository::class),
            app(FailingParagraphService::class),
        );

        try {
            $service->parseChapterContents($chapter);
            $this->fail('The re-parse was expected to fail.');
        } catch (RuntimeException) {
            /* Expected. */
        }

        $after = Sentence::whereIn(
            'paragraph_id',
            Paragraph::where('chapter_id', $chapter->chapter_id)->pluck('paragraph_id')
        )->count();

        $this->assertSame($before, $after, 'The sentences were not restored by the rollback.');
    }

    /** A paragraph re-parse that fails part-way through leaves the sentences intact. */
    #[Test]
    public function failedParagraphReparseKeepsExistingSentences(): void
    {
        $paragraph = Paragraph::factory()->create([
            'content' => 'First sentence here. Second sentence here. Third sentence here.',
        ]);
        app(ParagraphService::class)->parseParagraphContents($paragraph);

        $before = Sentence::where('paragraph_id', $paragraph->paragraph_id)
            ->orderBy('sentence_number')
            ->pluck('content', 'sentence_number')
            ->all();
        $this->assertCount(3, $before);

        $service = new FailingSentenceParser(app(ParagraphRepository::class));

        try {
            $service->parseParagraphContents($paragraph);
            $this->fail('The re-parse was expected to fail.');
        } catch (RuntimeException) {
            /* Expected. */
        }

        $after = Sentence::where('paragraph_id', $paragraph->paragraph_id)
            ->orderBy('sentence_number')
            ->pluck('content', 'sentence_number')
            ->all();

        $this->assertSame($before, $after, 'The sentences were not restored by the rollback.');
    }

    /** A successful re-parse still replaces the children, so the rollback did not disable it. */
    #[Test]
    public function successfulReparseStillReplacesChildren(): void
    {
        $chapter = $this->chapterWithParagraphs();
        app(ChapterService::class)->parseChapterContents($chapter);
        $this->assertSame(3, Paragraph::where('chapter_id', $chapter->chapter_id)->count());

        $chapter->update(['content' => 'Only one paragraph now. With two sentences.']);
        app(ChapterService::class)->parseChapterContents($chapter);

        $paragraphIds = Paragraph::where('chapter_id', $chapter->chapter_id)->pluck('paragraph_id');
        $this->assertCount(1, $paragraphIds);

        /* Without this the paragraph rebuild could pass while sentence parsing did nothing. */
        $this->assertSame(2, Sentence::whereIn('paragraph_id', $paragraphIds)->count());
    }
}
