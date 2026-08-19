<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Http\Resources\PublisherResource;
use ThreeLeaf\Biblioteca\Models\Publisher;
use PHPUnit\Framework\Attributes\Test;

/** Test {@link PublisherController}. */
class PublisherControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * {@link PublisherController::index()}.
     * @see {@link PublisherResource::collection()}
     */
    #[Test]
    public function indexPublisher(): void
    {
        $publishers = Publisher::factory()->count(3)->create();

        $expectedData = PublisherResource::collection($publishers)->response()->getData(true);

        $response = $this->getJson(route('publishers.index'));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
    }

    /**
     * {@link PublisherController::store()}.
     * @see {@link PublisherRequest::rules()}
     * @see {@link PublisherResource::toArray()}
     */
    #[Test]
    public function storePublisher(): void
    {
        $data = [
            'name' => $this->faker->company(),
            'address' => $this->faker->address(),
            'website' => $this->faker->url(),
        ];

        $response = $this->postJson(route('publishers.store'), $data);
        $response->assertStatus(HttpCodes::HTTP_CREATED);

        $publisher = Publisher::latest()->first();
        $expectedData = (new PublisherResource($publisher))->response()->getData(true);

        $response->assertJson($expectedData);
        $this->assertDatabaseHas(Publisher::TABLE_NAME, $data);
    }

    /**
     * {@link PublisherController::show()}.
     * @see {@link PublisherResource::toArray()}
     */
    #[Test]
    public function showPublisher(): void
    {
        $publisher = Publisher::factory()->create();

        $expectedData = (new PublisherResource($publisher))->response()->getData(true);

        $response = $this->getJson(route('publishers.show', $publisher->publisher_id));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
    }

    /**
     * {@link PublisherController::update()}.
     * @see {@link PublisherRequest::rules()}
     * @see {@link PublisherResource::toArray()}
     */
    #[Test]
    public function updatePublisher(): void
    {
        $publisher = Publisher::factory()->create(['name' => 'Original Name']);
        $updatedData = [
            'name' => 'Updated Publisher Name',
            'address' => $this->faker->address(),
            'website' => $this->faker->url(),
        ];

        $response = $this->putJson(route('publishers.update', $publisher->publisher_id), $updatedData);
        $response->assertStatus(HttpCodes::HTTP_OK);

        $publisher->refresh();
        $expectedData = (new PublisherResource($publisher))->response()->getData(true);

        $response->assertJson($expectedData);
        $this->assertDatabaseHas(Publisher::TABLE_NAME, $updatedData);
    }

    /**
     * {@link PublisherController::destroy()}.
     */
    #[Test]
    public function destroyPublisher(): void
    {
        $publisher = Publisher::factory()->create();

        $response = $this->deleteJson(route('publishers.destroy', $publisher->publisher_id));

        $response->assertStatus(HttpCodes::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(Publisher::TABLE_NAME, [
            'publisher_id' => $publisher->publisher_id,
        ]);
    }
}
