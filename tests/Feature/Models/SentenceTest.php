<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Annotation;
use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Models\Sentence;

/** Test {@link Sentence}. */
class SentenceTest extends TestCase
{
    use RefreshDatabase;

    /** @test {@link Sentence::create()}. */
    public function testSentenceCreation()
    {
        $sentence = Sentence::factory()->create();

        $this->assertDatabaseHas(Sentence::TABLE_NAME, [
            'sentence_id' => $sentence->sentence_id,
            'content' => $sentence->content,
        ]);
    }

    /** @test {@link Sentence::update()}. */
    public function update()
    {
        $sentence = Sentence::factory()->create(['content' => 'Old content.']);

        $sentence->update(['content' => 'New content.']);

        $this->assertDatabaseHas(Sentence::TABLE_NAME, [
            'sentence_id' => $sentence->sentence_id,
            'content' => 'New content.',
        ]);
    }

    /** @test {@link Sentence::delete()}. */
    public function testDelete()
    {
        $sentence = Sentence::factory()->create();

        $sentence->delete();

        $this->assertDatabaseMissing(Sentence::TABLE_NAME, [
            'sentence_id' => $sentence->sentence_id,
        ]);
    }

    /** @test {@link Sentence::$paragraph()}. */
    public function testSentenceRelationships()
    {
        $sentence = Sentence::factory()->create();
        $this->assertInstanceOf(Paragraph::class, $sentence->paragraph);
    }

    /** @test {@link Sentence::annotations()}. */
    public function annotation()
    {
        $sentence = Sentence::factory()->create();
        $this->assertCount(0, $sentence->annotations);

        Annotation::factory(3)->create(['reference_id' => $sentence->sentence_id, 'reference_type' => Sentence::class]);
        $sentence->refresh();

        $this->assertCount(3, $sentence->annotations);
    }
}
