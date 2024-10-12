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
    protected ParagraphService $service;

    /** @test {@link ParagraphService::parseParagraphContents()} parsing a paragraph into sentences and verifying round-trip integrity. */
    public function testParseParagraphToSentences()
    {
        $paragraph = Paragraph::factory()->create([
            'content' => 'This is the first sentence. Here is the second one! And the final sentence?',
        ]);

        $sentences = $this->service->parseParagraphContents($paragraph);

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

        $combinedContent = $this->service->combineIntoParagraph($retrievedSentences);

        $this->assertEquals($paragraph->content, $combinedContent);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ParagraphService();
    }
}
