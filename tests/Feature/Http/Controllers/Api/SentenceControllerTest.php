<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Http\Resources\SentenceResource;
use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Models\Sentence;

/** Test {@link SentenceController}. */
class SentenceControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test {@link SentenceController::index()}.
     * @see {@link SentenceResource::collection()}
     */
    public function indexSentence(): void
    {
        $sentences = Sentence::factory()->count(3)->create();

        $expectedData = SentenceResource::collection($sentences)->response()->getData(true);

        $response = $this->getJson(route('sentences.index'));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
    }

    /**
     * @test {@link SentenceController::store()}.
     * @see {@link SentenceRequest::rules()}
     * @see {@link SentenceResource::toArray()}
     */
    public function storeSentence(): void
    {
        $paragraph = Paragraph::factory()->create();
        $data = [
            'paragraph_id' => $paragraph->paragraph_id,
            'sentence_number' => $this->faker->numberBetween(1, 100),
            'content' => $this->faker->sentence(),
        ];

        $response = $this->postJson(route('sentences.store'), $data);
        $response->assertStatus(HttpCodes::HTTP_CREATED);

        $sentence = Sentence::latest()->first();
        $expectedData = (new SentenceResource($sentence))->response()->getData(true);

        $response->assertJson($expectedData);
        $this->assertDatabaseHas(Sentence::TABLE_NAME, $data);
    }

    /**
     * @test {@link SentenceController::show()}.
     * @see {@link SentenceResource::toArray()}
     */
    public function showSentence(): void
    {
        $sentence = Sentence::factory()->create();

        $expectedData = (new SentenceResource($sentence))->response()->getData(true);

        $response = $this->getJson(route('sentences.show', $sentence->sentence_id));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
    }

    /**
     * @test {@link SentenceController::update()}.
     * @see {@link SentenceRequest::rules()}
     * @see {@link SentenceResource::toArray()}
     */
    public function updateSentence(): void
    {
        $sentence = Sentence::factory()->create(['content' => 'Original Content']);
        $updatedData = [
            'paragraph_id' => $sentence->paragraph_id,
            'sentence_number' => $this->faker->numberBetween(1, 100),
            'content' => 'Updated sentence content.',
        ];

        $response = $this->putJson(route('sentences.update', $sentence->sentence_id), $updatedData);
        $response->assertStatus(HttpCodes::HTTP_OK);

        $sentence->refresh();
        $expectedData = (new SentenceResource($sentence))->response()->getData(true);

        $response->assertJson($expectedData);
        $this->assertDatabaseHas(Sentence::TABLE_NAME, $updatedData);
    }

    /** @test {@link SentenceController::destroy()}. */
    public function destroySentence(): void
    {
        $sentence = Sentence::factory()->create();

        $response = $this->deleteJson(route('sentences.destroy', $sentence->sentence_id));

        $response->assertStatus(HttpCodes::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(Sentence::TABLE_NAME, [
            'sentence_id' => $sentence->sentence_id,
        ]);
    }
}
