<?php

namespace Tests\Feature\Database;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Author;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Models\Publisher;
use ThreeLeaf\Biblioteca\Models\Sentence;

/**
 * Pin foreign key enforcement in the test database.
 *
 * SQLite does not enforce foreign keys unless "PRAGMA foreign_keys=ON" is issued, and it
 * ignores that pragma inside a transaction. Because RefreshDatabase wraps each test in a
 * transaction, the pragma must come from the connection configuration. Without these
 * tests the setting can be lost and every other test keeps passing, while the database
 * silently accepts rows that reference identifiers which do not exist.
 */
class ForeignKeyConstraintTest extends TestCase
{
    use RefreshDatabase;

    /** The pragma is on inside a test, where RefreshDatabase has already opened a transaction. */
    #[Test]
    public function foreignKeyPragmaIsEnabled(): void
    {
        $this->assertSame(
            'sqlite',
            DB::connection()->getDriverName(),
            'The testing connection is expected to be SQLite.',
        );
        $this->assertGreaterThan(
            0,
            DB::transactionLevel(),
            'RefreshDatabase is expected to have opened a transaction.',
        );

        $this->assertSame(1, (int)DB::scalar('PRAGMA foreign_keys'));
    }

    /** An unknown foreign key is rejected by the database, not silently stored. */
    #[Test]
    public function unknownForeignKeyIsRejected(): void
    {
        $this->expectException(QueryException::class);

        Chapter::factory()->create(['book_id' => 'no-such-book']);
    }

    /** {@link Book} rows cascade to their chapters, paragraphs, and sentences on delete. */
    #[Test]
    public function deletingABookCascadesToItsDescendants(): void
    {
        $sentence = Sentence::factory()->create();
        $paragraph = $sentence->paragraph;
        $chapter = $paragraph->chapter;
        $book = $chapter->book;

        DB::table(Book::TABLE_NAME)->where('book_id', $book->book_id)->delete();

        $this->assertDatabaseMissing(Chapter::TABLE_NAME, ['chapter_id' => $chapter->chapter_id]);
        $this->assertDatabaseMissing(Paragraph::TABLE_NAME, ['paragraph_id' => $paragraph->paragraph_id]);
        $this->assertDatabaseMissing(Sentence::TABLE_NAME, ['sentence_id' => $sentence->sentence_id]);
    }

    /** {@link Author} rows cascade to their books. */
    #[Test]
    public function deletingAnAuthorCascadesToTheirBooks(): void
    {
        $book = Book::factory()->create();

        DB::table(Author::TABLE_NAME)->where('author_id', $book->author_id)->delete();

        $this->assertDatabaseMissing(Book::TABLE_NAME, ['book_id' => $book->book_id]);
    }

    /** A deleted {@link Publisher} leaves its books in place with a null publisher. */
    #[Test]
    public function deletingAPublisherNullsTheBookPublisher(): void
    {
        $publisher = Publisher::factory()->create();
        $book = Book::factory()->create(['publisher_id' => $publisher->publisher_id]);

        DB::table(Publisher::TABLE_NAME)->where('publisher_id', $publisher->publisher_id)->delete();

        $this->assertDatabaseHas(Book::TABLE_NAME, [
            'book_id' => $book->book_id,
            'publisher_id' => null,
        ]);
    }
}
