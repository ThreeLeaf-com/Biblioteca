<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Http\Resources\BookResource;
use ThreeLeaf\Biblioteca\Models\Author;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Genre;
use ThreeLeaf\Biblioteca\Models\Publisher;
use ThreeLeaf\Biblioteca\Models\Tag;
use PHPUnit\Framework\Attributes\Test;

/** Test {@link BookController}. */
class BookControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * {@link BookController::index()}.
     * @see {@link BookResource::collection()}
     */
    #[Test]
    public function indexBook(): void
    {
        $books = Book::factory()->count(3)->create();

        $expectedData = BookResource::collection($books)->response()->getData(true);

        $response = $this->getJson(route('books.index'));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
    }

    /**
     * {@link BookController::store()}.
     * @see {@link BookRequest::rules()}
     * @see {@link BookResource::toArray()}
     */
    #[Test]
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
     * {@link BookController::show()}.
     * @see {@link BookResource::toArray()}
     */
    #[Test]
    public function showBook(): void
    {
        $book = Book::factory()->create();

        $expectedData = (new BookResource($book))->response()->getData(true);

        $response = $this->getJson(route('books.show', $book));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson($expectedData);
    }

    /**
     * {@link BookController::update()}.
     * @see {@link BookRequest::rules()}
     * @see {@link BookResource::toArray()}
     */
    #[Test]
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
     * {@link BookController::destroy()}.
     */
    #[Test]
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

    /** {@link BookController::addTags()}. */
    #[Test]
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

    /** {@link BookController::removeTag()}. */
    #[Test]
    public function removeTagFromBook(): void
    {
        $book = Book::factory()->create();
        $tag = Tag::factory()->create();
        $book->tags()->attach($tag);

        $response = $this->deleteJson(route('books.removeTag', ['book_id' => $book->book_id, 'tag_id' => $tag->tag_id]));

        $response->assertStatus(HttpCodes::HTTP_OK);
        $this->assertFalse($book->tags()->where('b_book_tags.tag_id', $tag->tag_id)->exists());
    }

    /** {@link BookController::addGenres()}. */
    #[Test]
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

    /** {@link BookController::removeGenre()}. */
    #[Test]
    public function removeGenreFromBook(): void
    {
        $book = Book::factory()->create();
        $genre = Genre::factory()->create();
        $book->genres()->attach($genre);

        $response = $this->deleteJson(route('books.removeGenre', ['book_id' => $book->book_id, 'genre_id' => $genre->genre_id]));

        $response->assertStatus(HttpCodes::HTTP_OK);
        $this->assertFalse($book->genres()->where('b_book_genres.genre_id', $genre->genre_id)->exists());
    }

    /**
     * {@link BookController::addTags()} rejects anything but existing tag identifiers.
     *
     * @see {@link \ThreeLeaf\Biblioteca\Http\Requests\BookTagRequest::rules()}
     */
    #[Test]
    public function addTagsRejectsInvalidInput(): void
    {
        $book = Book::factory()->create();
        $payloads = [
            'missing' => [],
            'not an array' => ['tag_ids' => 'not-an-array'],
            'empty string element' => ['tag_ids' => ['']],
            'not a uuid' => ['tag_ids' => ['nonsense']],
            'unknown uuid' => ['tag_ids' => [fake()->uuid()]],
        ];

        foreach ($payloads as $label => $payload) {
            $response = $this->postJson(route('books.addTags', ['book_id' => $book->book_id]), $payload);

            $response->assertStatus(HttpCodes::HTTP_UNPROCESSABLE_ENTITY);
            $this->assertSame(0, $book->tags()->count(), "Tags were attached for: $label");
        }
    }

    /** {@link BookController::addTags()} rejects a batch containing one unknown identifier. */
    #[Test]
    public function addTagsRejectsAPartiallyValidBatch(): void
    {
        $book = Book::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->postJson(route('books.addTags', ['book_id' => $book->book_id]), [
            'tag_ids' => [$tag->tag_id, fake()->uuid()],
        ]);

        $response->assertStatus(HttpCodes::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('tag_ids.1');

        /* Nothing is attached, not even the valid half. */
        $this->assertSame(0, $book->tags()->count());
    }

    /**
     * {@link BookController::addGenres()} rejects anything but existing genre identifiers.
     *
     * @see {@link \ThreeLeaf\Biblioteca\Http\Requests\BookGenreRequest::rules()}
     */
    #[Test]
    public function addGenresRejectsInvalidInput(): void
    {
        $book = Book::factory()->create();
        $payloads = [
            'missing' => [],
            'not an array' => ['genre_ids' => 'not-an-array'],
            'not a uuid' => ['genre_ids' => ['nonsense']],
            'unknown uuid' => ['genre_ids' => [fake()->uuid()]],
        ];

        foreach ($payloads as $label => $payload) {
            $response = $this->postJson(route('books.addGenres', ['book_id' => $book->book_id]), $payload);

            $response->assertStatus(HttpCodes::HTTP_UNPROCESSABLE_ENTITY);
            $this->assertSame(0, $book->genres()->count(), "Genres were attached for: $label");
        }
    }

    /** {@link BookController::removeTag()} reports an unknown tag rather than failing at the database. */
    #[Test]
    public function removeTagRejectsUnknownIdentifier(): void
    {
        $book = Book::factory()->create();

        foreach ([fake()->uuid(), 'nonsense'] as $tagId) {
            $response = $this->deleteJson(
                route('books.removeTag', ['book_id' => $book->book_id, 'tag_id' => $tagId])
            );

            $response->assertStatus(HttpCodes::HTTP_NOT_FOUND);
        }
    }

    /** {@link BookController::removeGenre()} reports an unknown genre rather than failing at the database. */
    #[Test]
    public function removeGenreRejectsUnknownIdentifier(): void
    {
        $book = Book::factory()->create();

        foreach ([fake()->uuid(), 'nonsense'] as $genreId) {
            $response = $this->deleteJson(
                route('books.removeGenre', ['book_id' => $book->book_id, 'genre_id' => $genreId])
            );

            $response->assertStatus(HttpCodes::HTTP_NOT_FOUND);
        }
    }

    /** Removing a tag that exists but is not attached is still a success, and detaches nothing. */
    #[Test]
    public function removeTagThatIsNotAttachedSucceeds(): void
    {
        $book = Book::factory()->create();
        $attached = Tag::factory()->create();
        $unattached = Tag::factory()->create();
        $book->tags()->attach($attached);

        $response = $this->deleteJson(
            route('books.removeTag', ['book_id' => $book->book_id, 'tag_id' => $unattached->tag_id])
        );

        $response->assertStatus(HttpCodes::HTTP_OK);
        $this->assertSame(1, $book->tags()->count());
    }

    /**
     * The element rules reject a value that trims to empty, without relying on middleware.
     *
     * Laravel skips an element's whole rule set when the value trims to empty and no
     * implicit rule is present, so `['"'"''"'"']` would pass through unvalidated. The stock
     * `ConvertEmptyStringsToNull` middleware masks this over HTTP, which is why this is
     * asserted against the rules directly — the guarantee has to come from the rules, not
     * from the host's middleware stack.
     */
    #[Test]
    public function elementRulesRejectEmptyAndWhitespaceIdentifiers(): void
    {
        $tag = Tag::factory()->create();
        $genre = Genre::factory()->create();

        foreach ([
            [new \ThreeLeaf\Biblioteca\Http\Requests\BookTagRequest(), 'tag_ids', $tag->tag_id],
            [new \ThreeLeaf\Biblioteca\Http\Requests\BookGenreRequest(), 'genre_ids', $genre->genre_id],
        ] as [$request, $field, $validId]) {
            $rules = $request->rules();

            foreach (['', ' ', "\t"] as $value) {
                $this->assertTrue(
                    Validator::make([$field => [$value]], $rules)->fails(),
                    "$field accepted a value that trims to empty.",
                );
            }

            $this->assertFalse(
                Validator::make([$field => [$validId]], $rules)->fails(),
                "$field rejected a valid identifier.",
            );
        }
    }

    /** Removing a genre that exists but is not attached is still a success. */
    #[Test]
    public function removeGenreThatIsNotAttachedSucceeds(): void
    {
        $book = Book::factory()->create();
        $attached = Genre::factory()->create();
        $unattached = Genre::factory()->create();
        $book->genres()->attach($attached);

        $response = $this->deleteJson(
            route('books.removeGenre', ['book_id' => $book->book_id, 'genre_id' => $unattached->genre_id])
        );

        $response->assertStatus(HttpCodes::HTTP_OK);
        $this->assertSame(1, $book->genres()->count());
    }

    /** An empty or absent identifier list is rejected rather than silently doing nothing. */
    #[Test]
    public function addTagsRejectsAnEmptyList(): void
    {
        $book = Book::factory()->create();

        foreach ([[], ['tag_ids' => []], ['tag_ids' => null]] as $payload) {
            $this->postJson(route('books.addTags', ['book_id' => $book->book_id]), $payload)
                ->assertStatus(HttpCodes::HTTP_UNPROCESSABLE_ENTITY);
        }

        foreach ([[], ['genre_ids' => []], ['genre_ids' => null]] as $payload) {
            $this->postJson(route('books.addGenres', ['book_id' => $book->book_id]), $payload)
                ->assertStatus(HttpCodes::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
