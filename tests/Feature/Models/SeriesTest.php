<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Author;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Series;
use ThreeLeaf\Biblioteca\Models\SeriesBook;

class SeriesTest extends TestCase
{
    use RefreshDatabase;

    /** @test {@link Series::create()}. */
    public function createSeries()
    {
        $series = Series::factory()->create();

        $this->assertDatabaseHas(Series::TABLE_NAME, [
            'series_id' => $series->series_id,
            'title' => $series->title,
            'description' => $series->description,
        ]);
    }

    /** @test {@link Series::book()}. */
    public function books()
    {
        /** @var Series $series */
        $series = Series::factory()->create();

        SeriesBook::factory(3)->create(['series_id' => $series->series_id]);

        $this->assertInstanceOf(Author::class, $series->author);
        $this->assertCount(3, $series->books);
    }

    /** @test {@link Series::update()}. */
    public function updateSeries()
    {
        $series = Series::factory()->create(['title' => 'Old Title']);

        $series->update(['title' => 'New Title']);

        $this->assertDatabaseHas(Series::TABLE_NAME, [
            'series_id' => $series->series_id,
            'title' => 'New Title',
        ]);
    }

    /** @test {@link Series::delete()}. */
    public function deleteSeries()
    {
        $series = Series::factory()->create();

        $series->delete();

        $this->assertDatabaseMissing(Series::TABLE_NAME, [
            'series_id' => $series->series_id,
        ]);
    }

    /** @test {@link Series::reorderBooks()}. */
    public function reorderBooks(): void
    {
        /* Create a series and attach books in initial order */
        $series = Series::factory()->create();
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();
        $book3 = Book::factory()->create();

        /* Attach books with initial numbers */
        $series->books()->attach($book1->book_id, ['number' => 1]);
        $series->books()->attach($book2->book_id, ['number' => 2]);
        $series->books()->attach($book3->book_id, ['number' => 3]);

        /* Define the new order of book IDs */
        $newOrder = [$book3->book_id, $book1->book_id, $book2->book_id];

        /* Call reorderBooks with the new order */
        $series->reorderBooks($newOrder);

        /* Refresh the relationship to get updated pivot data */
        $series->load('books');

        /* Assert the new order by checking the 'number' values in the pivot table */
        $this->assertEquals(1, $series->books->find($book3->book_id)->pivot->number);
        $this->assertEquals(2, $series->books->find($book1->book_id)->pivot->number);
        $this->assertEquals(3, $series->books->find($book2->book_id)->pivot->number);
    }
}
