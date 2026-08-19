<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Annotation;
use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Models\Sentence;
use PHPUnit\Framework\Attributes\Test;

/** Test {@link Paragraph}. */
class ParagraphTest extends TestCase
{
    use RefreshDatabase;

    /** {@link Paragraph::create()}. */
    #[Test]
    public function testParagraphCreation()
    {
        $paragraph = Paragraph::factory()->create();

        $this->assertDatabaseHas(Paragraph::TABLE_NAME, [
            'paragraph_id' => $paragraph->paragraph_id,
            'content' => $paragraph->content,
        ]);
    }

    /** {@link Paragraph::$sentences()}. */
    #[Test]
    public function testParagraphRelationships()
    {
        $paragraph = Paragraph::factory()->create();
        $sentences = Sentence::factory(3)->create(['paragraph_id' => $paragraph->paragraph_id]);

        $this->assertInstanceOf(Chapter::class, $paragraph->chapter);
        $this->assertCount(3, $paragraph->sentences);
    }

    /** {@link Paragraph::update()}. */
    #[Test]
    public function update()
    {
        $paragraph = Paragraph::factory()->create(['content' => 'Old content.']);

        $paragraph->update(['content' => 'New content.']);

        $this->assertDatabaseHas(Paragraph::TABLE_NAME, [
            'paragraph_id' => $paragraph->paragraph_id,
            'content' => 'New content.',
        ]);
    }

    /** {@link Paragraph::delete()}. */
    #[Test]
    public function testDelete()
    {
        $paragraph = Paragraph::factory()->create();

        $paragraph->delete();

        $this->assertDatabaseMissing(Paragraph::TABLE_NAME, [
            'paragraph_id' => $paragraph->paragraph_id,
        ]);
    }

    /** {@link Paragraph::annotations()}. */
    #[Test]
    public function annotation()
    {
        $paragraph = Paragraph::factory()->create();
        $this->assertCount(0, $paragraph->annotations);

        Annotation::factory(3)->create(['reference_id' => $paragraph->paragraph_id, 'reference_type' => Paragraph::class]);
        $paragraph->refresh();

        $this->assertCount(3, $paragraph->annotations);
    }
}
