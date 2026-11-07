<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use Tests\Feature\TestCase;
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

        $response->assertJson($expectedData);
        $this->assertDatabaseHas(Series::TABLE_NAME, $data);
    }

    /**
     * {@link SeriesController::store()} links the books named in `book_ids`, in order.
     *
     * Issue #46: `store()` called `Series::create()` directly, so the validated
     * `book_ids` were dropped and the new series had no books.
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
            ->assertJsonPath('data.books.0.book_id', $bookIds[0])
            ->assertJsonPath('data.books.1.book_id', $bookIds[1]);

        $series = Series::findOrFail($response->json('data.series_id'));
        $this->assertSame($bookIds, $series->books()->pluck(Book::TABLE_NAME . '.book_id')->all());
        $this->assertSame([1, 2], $series->books()->pluck('number')->map(fn($number) => (int)$number)->all());
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
