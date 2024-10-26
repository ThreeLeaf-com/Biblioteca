<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Http\Resources\TagResource;
use ThreeLeaf\Biblioteca\Models\Tag;

/** Test {@link TagController}. */
class TagControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test {@link TagController::index()}.
     * @see {@link TagResource::collection()}
     */
    public function indexTag(): void
    {
        $tags = Tag::factory()->count(3)->create();

        $expectedData = TagResource::collection($tags)->response()->getData(true);

        $response = $this->getJson(route('tags.index'));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
    }

    /**
     * @test {@link TagController::store()}.
     * @see {@link TagRequest::rules()}
     * @see {@link TagResource::toArray()}
     */
    public function storeTag(): void
    {
        $data = [
            'name' => $this->faker->word(),
        ];

        $response = $this->postJson(route('tags.store'), $data);
        $response->assertStatus(HttpCodes::HTTP_CREATED);

        $tag = Tag::latest()->first();
        $expectedData = (new TagResource($tag))->response()->getData(true);

        $response->assertJson($expectedData);
        $this->assertDatabaseHas(Tag::TABLE_NAME, $data);
    }

    /**
     * @test {@link TagController::show()}.
     * @see {@link TagResource::toArray()}
     */
    public function showTag(): void
    {
        $tag = Tag::factory()->create();

        $expectedData = (new TagResource($tag))->response()->getData(true);

        $response = $this->getJson(route('tags.show', $tag->tag_id));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
    }

    /**
     * @test {@link TagController::update()}.
     * @see {@link TagRequest::rules()}
     * @see {@link TagResource::toArray()}
     */
    public function updateTag(): void
    {
        $tag = Tag::factory()->create(['name' => 'Original Tag']);
        $updatedData = [
            'name' => 'Updated Tag Name',
        ];

        $response = $this->putJson(route('tags.update', $tag->tag_id), $updatedData);
        $response->assertStatus(HttpCodes::HTTP_OK);

        $tag->refresh();
        $expectedData = (new TagResource($tag))->response()->getData(true);

        $response->assertJson($expectedData);
        $this->assertDatabaseHas(Tag::TABLE_NAME, $updatedData);
    }

    /**  @test {@link TagController::destroy()}. */
    public function destroyTag(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->deleteJson(route('tags.destroy', $tag->tag_id));

        $response->assertStatus(HttpCodes::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(Tag::TABLE_NAME, [
            'tag_id' => $tag->tag_id,
        ]);
    }
}
