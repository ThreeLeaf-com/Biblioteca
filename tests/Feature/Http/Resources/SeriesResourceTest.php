<?php

namespace Tests\Feature\Http\Resources;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Http\Resources\SeriesResource;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Series;

/** Test {@link SeriesResource}. */
class SeriesResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test {@link SeriesResource::toArray()}. */
    public function toArray()
    {
        $series = Series::factory()->create();
        $author = $series->author;

        $books = Book::factory()->count(3)->create();
        $number = 0;
        $series->books()->attach($books->pluck('book_id'), ['number' => ++$number]);

        $response = $this->getJson(route('series.show', $series->series_id));

        // Assert response structure and data
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'series_id',
                    'title',
                    'subtitle',
                    'description',
                    'author' => [
                        'author_id',
                        'first_name',
                        'last_name',
                    ],
                    'books' => [
                        '*' => [
                            'book_id',
                            'title',
                        ],
                    ],
                ],
            ]);

        // Verify specific data in the response matches the created author and books
        $this->assertEquals($series->series_id, $response->json('data.series_id'));
        $this->assertEquals($author->author_id, $response->json('data.author.author_id'));
        $this->assertCount(3, $response->json('data.books'));
        $expectedBookIds = $books->pluck('book_id')->toArray();
        $actualBookIds = collect($response->json('data.books'))->pluck('book_id')->sort()->toArray();
        $this->assertEquals(sort($expectedBookIds), sort($actualBookIds));
    }
}
