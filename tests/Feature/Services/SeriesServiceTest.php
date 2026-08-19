<?php

namespace Tests\Feature\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Author;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Series;
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

    protected function setUp(): void
    {
        parent::setUp();
        $this->seriesService = new SeriesService();
    }
}
