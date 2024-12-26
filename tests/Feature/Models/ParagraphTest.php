<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Annotation;
use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Models\Sentence;

/** Test {@link Paragraph}. */
class ParagraphTest extends TestCase
{
    use RefreshDatabase;

    /** @test {@link Paragraph::create()}. */
    public function testParagraphCreation()
    {
        $paragraph = Paragraph::factory()->create();

        $this->assertDatabaseHas(Paragraph::TABLE_NAME, [
            'paragraph_id' => $paragraph->paragraph_id,
            'content' => $paragraph->content,
        ]);
    }

    /** @test {@link Paragraph::$sentences()}. */
    public function testParagraphRelationships()
    {
        $paragraph = Paragraph::factory()->create();
        $sentences = Sentence::factory(3)->create(['paragraph_id' => $paragraph->paragraph_id]);

        $this->assertInstanceOf(Chapter::class, $paragraph->chapter);
        $this->assertCount(3, $paragraph->sentences);
    }

    /** @test {@link Paragraph::update()}. */
    public function update()
    {
        $paragraph = Paragraph::factory()->create(['content' => 'Old content.']);

        $paragraph->update(['content' => 'New content.']);

        $this->assertDatabaseHas(Paragraph::TABLE_NAME, [
            'paragraph_id' => $paragraph->paragraph_id,
            'content' => 'New content.',
        ]);
    }

    /** @test {@link Paragraph::delete()}. */
    public function testDelete()
    {
        $paragraph = Paragraph::factory()->create();

        $paragraph->delete();

        $this->assertDatabaseMissing(Paragraph::TABLE_NAME, [
            'paragraph_id' => $paragraph->paragraph_id,
        ]);
    }

    /** @test {@link Paragraph::annotations()}. */
    public function annotation()
    {
        $paragraph = Paragraph::factory()->create();
        $this->assertCount(0, $paragraph->annotations);

        Annotation::factory(3)->create(['reference_id' => $paragraph->paragraph_id, 'reference_type' => Paragraph::class]);
        $paragraph->refresh();

        $this->assertCount(3, $paragraph->annotations);
    }
}
