<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Http\Resources\GenreResource;
use ThreeLeaf\Biblioteca\Models\Genre;

/** Test {@link GenreController}. */
class GenreControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test {@link GenreController::index()}.
     * @see {@link GenreResource::collection()}
     */
    public function indexGenre(): void
    {
        $genres = Genre::factory()->count(3)->create();

        $expectedData = GenreResource::collection($genres)->response()->getData(true);

        $response = $this->getJson(route('genres.index'));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
    }

    /**
     * @test {@link GenreController::store()}.
     * @see {@link GenreRequest::rules()}
     * @see {@link GenreResource::toArray()}
     */
    public function storeGenre(): void
    {
        $data = [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
        ];

        $response = $this->postJson(route('genres.store'), $data);
        $response->assertStatus(HttpCodes::HTTP_CREATED);

        $genre = Genre::latest()->first();
        $expectedData = (new GenreResource($genre))->response()->getData(true);

        $response->assertJson($expectedData);
        $this->assertDatabaseHas(Genre::TABLE_NAME, $data);
    }

    /**
     * @test {@link GenreController::show()}.
     * @see {@link GenreResource::toArray()}
     */
    public function showGenre(): void
    {
        $genre = Genre::factory()->create();

        $expectedData = (new GenreResource($genre))->response()->getData(true);

        $response = $this->getJson(route('genres.show', $genre->genre_id));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
    }

    /**
     * @test {@link GenreController::update()}.
     * @see {@link GenreRequest::rules()}
     * @see {@link GenreResource::toArray()}
     */
    public function updateGenre(): void
    {
        $genre = Genre::factory()->create(['name' => 'Original Genre']);
        $updatedData = [
            'name' => 'Updated Genre Name',
            'description' => 'Updated description for the genre.',
        ];

        $response = $this->putJson(route('genres.update', $genre->genre_id), $updatedData);
        $response->assertStatus(HttpCodes::HTTP_OK);

        $genre->refresh();
        $expectedData = (new GenreResource($genre))->response()->getData(true);

        $response->assertJson($expectedData);
        $this->assertDatabaseHas(Genre::TABLE_NAME, $updatedData);
    }

    /**
     * @test {@link GenreController::destroy()}.
     */
    public function destroyGenre(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->deleteJson(route('genres.destroy', $genre->genre_id));

        $response->assertStatus(HttpCodes::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(Genre::TABLE_NAME, [
            'genre_id' => $genre->genre_id,
        ]);
    }
}
