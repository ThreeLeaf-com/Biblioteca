<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Http\Resources\TagResource;
use ThreeLeaf\Biblioteca\Models\Tag;
use PHPUnit\Framework\Attributes\Test;

/** Test {@link TagController}. */
class TagControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * {@link TagController::index()}.
     * @see {@link TagResource::collection()}
     */
    #[Test]
    public function indexTag(): void
    {
        $tags = Tag::factory()->count(3)->create();

        $expectedData = TagResource::collection($tags)->response()->getData(true);

        $response = $this->getJson(route('tags.index'));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
    }

    /**
     * {@link TagController::store()}.
     * @see {@link TagRequest::rules()}
     * @see {@link TagResource::toArray()}
     */
    #[Test]
    public function storeTag(): void
    {
        $data = [
            'name' => $this->faker->regexify('[A-Za-z0-9]{20}'),
        ];

        $response = $this->postJson(route('tags.store'), $data);
        $response->assertStatus(HttpCodes::HTTP_CREATED);

        $tag = Tag::latest()->first();
        $expectedData = (new TagResource($tag))->response()->getData(true);

        $response->assertJson($expectedData);
        $this->assertDatabaseHas(Tag::TABLE_NAME, $data);
    }

    /**
     * {@link TagController::show()}.
     * @see {@link TagResource::toArray()}
     */
    #[Test]
    public function showTag(): void
    {
        $tag = Tag::factory()->create();

        $expectedData = (new TagResource($tag))->response()->getData(true);

        $response = $this->getJson(route('tags.show', $tag->tag_id));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
    }

    /**
     * {@link TagController::update()}.
     * @see {@link TagRequest::rules()}
     * @see {@link TagResource::toArray()}
     */
    #[Test]
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

    /** {@link TagController::destroy()}. */
    #[Test]
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
