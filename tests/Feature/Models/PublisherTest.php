<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Publisher;
use PHPUnit\Framework\Attributes\Test;

/** Test {@link Publisher}. */
class PublisherTest extends TestCase
{
    use RefreshDatabase;

    /** {@link Publisher::create()}. */
    #[Test]
    public function createPublisher(): void
    {
        $publisher = Publisher::factory()->create(['name' => 'Penguin Books']);

        $this->assertDatabaseHas(Publisher::TABLE_NAME, [
            'publisher_id' => $publisher->publisher_id,
            'name' => 'Penguin Books',
        ]);
    }

    /** {@link Publisher::update()}. */
    #[Test]
    public function updatePublisher(): void
    {
        $publisher = Publisher::factory()->create(['name' => 'Old Name']);

        $publisher->update(['name' => 'Updated Name']);

        $this->assertDatabaseHas(Publisher::TABLE_NAME, [
            'publisher_id' => $publisher->publisher_id,
            'name' => 'Updated Name',
        ]);
    }

    /** {@link Publisher::delete()}. */
    #[Test]
    public function deletePublisher(): void
    {
        $publisher = Publisher::factory()->create();

        $publisher->delete();

        $this->assertDatabaseMissing(Publisher::TABLE_NAME, [
            'publisher_id' => $publisher->publisher_id,
        ]);
    }

    /** {@link Publisher::books()}. */
    #[Test]
    public function it_retrieves_books_associated_with_a_publisher(): void
    {
        /* Create a Publisher and associate multiple books */
        $publisher = Publisher::factory()->create(['name' => 'Vintage']);
        $book1 = Book::factory()->create(['publisher_id' => $publisher->publisher_id]);
        $book2 = Book::factory()->create(['publisher_id' => $publisher->publisher_id]);

        /* Refresh the publisher instance to load the books relationship */
        $publisher->refresh();

        /* Verify that the Publisher is associated with both books */
        $this->assertTrue($publisher->books->contains($book1));
        $this->assertTrue($publisher->books->contains($book2));
        $this->assertCount(2, $publisher->books);
    }
}
