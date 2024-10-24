<?php

namespace Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Models\Figure;
use ThreeLeaf\Biblioteca\Models\Paragraph;

class ChapterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the creation of a Chapter using the factory.
     *
     * @return void
     */
    public function testChapterCreation()
    {
        $chapter = Chapter::factory()->create();

        $this->assertDatabaseHas(Chapter::TABLE_NAME, [
            'chapter_id' => $chapter->chapter_id,
            'title' => $chapter->title,
        ]);
    }

    /**
     * Test Chapter relationships.
     *
     * @return void
     */
    public function testChapterRelationships()
    {
        $chapter = Chapter::factory()->create();
        $paragraphs = Paragraph::factory(3)->create(['chapter_id' => $chapter->chapter_id]);
        $figures = Figure::factory(2)->create(['chapter_id' => $chapter->chapter_id]);

        $this->assertInstanceOf(Book::class, $chapter->book);
        $this->assertCount(3, $chapter->paragraphs);
        $this->assertCount(2, $chapter->figures);
    }

    /**
     * Test updating a Chapter.
     *
     * @return void
     */
    public function testChapterUpdate()
    {
        $chapter = Chapter::factory()->create(['title' => 'Old Title']);

        $chapter->update(['title' => 'New Title']);

        $this->assertDatabaseHas(Chapter::TABLE_NAME, [
            'chapter_id' => $chapter->chapter_id,
            'title' => 'New Title',
        ]);
    }

    /**
     * Test deleting a Chapter.
     *
     * @return void
     */
    public function testChapterDeletion()
    {
        $chapter = Chapter::factory()->create();

        $chapter->delete();

        $this->assertDatabaseMissing(Chapter::TABLE_NAME, [
            'chapter_id' => $chapter->chapter_id,
        ]);
    }
}
