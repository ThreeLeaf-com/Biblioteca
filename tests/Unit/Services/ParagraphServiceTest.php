<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use ThreeLeaf\Biblioteca\Services\ParagraphService;

/** Test {@link ParagraphService} */
class ParagraphServiceTest extends TestCase
{
    /** @var ParagraphService */
    protected ParagraphService $service;

    /** @test {@link ParagraphService::parseParagraphContents()} parsing a simple paragraph into sentences. */
    public function testParseSimpleParagraph()
    {
        $paragraph = 'This is a sentence. Here is another one! And a question?';
        $expected = [
            'This is a sentence.',
            'Here is another one!',
            'And a question?',
        ];

        $result = $this->service->parseToSentences($paragraph);
        $this->assertEquals($expected, $result);

        $reconstitutedParagraph = $this->service->combineIntoParagraph($result);
        $this->assertEquals($paragraph, $reconstitutedParagraph);
    }

    /** @test {@link ParagraphService::parseParagraphContents()} parsing a paragraph with abbreviations. */
    public function testParseParagraphWithAbbreviations()
    {
        $paragraph = 'Dr. Smith went to Washington. He arrived at 9 a.m. sharp. It was a great trip!';
        $expected = [
            /* Note that sentence detection is not perfect... */
            'Dr.',
            'Smith went to Washington.',
            'He arrived at 9 a.m. sharp.',
            'It was a great trip!',
        ];

        $result = $this->service->parseToSentences($paragraph);
        $this->assertEquals($expected, $result);

        $reconstitutedParagraph = $this->service->combineIntoParagraph($result);
        $this->assertEquals($paragraph, $reconstitutedParagraph);
    }

    /** @test {@link ParagraphService::parseParagraphContents()} parsing a paragraph with multiple spaces between sentences. */
    public function testParseParagraphWithMultipleSpaces()
    {
        $paragraph = 'This is the first sentence.    This is the second sentence.  And the third.';
        $expected = [
            'This is the first sentence.',
            'This is the second sentence.',
            'And the third.',
        ];

        $result = $this->service->parseToSentences($paragraph);
        $this->assertEquals($expected, $result);
    }

    /** @test {@link ParagraphService::combineIntoParagraph()} combining sentences back into a paragraph. */
    public function testCombineSentencesIntoParagraph()
    {
        $sentences = [
            'This is the first sentence.',
            'This is the second one.',
            'And finally, the third.',
        ];
        $expected = 'This is the first sentence. This is the second one. And finally, the third.';

        $result = $this->service->combineIntoParagraph($sentences);
        $this->assertEquals($expected, $result);
    }

    /** @test {@link ParagraphService::parseParagraphContents()} parsing and recombining a paragraph to ensure data integrity. */
    public function testParseAndCombineRoundTrip()
    {
        $paragraph = 'The quick brown fox jumps over the lazy dog. Mr. John Doe lives at 123 Elm St. Can you believe it?';
        $expected = 'The quick brown fox jumps over the lazy dog. Mr. John Doe lives at 123 Elm St. Can you believe it?';

        $sentences = $this->service->parseToSentences($paragraph);
        $recombinedParagraph = $this->service->combineIntoParagraph($sentences);

        $this->assertEquals($expected, $recombinedParagraph);
    }

    /** @test {@link ParagraphService::parseParagraphContents()} handling of edge cases with trailing spaces. */
    public function testParseParagraphWithTrailingSpaces()
    {
        $paragraph = 'This is a test.   ';
        $expected = ['This is a test.'];

        $result = $this->service->parseToSentences($paragraph);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test parsing an empty paragraph.
     */
    public function testParseEmptyParagraph()
    {
        $paragraph = '';
        $expected = [];

        $result = $this->service->parseToSentences($paragraph);
        $this->assertEquals($expected, $result);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ParagraphService();
    }
}
