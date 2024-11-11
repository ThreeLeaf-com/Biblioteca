<?php

namespace Tests\Feature\Models;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Series;
use ThreeLeaf\Biblioteca\Models\SeriesBook;

class SeriesBookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the creation of a SeriesBook entry.
     *
     * @return void
     */
    public function test_it_creates_a_series_book_entry(): void
    {
        $series = Series::factory()->create();
        $book = Book::factory()->create();

        $seriesBook = SeriesBook::factory()->create([
            'series_id' => $series->series_id,
            'book_id' => $book->book_id,
            'number' => 1,
        ]);

        $this->assertDatabaseHas(SeriesBook::TABLE_NAME, [
            'series_id' => $series->series_id,
            'book_id' => $book->book_id,
            'number' => 1,
        ]);

        $this->assertEquals($series->series_id, $seriesBook->series->series_id);
        $this->assertEquals($book->book_id, $seriesBook->book->book_id);
        $this->assertEquals($series->title, $seriesBook->series->title);
        $this->assertEquals($book->title, $seriesBook->book->title);
    }

    /**
     * Test the relationship between Series and Books in SeriesBook.
     *
     * @return void
     */
    public function test_series_book_relationships(): void
    {
        $seriesBook = SeriesBook::factory()->create();

        $this->assertInstanceOf(Series::class, $seriesBook->series);
        $this->assertInstanceOf(Book::class, $seriesBook->book);
    }

    /**
     * Test updating a SeriesBook entry.
     *
     * @return void
     */
    public function test_update_series_book_entry(): void
    {
        $seriesBook = SeriesBook::factory()->create(['number' => 1]);

        $seriesBook->update(['number' => 2]);

        $this->assertDatabaseHas(SeriesBook::TABLE_NAME, [
            'series_id' => $seriesBook->series_id,
            'book_id' => $seriesBook->book_id,
            'number' => 2,
        ]);
    }

    /**
     * Test deleting a SeriesBook entry.
     *
     * @return void
     */
    public function test_delete_series_book_entry(): void
    {
        $seriesBook = SeriesBook::factory()->create();

        $seriesBook->delete();

        $this->assertDatabaseMissing(SeriesBook::TABLE_NAME, [
            'series_id' => $seriesBook->series_id,
            'book_id' => $seriesBook->book_id,
        ]);
    }

    /**
     * Test SeriesBook enforces unique series_id and book_id combination.
     *
     * @return void
     */
    public function test_unique_constraint_on_series_id_and_book_id(): void
    {
        $this->expectException(QueryException::class);

        $series = Series::factory()->create();
        $book = Book::factory()->create();

        SeriesBook::factory()->create([
            'series_id' => $series->series_id,
            'book_id' => $book->book_id,
        ]);

        // Attempt to create a duplicate entry with the same series_id and book_id
        SeriesBook::factory()->create([
            'series_id' => $series->series_id,
            'book_id' => $book->book_id,
        ]);
    }

    /**
     * Test SeriesBook requires a series_id and book_id.
     *
     * @return void
     */
    public function test_series_book_requires_series_id_and_book_id(): void
    {
        $this->expectException(QueryException::class);

        SeriesBook::factory()->create(['series_id' => null, 'book_id' => null]);
    }
}
