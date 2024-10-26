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

/**
 * Test {@link Book}
 */
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

    /** @test */
    public function it_creates_a_book_entry_with_all_attributes(): void
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

    /** @test */
    public function it_updates_a_book_entry(): void
    {
        $book = Book::factory()->create(['title' => 'Old Title']);
        $book->update(['title' => 'Updated Title']);

        $this->assertDatabaseHas(Book::TABLE_NAME, [
            'book_id' => $book->book_id,
            'title' => 'Updated Title',
        ]);
    }

    /** @test */
    public function it_deletes_a_book_entry(): void
    {
        $book = Book::factory()->create();
        $book->delete();

        $this->assertDatabaseMissing(Book::TABLE_NAME, [
            'book_id' => $book->book_id,
        ]);
    }

    /** @test */
    public function it_verifies_relationship_with_author(): void
    {
        $book = Book::factory()->create();
        $this->assertInstanceOf(Author::class, $book->author);
    }

    /** @test */
    public function it_verifies_relationship_with_publisher(): void
    {
        $book = Book::factory()->create();
        $this->assertInstanceOf(Publisher::class, $book->publisher);
    }

    /** @test */
    public function it_verifies_relationship_with_chapters(): void
    {
        $book = Book::factory()->create();
        Chapter::factory(3)->create(['book_id' => $book->book_id]);

        $this->assertCount(3, $book->chapters);
    }

    /** @test */
    public function it_verifies_relationship_with_series(): void
    {
        $book = Book::factory()->create();
        $series = Series::factory()->create();

        $series->attachBook($book->book_id);

        $this->assertTrue($series->books->contains($book));
    }

    /** @test */
    public function it_verifies_required_fields_for_book(): void
    {
        $this->expectException(QueryException::class);

        Book::factory()->create(['title' => null]); // Title is required
    }

    /** @test */
    public function it_verifies_series_relationship_with_pivot_data(): void
    {
        // Create a book and a series
        $book = Book::factory()->create();
        $series = Series::factory()->create();

        // Attach the book to the series with a specific 'number' value
        $series->books()->attach($book->book_id, ['number' => 1]);

        // Refresh the book instance to load the relationship
        $book->refresh();

        // Assert that the book has the correct series
        $this->assertTrue($book->series->contains($series));

        // Retrieve the series with pivot data and verify 'number'
        $relatedSeries = $book->series->first();
        $this->assertEquals(1, $relatedSeries->pivot->number);
    }
}
