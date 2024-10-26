<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Http\Resources\BookResource;
use ThreeLeaf\Biblioteca\Models\Author;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Genre;
use ThreeLeaf\Biblioteca\Models\Publisher;
use ThreeLeaf\Biblioteca\Models\Tag;

/** Test {@link BookController}. */
class BookControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test {@link BookController::index()}.
     * @see {@link BookResource::collection()}
     */
    public function indexBook(): void
    {
        $books = Book::factory()->count(3)->create();

        $expectedData = BookResource::collection($books)->response()->getData(true);

        $response = $this->getJson(route('books.index'));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
    }

    /**
     * @test {@link BookController::store()}.
     * @see {@link BookRequest::rules()}
     * @see {@link BookResource::toArray()}
     */
    public function storeBook(): void
    {
        $author = Author::factory()->create();
        $publisher = Publisher::factory()->create();
        $data = [
            'title' => $this->faker->sentence(),
            'author_id' => $author->author_id,
            'publisher_id' => $publisher->publisher_id,
            'cover_image_url' => $this->faker->imageUrl(),
        ];

        $response = $this->postJson(route('books.store'), $data);
        $response->assertStatus(HttpCodes::HTTP_CREATED);

        $book = Book::latest()->first();
        $expectedData = (new BookResource($book))->response()->getData(true);

        $response->assertJson($expectedData);

        $this->assertDatabaseHas(Book::TABLE_NAME, $data);
    }

    /**
     * @test {@link BookController::show()}.
     * @see {@link BookResource::toArray()}
     */
    public function showBook(): void
    {
        $book = Book::factory()->create();

        $expectedData = (new BookResource($book))->response()->getData(true);

        $response = $this->getJson(route('books.show', $book));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
    }

    /**
     * @test {@link BookController::update()}.
     * @see {@link BookRequest::rules()}
     * @see {@link BookResource::toArray()}
     */
    public function updateBook(): void
    {
        $book = Book::factory()->create();
        $updatedData = [
            'title' => $this->faker->sentence(),
            'author_id' => $book->author_id,
            'publisher_id' => $book->publisher_id,
        ];
        $this->assertNotEquals($book->title, $updatedData['title']);

        $response = $this->putJson(route('books.update', $book), $updatedData);
        $response->assertStatus(HttpCodes::HTTP_OK);

        $book->refresh();
        $expectedData = (new BookResource($book))->response()->getData(true);

        $response->assertJson($expectedData);

        $this->assertDatabaseHas(Book::TABLE_NAME, $updatedData);
    }

    /**
     * @test {@link BookController::destroy()}.
     */
    public function destroyBook(): void
    {
        $book = Book::factory()->create();

        $response = $this->deleteJson(route('books.destroy', $book));

        $response->assertStatus(HttpCodes::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(Book::TABLE_NAME, [
            'book_id' => $book->book_id,
        ]);

        $response = $this->deleteJson(route('books.destroy', $book));

        $response->assertStatus(HttpCodes::HTTP_NOT_FOUND);
    }

    /** @test {@link BookController::addTags()}. */
    public function addTagsToBook(): void
    {
        $book = Book::factory()->create();
        $tags = Tag::factory()->count(3)->create();

        $tagIds = $tags->pluck('tag_id')->toArray(); // Extract tag IDs for request payload

        $response = $this->postJson(route('books.addTags', ['book_id' => $book->book_id]), ['tag_ids' => $tagIds]);

        $response->assertStatus(HttpCodes::HTTP_OK);
        foreach ($tags as $tag) {
            $this->assertTrue($book->tags()->where('b_book_tags.tag_id', $tag->tag_id)->exists());
        }
    }

    /** @test {@link BookController::removeTag()}. */
    public function removeTagFromBook(): void
    {
        $book = Book::factory()->create();
        $tag = Tag::factory()->create();
        $book->tags()->attach($tag);

        $response = $this->deleteJson(route('books.removeTag', ['book_id' => $book->book_id, 'tag_id' => $tag->tag_id]));

        $response->assertStatus(HttpCodes::HTTP_OK);
        $this->assertFalse($book->tags()->where('b_book_tags.tag_id', $tag->tag_id)->exists());
    }

    /** @test {@link BookController::addGenres()}. */
    public function addGenresToBook(): void
    {
        $book = Book::factory()->create();
        $genres = Genre::factory()->count(3)->create();

        $genreIds = $genres->pluck('genre_id')->toArray(); // Extract genre IDs for request payload

        $response = $this->postJson(route('books.addGenres', ['book_id' => $book->book_id]), ['genre_ids' => $genreIds]);

        $response->assertStatus(HttpCodes::HTTP_OK);
        foreach ($genres as $genre) {
            $this->assertTrue($book->genres()->where('b_book_genres.genre_id', $genre->genre_id)->exists());
        }
    }

    /** @test {@link BookController::removeGenre()}. */
    public function removeGenreFromBook(): void
    {
        $book = Book::factory()->create();
        $genre = Genre::factory()->create();
        $book->genres()->attach($genre);

        $response = $this->deleteJson(route('books.removeGenre', ['book_id' => $book->book_id, 'genre_id' => $genre->genre_id]));

        $response->assertStatus(HttpCodes::HTTP_OK);
        $this->assertFalse($book->genres()->where('b_book_genres.genre_id', $genre->genre_id)->exists());
    }
}
