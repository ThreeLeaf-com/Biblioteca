<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Http\Requests\SeriesRequest;
use ThreeLeaf\Biblioteca\Http\Resources\SeriesResource;
use ThreeLeaf\Biblioteca\Models\Author;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Series;
use PHPUnit\Framework\Attributes\Test;

/** Test {@link SeriesController}. */
class SeriesControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * {@link SeriesController::index()}.
     * @see {@link SeriesResource::collection()}
     */
    #[Test]
    public function indexSeries(): void
    {
        $seriesCollection = Series::factory()->count(3)->create();

        $expectedData = SeriesResource::collection($seriesCollection)->response()->getData(true);

        $response = $this->getJson(route('series.index'));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
    }

    /**
     * {@link SeriesController::store()}.
     * @see {@link SeriesRequest::rules()}
     * @see {@link SeriesResource::toArray()}
     */
    #[Test]
    public function storeSeries(): void
    {
        $author = Author::factory()->create();
        $data = [
            'title' => $this->faker->sentence(),
            'subtitle' => $this->faker->sentence(5),
            'description' => $this->faker->paragraph(),
            'author_id' => $author->author_id,
        ];

        $response = $this->postJson(route('series.store'), $data);
        $response->assertStatus(HttpCodes::HTTP_CREATED);

        $series = Series::latest()->first();
        $expectedData = (new SeriesResource($series))->response()->getData(true);

        $response->assertJson($expectedData)
            ->assertJsonPath('data.author.author_id', $author->author_id)
            ->assertJsonCount(0, 'data.books');
        $this->assertDatabaseHas(Series::TABLE_NAME, $data);
    }

    /**
     * {@link SeriesController::store()} links the books named in `book_ids`, in order.
     *
     * Regression guard: when `store()` called `Series::create()` directly, the
     * validated `book_ids` were dropped and the new series had no books.
     *
     * @see {@link SeriesService::create()}
     */
    #[Test]
    public function storeSeriesWithBookIds(): void
    {
        $author = Author::factory()->create();
        $books = Book::factory()->count(2)->create();
        $bookIds = [$books[1]->book_id, $books[0]->book_id];
        $data = [
            'title' => $this->faker->sentence(),
            'author_id' => $author->author_id,
            'book_ids' => $bookIds,
        ];

        $response = $this->postJson(route('series.store'), $data);

        $response->assertStatus(HttpCodes::HTTP_CREATED)
            ->assertJsonPath('data.author.author_id', $author->author_id)
            ->assertJsonPath('data.books.0.book_id', $bookIds[0])
            ->assertJsonPath('data.books.1.book_id', $bookIds[1]);

        $series = Series::findOrFail($response->json('data.series_id'));
        $this->assertSame($bookIds, $series->books()->pluck(Book::TABLE_NAME . '.book_id')->all());
        $this->assertSame([1, 2], $series->books()->pluck('number')->map(fn($number) => (int)$number)->all());
    }

    /**
     * {@link SeriesController::store()} rejects a `book_ids` entry that names no book, and creates nothing.
     *
     * @see {@link SeriesRequest::rules()}
     */
    #[Test]
    public function storeSeriesWithUnknownBookIdIsRejected(): void
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create();

        $response = $this->postJson(route('series.store'), [
            'title' => $this->faker->sentence(),
            'author_id' => $author->author_id,
            'book_ids' => [$book->book_id, $this->faker->uuid()],
        ]);

        $response->assertStatus(HttpCodes::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('book_ids.1');
        $this->assertDatabaseCount(Series::TABLE_NAME, 0);
    }

    /**
     * {@link SeriesController::store()} rejects a repeated `book_ids` entry, which would otherwise be linked once.
     *
     * @see {@link SeriesRequest::rules()}
     */
    #[Test]
    public function storeSeriesWithRepeatedBookIdIsRejected(): void
    {
        $author = Author::factory()->create();
        $books = Book::factory()->count(2)->create();

        $response = $this->postJson(route('series.store'), [
            'title' => $this->faker->sentence(),
            'author_id' => $author->author_id,
            'book_ids' => [$books[0]->book_id, $books[1]->book_id, $books[0]->book_id],
        ]);

        $response->assertStatus(HttpCodes::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['book_ids.0', 'book_ids.2']);
        $this->assertDatabaseCount(Series::TABLE_NAME, 0);
    }

    /**
     * {@link SeriesController::store()} rejects more than {@link SeriesRequest::MAX_BOOK_IDS} book IDs.
     *
     * @see {@link SeriesRequest::rules()}
     */
    #[Test]
    public function storeSeriesWithTooManyBookIdsIsRejected(): void
    {
        $author = Author::factory()->create();
        $bookIds = array_map(fn() => $this->faker->uuid(), range(0, SeriesRequest::MAX_BOOK_IDS));

        $response = $this->postJson(route('series.store'), [
            'title' => $this->faker->sentence(),
            'author_id' => $author->author_id,
            'book_ids' => $bookIds,
        ]);

        $response->assertStatus(HttpCodes::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('book_ids');
        $this->assertDatabaseCount(Series::TABLE_NAME, 0);
    }

    /**
     * {@link SeriesController::show()}.
     * @see {@link SeriesResource::toArray()}
     */
    #[Test]
    public function showSeries(): void
    {
        $series = Series::factory()->create();

        $expectedData = (new SeriesResource($series))->response()->getData(true);

        $response = $this->getJson(route('series.show', $series->series_id));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
    }

    /**
     * {@link SeriesController::update()}.
     * @see {@link SeriesRequest::rules()}
     * @see {@link SeriesResource::toArray()}
     */
    #[Test]
    public function updateSeries(): void
    {
        $series = Series::factory()->create(['title' => 'Original Title']);
        $updatedData = [
            'title' => 'Updated Series Title',
            'subtitle' => $this->faker->sentence(5),
            'description' => 'Updated description for the series.',
            'author_id' => $series->author_id,
        ];

        $response = $this->putJson(route('series.update', $series->series_id), $updatedData);
        $response->assertStatus(HttpCodes::HTTP_OK);

        $series->refresh();
        $expectedData = (new SeriesResource($series))->response()->getData(true);

        $response->assertJson($expectedData);
        $this->assertDatabaseHas(Series::TABLE_NAME, $updatedData);
    }

    /**
     * {@link SeriesController::destroy()}.
     */
    #[Test]
    public function destroySeries(): void
    {
        $series = Series::factory()->create();

        $response = $this->deleteJson(route('series.destroy', $series->series_id));

        $response->assertStatus(HttpCodes::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(Series::TABLE_NAME, [
            'series_id' => $series->series_id,
        ]);
    }
}
