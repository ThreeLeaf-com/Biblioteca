<?php

namespace Tests\Feature\Services;

use Exception;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Author;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Series;
use ThreeLeaf\Biblioteca\Models\SeriesBook;
use ThreeLeaf\Biblioteca\Services\SeriesService;
use PHPUnit\Framework\Attributes\Test;

/**
 * A failure injected into a query listener, standing in for any mid-write error.
 *
 * It extends {@link Exception} rather than a framework or PHPUnit type so that catching it
 * cannot also swallow a real database error or a PHPUnit assertion failure. Both of those
 * extend `RuntimeException`, so a broad catch would hide the very defects these tests exist
 * to detect.
 */
class InjectedFailure extends Exception
{
}

/** Test {@link SeriesService}. */
class SeriesServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @var SeriesService */
    protected SeriesService $seriesService;

    /** {@link SeriesService::create()} with book IDs. */
    #[Test]
    public function testCreateSeriesWithBookIds()
    {
        $author = Author::factory()->create();
        $books = Book::factory()->count(3)->create();
        $seriesService = new SeriesService();
        $seriesData = [
            'title' => fake()->sentence(),
            'author_id' => $author->author_id,
            'book_ids' => $books->pluck('book_id')->toArray(),
        ];

        $series = $seriesService->create($seriesData);

        $this->assertEquals($books->count(), $series->books->count());
    }

    /** {@link SeriesService::create()} without book IDs. */
    #[Test]
    public function createNoBooks()
    {
        $author = Author::factory()->create();
        $seriesService = new SeriesService();
        $seriesData = [
            'title' => fake()->sentence(),
            'author_id' => $author->author_id,
            'book_ids' => [],
        ];

        $series = $seriesService->create($seriesData);

        $this->assertEmpty($series->books);
    }

    /** {@link SeriesService::update()} with book IDs. */
    #[Test]
    public function updateWithBookIds()
    {
        $author = Author::factory()->create();
        $books = Book::factory()->count(3)->create();
        $series = Series::factory()->create();
        $seriesService = new SeriesService();
        $seriesData = [
            'title' => fake()->sentence(),
            'author_id' => $author->author_id,
            'book_ids' => $books->pluck('book_id')->toArray(),
        ];

        $series = $seriesService->update($series, $seriesData);

        $this->assertEquals($books->count(), $series->books->count());

        $seriesData = [
            'title' => fake()->sentence(),
            'author_id' => $author->author_id,
            'book_ids' => [],
        ];

        $series = $seriesService->update($series, $seriesData);

        /* We keep existing books if empty array passed in */
        $this->assertEquals($books->count(), $series->books->count());
    }

    /**
     * {@link SeriesService::create()} discards the whole series when attaching a book fails.
     *
     * The series row and both pivot rows are written before the injected failure fires, so
     * without the surrounding transaction a half-built series survives.
     */
    #[Test]
    public function createRollsBackWhenAttachingABookFails()
    {
        $author = Author::factory()->create();
        $books = Book::factory()->count(2)->create();
        $title = fake()->sentence();
        $seriesData = [
            'title' => $title,
            'author_id' => $author->author_id,
            'book_ids' => $books->pluck('book_id')->toArray(),
        ];

        $this->failOnInsertInto(SeriesBook::TABLE_NAME, 2);
        $injectedFailure = null;

        try {
            $this->seriesService->create($seriesData);
        } catch (InjectedFailure $exception) {
            $injectedFailure = $exception;
        }

        /* Without this, the assertions below would also hold for a create() that did nothing. */
        $this->assertNotNull($injectedFailure, 'The injected failure never fired.');

        $this->assertDatabaseMissing(Series::TABLE_NAME, ['title' => $title]);
        $this->assertDatabaseCount(SeriesBook::TABLE_NAME, 0);
    }

    /**
     * {@link SeriesService::update()} rolls the title and the book list back together.
     *
     * The update writes the new title and detaches every existing book before it attaches
     * the replacements, so without the surrounding transaction a failed attach leaves the
     * series renamed and stripped of the books it used to have.
     */
    #[Test]
    public function updateRollsBackWhenAttachingABookFails()
    {
        $author = Author::factory()->create();
        $books = Book::factory()->count(2)->create();
        $replacementBook = Book::factory()->create();
        $originalTitle = fake()->sentence();

        /* Arranged without the service, so a regression in update() cannot fake the baseline. */
        $series = Series::factory()->create([
            'title' => $originalTitle,
            'author_id' => $author->author_id,
        ]);
        foreach ($books as $index => $book) {
            $series->books()->attach($book->book_id, ['number' => $index + 1]);
        }

        $this->failOnInsertInto(SeriesBook::TABLE_NAME, 1);
        $injectedFailure = null;

        try {
            $this->seriesService->update($series, [
                'title' => 'Replacement title',
                'author_id' => $author->author_id,
                'book_ids' => [$replacementBook->book_id],
            ]);
        } catch (InjectedFailure $exception) {
            $injectedFailure = $exception;
        }

        $this->assertNotNull($injectedFailure, 'The injected failure never fired.');

        $this->assertDatabaseHas(Series::TABLE_NAME, [
            'series_id' => $series->series_id,
            'title' => $originalTitle,
        ]);
        $this->assertEqualsCanonicalizing(
            $books->pluck('book_id')->toArray(),
            $series->fresh()->books->pluck('book_id')->toArray(),
        );
    }

    /**
     * Register a query listener that throws once the nth insert into the given table has run.
     *
     * The listener stays registered for the rest of the test, and it fires on every matching
     * insert from the nth onwards.
     *
     * A missing foreign key is not usable as the failure trigger. SQLite treats
     * `PRAGMA foreign_keys` as a no-op inside a transaction, and `RefreshDatabase` opens one
     * before the test starts, so the constraint is not enforced. A query listener is
     * driver-independent. It also fires after the statement has executed, so real rows exist
     * for the rollback to undo rather than the test asserting against an empty transaction.
     *
     * @param string $table       The table whose inserts are counted.
     * @param int    $insertCount The 1-based insert to start failing on.
     *
     * @throws InjectedFailure From a later query, once the nth matching insert has run.
     */
    private function failOnInsertInto(string $table, int $insertCount): void
    {
        /* Anchored on the insert target: b_series is a prefix of b_series_books. */
        $pattern = '/^insert\s+(?:or\s+\w+\s+)?into\s+["`\[]?' . preg_quote($table, '/') . '["`\]]?[\s(]/i';

        $insertsSeen = 0;
        DB::listen(function (QueryExecuted $query) use ($pattern, $table, $insertCount, &$insertsSeen) {
            if (preg_match($pattern, ltrim($query->sql)) && ++$insertsSeen >= $insertCount) {
                throw new InjectedFailure("Injected failure on insert $insertsSeen into $table.");
            }
        });
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seriesService = new SeriesService();
    }
}
