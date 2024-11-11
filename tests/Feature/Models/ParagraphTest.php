<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Models\Sentence;

/** Test {@link Paragraph}. */
class ParagraphTest extends TestCase
{
    use RefreshDatabase;

    /** @test the creation of a Paragraph using the factory. */
    public function testParagraphCreation()
    {
        $paragraph = Paragraph::factory()->create();

        $this->assertDatabaseHas(Paragraph::TABLE_NAME, [
            'paragraph_id' => $paragraph->paragraph_id,
            'content' => $paragraph->content,
        ]);
    }

    /** @test Paragraph relationships. */
    public function testParagraphRelationships()
    {
        $paragraph = Paragraph::factory()->create();
        $sentences = Sentence::factory(3)->create(['paragraph_id' => $paragraph->paragraph_id]);

        $this->assertInstanceOf(Chapter::class, $paragraph->chapter);
        $this->assertCount(3, $paragraph->sentences);
    }

    /** @test updating a Paragraph. */
    public function testParagraphUpdate()
    {
        $paragraph = Paragraph::factory()->create(['content' => 'Old content.']);

        $paragraph->update(['content' => 'New content.']);

        $this->assertDatabaseHas(Paragraph::TABLE_NAME, [
            'paragraph_id' => $paragraph->paragraph_id,
            'content' => 'New content.',
        ]);
    }

    /** @test deleting a Paragraph. */
    public function testParagraphDeletion()
    {
        $paragraph = Paragraph::factory()->create();

        $paragraph->delete();

        $this->assertDatabaseMissing(Paragraph::TABLE_NAME, [
            'paragraph_id' => $paragraph->paragraph_id,
        ]);
    }
}
