<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\BookGenre;
use ThreeLeaf\Biblioteca\Models\Genre;
use PHPUnit\Framework\Attributes\Test;

/** Test {@link BookGenre}. */
class BookGenreTest extends TestCase
{
    use RefreshDatabase;

    /** {@link Book::genres()} attach. */
    #[Test]
    public function attachGenres(): void
    {
        $book = Book::factory()->create();
        $genre1 = Genre::factory()->create(['name' => 'Fantasy']);
        $genre2 = Genre::factory()->create(['name' => 'Mystery']);

        $book->genres()->attach([$genre1->genre_id, $genre2->genre_id]);

        $this->assertTrue($book->genres->contains($genre1));
        $this->assertTrue($book->genres->contains($genre2));
    }

    /** {@link Genre::books()}. */
    #[Test]
    public function genreBooks(): void
    {
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();
        $genre = Genre::factory()->create(['name' => 'Non-Fiction']);

        $genre->books()->attach([$book1->book_id, $book2->book_id]);

        $this->assertTrue($genre->books->contains($book1));
        $this->assertTrue($genre->books->contains($book2));
    }

    /** {@link Book::genres()} detach. */
    #[Test]
    public function detachGenres(): void
    {
        $book = Book::factory()->create();
        $genre = Genre::factory()->create(['name' => 'Science Fiction']);

        $book->genres()->attach($genre->genre_id);
        $book->genres()->detach($genre->genre_id);

        $this->assertFalse($book->genres->contains($genre));
    }

    /** {@link Book::genres()} sync. */
    #[Test]
    public function syncGenres(): void
    {
        $book = Book::factory()->create();
        $genre1 = Genre::factory()->create(['name' => 'Romance']);
        $genre2 = Genre::factory()->create(['name' => 'Adventure']);
        $genre3 = Genre::factory()->create(['name' => 'Thriller']);

        /* Initially attach two genres to the book */
        $book->genres()->sync([$genre1->genre_id, $genre2->genre_id]);

        /* Verify initial genres are attached */
        $this->assertTrue($book->genres->contains($genre1));
        $this->assertTrue($book->genres->contains($genre2));

        // Sync to replace with a new set of genres
        $book->genres()->sync([$genre2->genre_id, $genre3->genre_id]);

        /* Refresh the book instance and verify the genres */
        $book->refresh();
        $this->assertFalse($book->genres->contains($genre1));
        $this->assertTrue($book->genres->contains($genre2));
        $this->assertTrue($book->genres->contains($genre3));
    }
}
