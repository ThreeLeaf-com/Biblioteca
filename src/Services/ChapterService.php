<?php

namespace ThreeLeaf\Biblioteca\Services;

use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Repositories\ChapterRepository;

/** {@link Chapter} services. */
class ChapterService
{
    public function __construct(
        private readonly ChapterRepository $chapterRepository,
        private readonly ParagraphService  $paragraphService,
    )
    {
    }

    /**
     * Creates a new Chapter and parses its content into Paragraphs.
     *
     * @param mixed $data The data to create the Chapter with.
     *
     * @return Chapter The newly created Chapter with its Paragraphs parsed.
     */
    public function create(mixed $data): Chapter
    {
        $data = $this->assignChapterNumber($data);
        $chapter = $this->chapterRepository->create($data);
        $this->parseChapterContents($chapter);

        return $chapter;
    }

    /**
     * Retrieves all Chapters from the database.
     *
     * This function uses the ChapterRepository to fetch all Chapters from the database.
     * If a bookId is provided, it will filter the Chapters by the given bookId.
     *
     * @param string|null $bookId The ID of the book to filter Chapters by. If null, all Chapters will be returned.
     *
     * @return Chapter[] An array of Chapter models.
     */
    public function getAll(?string $bookId = null): array
    {
        return $this->chapterRepository->readAll($bookId);
    }

    /**
     * Updates a Chapter and parses its content into Paragraphs.
     *
     * @param Chapter $chapter The Chapter to update.
     * @param array   $data    The data to update the Chapter with.
     *
     * @return Chapter The updated Chapter with its Paragraphs parsed.
     */
    public function update(Chapter $chapter, array $data): Chapter
    {
        $chapter = $this->chapterRepository->update($chapter, $data);
        $this->parseChapterContents($chapter);

        return $chapter;
    }

    /**
     * Creates a new Chapter and parses its content into Paragraphs.
     * If a Chapter with the same book_id and chapter_number already exists, it will be updated.
     *
     * @param mixed $data The data to create or update the Chapter with.
     *
     * @return Chapter The newly created or updated Chapter with its Paragraphs parsed.
     */
    public function updateOrCreate(mixed $data): Chapter
    {
        $data = $this->assignChapterNumber($data);
        $chapter = $this->chapterRepository->updateOrCreate($data);
        $this->parseChapterContents($chapter);

        return $chapter;
    }

    /**
     * Deletes a Chapter and all its associated Paragraphs.
     *
     * This function takes a Chapter model as a parameter and deletes it from the database.
     * It also deletes all Paragraphs associated with the given Chapter.
     *
     * @param Chapter $chapter The Chapter to delete.
     *
     * @return bool True if the chapter was successfully deleted, false otherwise.
     */
    public function delete(Chapter $chapter): bool
    {
        return $this->chapterRepository->deleteChapter($chapter);
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
        $this->chapterRepository->deleteAllParagraphs($chapter);

        if ($chapter->content) {
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

            $this->chapterRepository->addParagraphs($chapter, $paragraphs);
            $chapter->refresh();
        }

        return $paragraphs;
    }

    /**
     * Assigns a chapter number to the chapter data.
     *
     * If the 'chapter_number' is not provided in the input data, the function will attempt to determine the next available chapter number for the given book.
     * It does this by fetching the highest chapter number for the book from the database and incrementing it by one.
     * If no chapters exist for the book, the function will default to chapter number 1.
     *
     * @param array $data the chapter data
     *
     * @return array the updated chapter data
     */
    public function assignChapterNumber(array $data): array
    {
        if (empty($data['chapter_number']) || !is_numeric($data['chapter_number']) || $data['chapter_number'] < 1) {
            $lastChapter = Chapter::where('book_id', $data['book_id'])
                ->orderBy('chapter_number', 'desc')
                ->first();

            if ($lastChapter) {
                $data['chapter_number'] = $lastChapter->chapter_number + 1;
            } else {
                $data['chapter_number'] = 1;
            }
        }

        return $data;
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

    public function findOrFail(string $chapter_id): Chapter
    {
        return $this->chapterRepository->readOrFail($chapter_id);
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
