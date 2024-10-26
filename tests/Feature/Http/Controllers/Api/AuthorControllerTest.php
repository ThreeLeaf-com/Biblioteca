<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Http\Resources\AuthorResource;
use ThreeLeaf\Biblioteca\Models\Author;

/** Test {@link AuthorController}. */
class AuthorControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test {@link AuthorController::index()}.
     * @see {@link AuthorResource::collection()}
     */
    public function indexAuthor(): void
    {
        $authors = Author::factory()->count(3)->create();

        $expectedData = AuthorResource::collection($authors)->response()->getData(true);

        $response = $this->getJson(route('authors.index'));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
    }

    /**
     * @test {@link AuthorController::store()}.
     * @see {@link AuthorRequest::rules()}
     * @see {@link AuthorResource::toArray()}
     */
    public function storeAuthor(): void
    {
        $data = [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'biography' => $this->faker->paragraph(),
            'author_image_url' => $this->faker->imageUrl(),
        ];

        $response = $this->postJson(route('authors.store'), $data);
        $response->assertStatus(HttpCodes::HTTP_CREATED);

        $author = Author::latest()->first();
        $expectedData = (new AuthorResource($author))->response()->getData(true);

        $response->assertJson($expectedData);

        $this->assertDatabaseHas(Author::TABLE_NAME, $data);
    }

    /**
     * @test {@link AuthorController::show()}.
     * @see {@link AuthorResource::toArray()}
     */
    public function showAuthor(): void
    {
        $author = Author::factory()->create();

        $expectedData = (new AuthorResource($author))->response()->getData(true);

        $response = $this->getJson(route('authors.show', $author));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
    }

    /**
     * @test {@link AuthorController::update()}.
     * @see {@link AuthorRequest::rules()}
     * @see {@link AuthorResource::toArray()}
     */
    public function updateAuthor(): void
    {
        $author = Author::factory()->create();
        $updatedData = [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'biography' => $this->faker->paragraph(),
            'author_image_url' => $this->faker->imageUrl(),
        ];

        $response = $this->putJson(route('authors.update', $author), $updatedData);
        $response->assertStatus(HttpCodes::HTTP_OK);

        $author->refresh();
        $expectedData = (new AuthorResource($author))->response()->getData(true);

        $response->assertJson($expectedData);

        $this->assertDatabaseHas(Author::TABLE_NAME, $updatedData);
    }

    /**
     * @test {@link AuthorController::destroy()}.
     */
    public function destroyAuthor(): void
    {
        $author = Author::factory()->create();

        $response = $this->deleteJson(route('authors.destroy', $author));

        $response->assertStatus(HttpCodes::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(Author::TABLE_NAME, [
            'author_id' => $author->author_id,
        ]);

        $response = $this->deleteJson(route('authors.destroy', $author));

        $response->assertStatus(HttpCodes::HTTP_NOT_FOUND);
    }
}
