<?php

namespace ThreeLeaf\Biblioteca\Services;

use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Models\Paragraph;

/** {@link Chapter} services. */
class ChapterService
{
    public function __construct(
        private readonly ParagraphService $paragraphService,
    )
    {
    }

    /**
     * Parses a {@link Chapter} into a {@link Paragraph} array.
     *
     * @param Chapter $chapter The chapter to parse.
     *
     * @return Paragraph[] The paragraphs
     */
    public function parseChapterContents(Chapter $chapter): array
    {
        $paragraphs = [];
        $content = $this->normalizeContent($chapter->content);
        $paragraphStrings = $this->parseToParagraphs($content);

        foreach ($paragraphStrings as $paragraphString) {
            $paragraph = Paragraph::create([
                'chapter_id' => $chapter->chapter_id,
                'paragraph_number' => count($paragraphs) + 1,
                'content' => $paragraphString,
            ]);
            $paragraphs[] = $paragraph;
            $this->paragraphService->parseParagraphContents($paragraph);
        }

        $chapter->refresh();

        return $paragraphs;
    }

    /**
     * Parses a string into paragraphs.
     *
     * @param string $content The chapter content to parse.
     *
     * @return array An array of paragraph strings.
     */
    public function parseToParagraphs(string $content): array
    {
        return preg_split("/\n+/", $content, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Combines an array of {@link Paragraph} models into a chapter's content.
     *
     * @param array $paragraphs The array of paragraph content.
     *
     * @return string The combined chapter content.
     */
    public function combineIntoChapter(array $paragraphs): string
    {
        return implode("\n", $paragraphs);
    }

    /**
     * Normalizes content by trimming and converting line breaks.
     *
     * @param string $content The chapter content to normalize.
     *
     * @return string The normalized content.
     */
    protected function normalizeContent(string $content): string
    {
        return preg_replace("/\r\n|\r|\n+/", "\n", trim($content));
    }
}
