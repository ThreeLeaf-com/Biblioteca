<?php

namespace Tests\Feature\Models;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Series;
use ThreeLeaf\Biblioteca\Models\SeriesBook;

/** Test {@link SeriesBook}. */
class SeriesBookTest extends TestCase
{
    use RefreshDatabase;

    /**  @test {@link SeriesBook::create()}. */
    public function create(): void
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

    /**  @test {@link SeriesBook::create()} relationship. */
    public function createRelationship(): void
    {
        $seriesBook = SeriesBook::factory()->create();

        $this->assertInstanceOf(Series::class, $seriesBook->series);
        $this->assertInstanceOf(Book::class, $seriesBook->book);
    }

    /**  @test {@link SeriesBook::update()}. */
    public function update(): void
    {
        $seriesBook = SeriesBook::factory()->create(['number' => 1]);

        $seriesBook->update(['number' => 2]);

        $this->assertDatabaseHas(SeriesBook::TABLE_NAME, [
            'series_id' => $seriesBook->series_id,
            'book_id' => $seriesBook->book_id,
            'number' => 2,
        ]);
    }

    /**  @test {@link SeriesBook::delete()}. */
    public function testDelete(): void
    {
        $seriesBook = SeriesBook::factory()->create();

        $seriesBook->delete();

        $this->assertDatabaseMissing(SeriesBook::TABLE_NAME, [
            'series_id' => $seriesBook->series_id,
            'book_id' => $seriesBook->book_id,
        ]);
    }

    /**  @test {@link SeriesBook::create()} unique. */
    public function createUnique(): void
    {
        $this->expectException(QueryException::class);

        $series = Series::factory()->create();
        $book = Book::factory()->create();

        SeriesBook::factory()->create([
            'series_id' => $series->series_id,
            'book_id' => $book->book_id,
        ]);

        /* Attempt to create a duplicate entry with the same series_id and book_id fails */
        SeriesBook::factory()->create([
            'series_id' => $series->series_id,
            'book_id' => $book->book_id,
        ]);
    }

    /**  @test {@link SeriesBook::create()} required fields. */
    public function createRequired(): void
    {
        $this->expectException(QueryException::class);

        SeriesBook::factory()->create(['series_id' => null, 'book_id' => null]);
    }

    /** @test {@link Series::attachBook()}. */
    public function attachBookToSeries(): void
    {
        $book = Book::factory()->create();
        $series = Series::factory()->create();

        $series->attachBook($book->book_id);

        $this->assertTrue($series->books->contains($book));
    }
}
