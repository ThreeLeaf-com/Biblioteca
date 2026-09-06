<?php

namespace Tests\Feature\Services;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Author;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Series;
use ThreeLeaf\Biblioteca\Models\SeriesBook;
use ThreeLeaf\Biblioteca\Services\SeriesService;
use PHPUnit\Framework\Attributes\Test;

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
     * The series row and the first pivot row are both written before the second attach
     * fails, so without the surrounding transaction a half-built series survives.
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

        try {
            $this->seriesService->create($seriesData);
            $this->fail('The injected failure should have aborted the create.');
        } catch (RuntimeException) {
            /* Expected: the second book fails to attach. */
        }

        $this->assertDatabaseMissing(Series::TABLE_NAME, ['title' => $title]);
        $this->assertDatabaseMissing(SeriesBook::TABLE_NAME, ['book_id' => $books->first()->book_id]);
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
        $series = $this->seriesService->update(Series::factory()->create(), [
            'title' => $originalTitle,
            'author_id' => $author->author_id,
            'book_ids' => $books->pluck('book_id')->toArray(),
        ]);

        $this->failOnInsertInto(SeriesBook::TABLE_NAME, 1);

        try {
            $this->seriesService->update($series, [
                'title' => 'Replacement title',
                'author_id' => $author->author_id,
                'book_ids' => [$replacementBook->book_id],
            ]);
            $this->fail('The injected failure should have aborted the update.');
        } catch (RuntimeException) {
            /* Expected: the replacement book fails to attach, after the detach has run. */
        }

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
     * Throw once the nth insert into the given table has run.
     *
     * A missing foreign key is not usable as the failure here: SQLite treats
     * `PRAGMA foreign_keys` as a no-op inside a transaction, and `RefreshDatabase` opens one
     * before the test starts, so the constraint is not enforced. Failing from a query
     * listener is driver-independent, and it fires *after* the statement has executed, which
     * leaves real rows behind for the rollback to undo rather than testing an empty
     * transaction.
     *
     * @param string $table       The table whose inserts are counted.
     * @param int    $insertCount The 1-based insert to fail on.
     */
    private function failOnInsertInto(string $table, int $insertCount): void
    {
        $insertsSeen = 0;
        DB::listen(function (QueryExecuted $query) use ($table, $insertCount, &$insertsSeen) {
            $isInsert = str_starts_with(strtolower(ltrim($query->sql)), 'insert into')
                && str_contains($query->sql, $table);

            if ($isInsert && ++$insertsSeen === $insertCount) {
                throw new RuntimeException("Injected failure on insert $insertCount into $table.");
            }
        });
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seriesService = new SeriesService();
    }
}
