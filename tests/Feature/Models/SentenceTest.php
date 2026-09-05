<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Annotation;
use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Models\Sentence;
use PHPUnit\Framework\Attributes\Test;

/** Test {@link Sentence}. */
class SentenceTest extends TestCase
{
    use RefreshDatabase;

    /** {@link Sentence::create()}. */
    #[Test]
    public function testSentenceCreation()
    {
        $sentence = Sentence::factory()->create();

        $this->assertDatabaseHas(Sentence::TABLE_NAME, [
            'sentence_id' => $sentence->sentence_id,
            'content' => $sentence->content,
        ]);
    }

    /** {@link Sentence::update()}. */
    #[Test]
    public function update()
    {
        $sentence = Sentence::factory()->create(['content' => 'Old content.']);

        $sentence->update(['content' => 'New content.']);

        $this->assertDatabaseHas(Sentence::TABLE_NAME, [
            'sentence_id' => $sentence->sentence_id,
            'content' => 'New content.',
        ]);
    }

    /** {@link Sentence::delete()}. */
    #[Test]
    public function testDelete()
    {
        $sentence = Sentence::factory()->create();

        $sentence->delete();

        $this->assertDatabaseMissing(Sentence::TABLE_NAME, [
            'sentence_id' => $sentence->sentence_id,
        ]);
    }

    /** {@link Sentence::$paragraph()}. */
    #[Test]
    public function testSentenceRelationships()
    {
        $sentence = Sentence::factory()->create();
        $this->assertInstanceOf(Paragraph::class, $sentence->paragraph);
    }

    /** {@link Sentence::annotations()}. */
    #[Test]
    public function annotation()
    {
        $sentence = Sentence::factory()->create();
        $this->assertCount(0, $sentence->annotations);

        Annotation::factory(3)->create(['reference_id' => $sentence->sentence_id, 'reference_type' => Sentence::class]);
        $sentence->refresh();

        $this->assertCount(3, $sentence->annotations);
    }
}
