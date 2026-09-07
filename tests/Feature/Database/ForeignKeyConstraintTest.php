<?php

namespace Tests\Feature\Database;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Annotation;
use ThreeLeaf\Biblioteca\Models\Author;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\BookGenre;
use ThreeLeaf\Biblioteca\Models\BookTag;
use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Models\Genre;
use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Models\Publisher;
use ThreeLeaf\Biblioteca\Models\Sentence;
use ThreeLeaf\Biblioteca\Models\Series;
use ThreeLeaf\Biblioteca\Models\SeriesBook;
use ThreeLeaf\Biblioteca\Models\Tag;

/**
 * Pin foreign key enforcement in the test database.
 *
 * SQLite does not enforce foreign keys unless "PRAGMA foreign_keys=ON" is issued, and it
 * ignores that pragma inside a transaction. {@link RefreshDatabase} wraps each test in a
 * transaction, so the pragma must come from the connection configuration. The setting
 * gives no signal when it is missing. If it is removed, all other tests continue to pass,
 * and the database accepts rows that point to identifiers that do not exist.
 *
 * The cascade tests delete through the query builder rather than Eloquent. The assertion
 * then proves the database cascaded, not that a model event did.
 */
class ForeignKeyConstraintTest extends TestCase
{
    use RefreshDatabase;

    /** The pragma is on inside a test, where {@link RefreshDatabase} has already opened a transaction. */
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

        $this->assertSame(
            1,
            (int)DB::scalar('PRAGMA foreign_keys'),
            'Foreign key enforcement is expected to be on inside the test transaction.',
        );
    }

    /**
     * An unknown foreign key raises {@link QueryException} and the row is not stored.
     *
     * The insert goes through the query builder rather than a factory. A factory writes an
     * author, a publisher, and a book first, and a unique-index collision in any of those
     * would satisfy a bare expectException() while proving nothing about foreign keys.
     */
    #[Test]
    public function unknownForeignKeyIsRejected(): void
    {
        try {
            DB::table(Chapter::TABLE_NAME)->insert([
                'chapter_id' => '9f1d4a7e-0000-4000-8000-00000000c001',
                'book_id' => 'no-such-book',
                'chapter_number' => 1,
                'title' => 'Rejected',
                'content' => 'Rejected',
            ]);
            $this->fail('Expected the unknown book_id to be rejected by the database.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('FOREIGN KEY constraint failed', $exception->getMessage());
        }

        $this->assertDatabaseMissing(Chapter::TABLE_NAME, ['book_id' => 'no-such-book']);
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

    /** {@link Author} rows cascade to their books, their series, and the pivot rows beneath both. */
    #[Test]
    public function deletingAnAuthorCascadesToTheirBooksAndSeries(): void
    {
        $book = Book::factory()->create();
        $series = Series::factory()->create(['author_id' => $book->author_id]);
        SeriesBook::factory()->create([
            'series_id' => $series->series_id,
            'book_id' => $book->book_id,
        ]);
        $book->tags()->attach(Tag::factory()->create());
        $book->genres()->attach(Genre::factory()->create());

        DB::table(Author::TABLE_NAME)->where('author_id', $book->author_id)->delete();

        $this->assertDatabaseMissing(Book::TABLE_NAME, ['book_id' => $book->book_id]);
        $this->assertDatabaseMissing(Series::TABLE_NAME, ['series_id' => $series->series_id]);
        $this->assertDatabaseMissing(SeriesBook::TABLE_NAME, ['book_id' => $book->book_id]);
        $this->assertDatabaseMissing(BookTag::TABLE_NAME, ['book_id' => $book->book_id]);
        $this->assertDatabaseMissing(BookGenre::TABLE_NAME, ['book_id' => $book->book_id]);
    }

    /** A deleted {@link Publisher} leaves its books in place with a null publisher. */
    #[Test]
    public function deletingAPublisherNullsTheBookPublisher(): void
    {
        $book = Book::factory()->create();
        $this->assertNotNull($book->publisher_id, 'The book factory is expected to attach a publisher.');

        DB::table(Publisher::TABLE_NAME)->where('publisher_id', $book->publisher_id)->delete();

        $this->assertDatabaseHas(Book::TABLE_NAME, [
            'book_id' => $book->book_id,
            'publisher_id' => null,
        ]);
    }

    /**
     * A deleted {@link Sentence} leaves its annotations behind.
     *
     * This is the one documented exception: b_annotations declares no foreign key, because
     * reference_id is polymorphic. Now that every other table enforces its keys, only a
     * test states that the exception is deliberate.
     */
    #[Test]
    public function deletingASentenceLeavesItsAnnotationsBehind(): void
    {
        $sentence = Sentence::factory()->create();
        $annotation = Annotation::factory()->create([
            'reference_id' => $sentence->sentence_id,
            'reference_type' => Sentence::TABLE_NAME,
        ]);

        DB::table(Sentence::TABLE_NAME)->where('sentence_id', $sentence->sentence_id)->delete();

        $this->assertDatabaseMissing(Sentence::TABLE_NAME, ['sentence_id' => $sentence->sentence_id]);
        $this->assertDatabaseHas(Annotation::TABLE_NAME, ['annotation_id' => $annotation->annotation_id]);
    }
}
