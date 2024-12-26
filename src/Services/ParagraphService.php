<?php

namespace ThreeLeaf\Biblioteca\Services;

use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Models\Sentence;
use ThreeLeaf\Biblioteca\Repositories\ParagraphRepository;

/** {@link Paragraph} services. */
class ParagraphService
{

    public function __construct(
        private readonly ParagraphRepository $paragraphRepository,
    )
    {
    }

    /**
     * Parses a {@link Paragraph} into a {@link Sentence} array.
     *
     * @param Paragraph $paragraph The paragraph to parse.
     *
     * @return Sentence[] The sentences
     */
    public function parseParagraphContents(Paragraph $paragraph): array
    {
        $sentences = [];
        $this->paragraphRepository->deleteAllSentences($paragraph);

        if ($paragraph->content) {
            $sentenceStrings = $this->parseToSentences($paragraph->content);

            foreach ($sentenceStrings as $sentenceString) {
                $sentence = Sentence::create([
                    'paragraph_id' => $paragraph->paragraph_id,
                    'sentence_number' => count($sentences) + 1,
                    'content' => $sentenceString,
                ]);
                $sentences[] = $sentence;
            }

            $this->paragraphRepository->addSentences($paragraph, $sentences);
            $paragraph->refresh();
        }

        return $sentences;
    }

    /**
     * Parses a paragraph into an array of sentences.
     *
     * @param string $paragraph The paragraph to parse.
     *
     * @return string[] An array of sentences.
     */
    public function parseToSentences(string $paragraph): array
    {
        $normalizedParagraph = preg_replace('/\s+/', ' ', trim($paragraph));

        $pattern = '/(?<=[.!?])\s+(?=[A-Z])/';

        return preg_split($pattern, $normalizedParagraph, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Combines sentences back into a paragraph.
     *
     * @param array $sentences An array of sentences.
     *
     * @return string A combined paragraph.
     */
    public function combineIntoParagraph(array $sentences): string
    {
        return implode(' ', $sentences);
    }
}
