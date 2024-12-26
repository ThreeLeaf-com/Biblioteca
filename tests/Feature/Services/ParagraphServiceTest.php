<?php

namespace Tests\Feature\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Models\Sentence;
use ThreeLeaf\Biblioteca\Services\ParagraphService;

/** Test {@link ParagraphService} */
class ParagraphServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @var ParagraphService */
    protected ParagraphService $paragraphService;

    /** @test {@link ParagraphService::parseParagraphContents()} parsing a paragraph into sentences and verifying round-trip integrity. */
    public function testParseParagraphToSentences()
    {
        $paragraph = Paragraph::factory()->create([
            'content' => 'This is the first sentence. Here is the second one! And the final sentence?',
        ]);

        $sentences = $this->paragraphService->parseParagraphContents($paragraph);

        $this->assertCount(3, $sentences);

        $retrievedSentences = Sentence::where('paragraph_id', $paragraph->paragraph_id)
            ->orderBy('sentence_number')
            ->pluck('content')
            ->toArray();

        $expectedSentences = [
            'This is the first sentence.',
            'Here is the second one!',
            'And the final sentence?',
        ];
        $this->assertEquals($expectedSentences, $retrievedSentences);

        $combinedContent = $this->paragraphService->combineIntoParagraph($retrievedSentences);

        $this->assertEquals($paragraph->content, $combinedContent);
    }

    /** @test {@link ParagraphService::parseParagraphContents()} parsing a simple paragraph into sentences. */
    public function parseParagraphContents()
    {
        $paragraph = 'This is a sentence. Here is another one! And a question?';
        $expected = [
            'This is a sentence.',
            'Here is another one!',
            'And a question?',
        ];

        $result = $this->paragraphService->parseToSentences($paragraph);
        $this->assertEquals($expected, $result);

        $reconstitutedParagraph = $this->paragraphService->combineIntoParagraph($result);
        $this->assertEquals($paragraph, $reconstitutedParagraph);
    }

    /** @test {@link ParagraphService::parseParagraphContents()} parsing a paragraph with abbreviations. */
    public function parseParagraphContentsAbbreviations()
    {
        $paragraph = 'Dr. Smith went to Washington. He arrived at 9 a.m. sharp. It was a great trip!';
        $expected = [
            /* Note that sentence detection is not perfect... */
            'Dr.',
            'Smith went to Washington.',
            'He arrived at 9 a.m. sharp.',
            'It was a great trip!',
        ];

        $result = $this->paragraphService->parseToSentences($paragraph);
        $this->assertEquals($expected, $result);

        $reconstitutedParagraph = $this->paragraphService->combineIntoParagraph($result);
        $this->assertEquals($paragraph, $reconstitutedParagraph);
    }

    /** @test {@link ParagraphService::parseParagraphContents()} parsing a paragraph with multiple spaces between sentences. */
    public function parseParagraphContentsSpaces()
    {
        $paragraph = 'This is the first sentence.    This is the second sentence.  And the third.';
        $expected = [
            'This is the first sentence.',
            'This is the second sentence.',
            'And the third.',
        ];

        $result = $this->paragraphService->parseToSentences($paragraph);
        $this->assertEquals($expected, $result);
    }

    /** @test {@link ParagraphService::combineIntoParagraph()} combining sentences back into a paragraph. */
    public function combineIntoParagraph()
    {
        $sentences = [
            'This is the first sentence.',
            'This is the second one.',
            'And finally, the third.',
        ];
        $expected = 'This is the first sentence. This is the second one. And finally, the third.';

        $result = $this->paragraphService->combineIntoParagraph($sentences);
        $this->assertEquals($expected, $result);
    }

    /** @test {@link ParagraphService::parseParagraphContents()} parsing and recombining a paragraph to ensure data integrity. */
    public function parseParagraphContentsRoundTrip()
    {
        $paragraph = 'The quick brown fox jumps over the lazy dog. Mr. John Doe lives at 123 Elm St. Can you believe it?';
        $expected = 'The quick brown fox jumps over the lazy dog. Mr. John Doe lives at 123 Elm St. Can you believe it?';

        $sentences = $this->paragraphService->parseToSentences($paragraph);
        $recombinedParagraph = $this->paragraphService->combineIntoParagraph($sentences);

        $this->assertEquals($expected, $recombinedParagraph);
    }

    /** @test {@link ParagraphService::parseParagraphContents()} handling of edge cases with trailing spaces. */
    public function parseParagraphContentsTrailingSpaces()
    {
        $paragraph = 'This is a test.   ';
        $expected = ['This is a test.'];

        $result = $this->paragraphService->parseToSentences($paragraph);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test parsing an empty paragraph.
     */
    public function testParseEmptyParagraph()
    {
        $paragraph = '';
        $expected = [];

        $result = $this->paragraphService->parseToSentences($paragraph);
        $this->assertEquals($expected, $result);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->paragraphService = app(ParagraphService::class);
    }
}
