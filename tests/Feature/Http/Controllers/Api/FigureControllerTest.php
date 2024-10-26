<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Http\Resources\FigureResource;
use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Models\Figure;

/** Test {@link FigureController}. */
class FigureControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test {@link FigureController::index()}.
     * @see {@link FigureResource::collection()}
     */
    public function indexFigure(): void
    {
        $figures = Figure::factory()->count(3)->create();

        $expectedData = FigureResource::collection($figures)->response()->getData(true);

        $response = $this->getJson(route('figures.index'));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
    }

    /**
     * @test {@link FigureController::store()}.
     * @see {@link FigureRequest::rules()}
     * @see {@link FigureResource::toArray()}
     */
    public function storeFigure(): void
    {
        $chapter = Chapter::factory()->create();
        $data = [
            'chapter_id' => $chapter->chapter_id,
            'figure_label' => $this->faker->lexify('Fig ???'),
            'caption' => $this->faker->sentence(),
            'image_url' => $this->faker->imageUrl(),
            'description' => $this->faker->paragraph(),
        ];

        $response = $this->postJson(route('figures.store'), $data);
        $response->assertStatus(HttpCodes::HTTP_CREATED);

        $figure = Figure::latest()->first();
        $expectedData = (new FigureResource($figure))->response()->getData(true);

        $response->assertJson($expectedData);
        $this->assertDatabaseHas(Figure::TABLE_NAME, $data);
    }

    /**
     * @test {@link FigureController::show()}.
     * @see {@link FigureResource::toArray()}
     */
    public function showFigure(): void
    {
        $figure = Figure::factory()->create();

        $expectedData = (new FigureResource($figure))->response()->getData(true);

        $response = $this->getJson(route('figures.show', $figure->figure_id));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
    }

    /**
     * @test {@link FigureController::update()}.
     * @see {@link FigureRequest::rules()}
     * @see {@link FigureResource::toArray()}
     */
    public function updateFigure(): void
    {
        $figure = Figure::factory()->create(['caption' => 'Original Caption']);
        $updatedData = [
            'chapter_id' => $figure->chapter_id,
            'figure_label' => $this->faker->lexify('Fig ???'),
            'caption' => 'Updated Caption',
            'image_url' => $this->faker->imageUrl(),
            'description' => $this->faker->paragraph(),
        ];

        $response = $this->putJson(route('figures.update', $figure->figure_id), $updatedData);
        $response->assertStatus(HttpCodes::HTTP_OK);

        $figure->refresh();
        $expectedData = (new FigureResource($figure))->response()->getData(true);

        $response->assertJson($expectedData);
        $this->assertDatabaseHas(Figure::TABLE_NAME, $updatedData);
    }

    /**
     * @test {@link FigureController::destroy()}.
     */
    public function destroyFigure(): void
    {
        $figure = Figure::factory()->create();

        $response = $this->deleteJson(route('figures.destroy', $figure->figure_id));

        $response->assertStatus(HttpCodes::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(Figure::TABLE_NAME, [
            'figure_id' => $figure->figure_id,
        ]);
    }
}
