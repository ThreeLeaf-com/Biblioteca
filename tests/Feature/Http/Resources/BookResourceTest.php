<?php

namespace Tests\Feature\Http\Resources;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Http\Resources\BookResource;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Genre;
use ThreeLeaf\Biblioteca\Models\Series;
use ThreeLeaf\Biblioteca\Models\Tag;

/** Test {@link BookResource}. */
class BookResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test {@link BookResource::toArray()}. */
    public function showBookResource()
    {
        $book = Book::factory()->create();
        $series = Series::factory()->create();
        $series->attachBook($book->book_id);
        $tag = Tag::factory()->create();
        $book->tags()->attach($tag->tag_id);
        $genre = Genre::factory()->create();
        $book->genres()->attach($genre->genre_id);

        $response = $this->getJson(route('books.show', $book->book_id));

        /* Verify the response structure includes author, publisher, and series */
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'book_id',
                    'title',
                    'author' => [
                        'author_id',
                        'first_name',
                        'last_name',
                        'biography',
                    ],
                    'publisher' => [
                        'publisher_id',
                        'name',
                    ],
                    'series' => [
                        '*' => [
                            'series_id',
                            'title',
                        ],
                    ],
                    'tags' => [
                        '*' => [
                            'tag_id',
                            'name',
                        ],
                    ],
                    'genres' => [
                        '*' => [
                            'genre_id',
                            'name',
                        ],
                    ],
                ],
            ]);

        /* Additional checks to ensure data integrity */
        $this->assertEquals($book->author->author_id, $response->json('data.author.author_id'));
        $this->assertEquals($book->publisher->publisher_id, $response->json('data.publisher.publisher_id'));
        $this->assertEquals($series->series_id, $response->json('data.series.0.series_id'));
    }
}
