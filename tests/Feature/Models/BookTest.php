<?php

namespace Tests\Feature\Models;

use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Author;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Models\Publisher;
use ThreeLeaf\Biblioteca\Models\Series;

/** Test {@link Book}. */
class BookTest extends TestCase
{
    use RefreshDatabase;

    /** @test {@link Book::__construct()} */
    public function construct(): void
    {
        $book = Book::factory()->create();
        $this->assertDatabaseHas(Author::TABLE_NAME, ['author_id' => $book->author_id]);
        $this->assertDatabaseHas(Publisher::TABLE_NAME, ['publisher_id' => $book->publisher_id]);
        $this->assertDatabaseHas(Book::TABLE_NAME, ['title' => $book->title]);
    }

    /** @test {@link Book::create()}. */
    public function create(): void
    {
        Book::factory()->create([
            'title' => 'Sample Book',
            'published_date' => Carbon::now()->subYear(),
            'locale' => 'en_US',
        ]);

        $this->assertDatabaseHas(Book::TABLE_NAME, [
            'title' => 'Sample Book',
            'locale' => 'en_US',
        ]);
    }

    /** @test {@link Book::update()}. */
    public function update(): void
    {
        $book = Book::factory()->create(['title' => 'Old Title']);
        $book->update(['title' => 'Updated Title']);

        $this->assertDatabaseHas(Book::TABLE_NAME, [
            'book_id' => $book->book_id,
            'title' => 'Updated Title',
        ]);
    }

    /** @test {@link Book::delete()}. */
    public function testDelete(): void
    {
        $book = Book::factory()->create();
        $book->delete();

        $this->assertDatabaseMissing(Book::TABLE_NAME, [
            'book_id' => $book->book_id,
        ]);
    }

    /** @test {@link Book::$author}. */
    public function bookAuthor(): void
    {
        $book = Book::factory()->create();
        $this->assertInstanceOf(Author::class, $book->author);
    }

    /** @test {@link Book::$publisher}. */
    public function bookPublisher(): void
    {
        $book = Book::factory()->create();
        $this->assertInstanceOf(Publisher::class, $book->publisher);
    }

    /** @test {@link Book::$chapters}. */
    public function bookChapters(): void
    {
        $book = Book::factory()->create();
        Chapter::factory(3)->create(['book_id' => $book->book_id]);

        $this->assertCount(3, $book->chapters);
    }

    /** @test {@link Book} required fields. */
    public function bookRequiredFields(): void
    {
        $this->expectException(QueryException::class);

        Book::factory()->create(['title' => null]); // Title is required
    }

    /** @test {@link Book} attaches to {@link Series}. */
    public function bookSeries(): void
    {
        $book = Book::factory()->create();
        $series = Series::factory()->create();

        /* Attach the book to the series with a specific 'number' value */
        $series->books()->attach($book->book_id, ['number' => 1]);

        $book->refresh();

        /* Assert that the book has the correct series */
        $this->assertTrue($book->series->contains($series));

        /* Retrieve the series with pivot data and verify 'number' */
        $relatedSeries = $book->series->first();
        $this->assertEquals(1, $relatedSeries->pivot->number);
    }
}
