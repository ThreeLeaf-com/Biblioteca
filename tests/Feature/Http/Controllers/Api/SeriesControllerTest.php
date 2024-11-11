<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Http\Resources\SeriesResource;
use ThreeLeaf\Biblioteca\Models\Author;
use ThreeLeaf\Biblioteca\Models\Series;

/** Test {@link SeriesController}. */
class SeriesControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test {@link SeriesController::index()}.
     * @see {@link SeriesResource::collection()}
     */
    public function indexSeries(): void
    {
        $seriesCollection = Series::factory()->count(3)->create();

        $expectedData = SeriesResource::collection($seriesCollection)->response()->getData(true);

        $response = $this->getJson(route('series.index'));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
    }

    /**
     * @test {@link SeriesController::store()}.
     * @see {@link SeriesRequest::rules()}
     * @see {@link SeriesResource::toArray()}
     */
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
     * @test {@link SeriesController::show()}.
     * @see {@link SeriesResource::toArray()}
     */
    public function showSeries(): void
    {
        $series = Series::factory()->create();

        $expectedData = (new SeriesResource($series))->response()->getData(true);

        $response = $this->getJson(route('series.show', $series->series_id));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
    }

    /**
     * @test {@link SeriesController::update()}.
     * @see {@link SeriesRequest::rules()}
     * @see {@link SeriesResource::toArray()}
     */
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
     * @test {@link SeriesController::destroy()}.
     */
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
