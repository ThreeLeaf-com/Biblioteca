<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use ThreeLeaf\Biblioteca\Http\Controllers\Controller;
use ThreeLeaf\Biblioteca\Http\Requests\BookGenreRequest;
use ThreeLeaf\Biblioteca\Http\Requests\BookRequest;
use ThreeLeaf\Biblioteca\Http\Requests\BookTagRequest;
use ThreeLeaf\Biblioteca\Http\Resources\BookResource;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Genre;
use ThreeLeaf\Biblioteca\Models\Tag;

/**
 * Controller for {@link Book}.
 */
#[OA\Tag(name: 'Biblioteca/Books', description: 'API Endpoints for managing Books in Biblioteca')]
class BookController extends Controller
{
    /**
     * Display a listing of the books.
     */
    #[OA\Get(
        path: '/api/books',
        summary: 'Get a list of books',
        tags: ['Biblioteca/Books'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/BookResource'),
                ),
            ),
        ],
    )]
    public function index()
    {
        $books = Book::with(['author', 'publisher', 'series', 'tags', 'genres'])->get();

        return BookResource::collection($books);
    }

    /**
     * Store a newly created book in storage.
     */
    #[OA\Post(
        path: '/api/books',
        summary: 'Create a new book',
        tags: ['Biblioteca/Books'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/BookRequest'),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Book created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/BookResource'),
            ),
        ],
    )]
    public function store(BookRequest $request)
    {
        $validatedData = $request->validated();
        $book = Book::create($validatedData);

        return (new BookResource($book))
            ->response()
            ->setStatusCode(HttpCodes::HTTP_CREATED);
    }

    /**
     * Display the specified book.
     */
    #[OA\Get(
        path: '/api/books/{book_id}',
        summary: 'Get a specific book by ID',
        tags: ['Biblioteca/Books'],
        parameters: [
            new OA\Parameter(
                name: 'book_id',
                in: 'path',
                required: true,
                description: 'ID of the book',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(ref: '#/components/schemas/BookResource'),
            ),
            new OA\Response(response: 404, description: 'Book not found'),
        ],
    )]
    public function show($book_id)
    {
        $book = Book::with(['author', 'publisher', 'series', 'tags', 'genres'])->findOrFail($book_id);

        return new BookResource($book);
    }

    /**
     * Update the specified book in storage.
     */
    #[OA\Put(
        path: '/api/books/{book_id}',
        summary: 'Update an existing book',
        tags: ['Biblioteca/Books'],
        parameters: [
            new OA\Parameter(
                name: 'book_id',
                in: 'path',
                required: true,
                description: 'ID of the book',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/BookRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Book updated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/BookResource'),
            ),
            new OA\Response(response: 404, description: 'Book not found'),
        ],
    )]
    public function update(BookRequest $request, $book_id)
    {
        $book = Book::findOrFail($book_id);
        $validatedData = $request->validated();
        $book->update($validatedData);

        return new BookResource($book);
    }

    /**
     * Remove the specified book from storage.
     */
    #[OA\Delete(
        path: '/api/books/{book_id}',
        summary: 'Delete a specific book',
        tags: ['Biblioteca/Books'],
        parameters: [
            new OA\Parameter(
                name: 'book_id',
                in: 'path',
                required: true,
                description: 'ID of the book',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Book deleted successfully'),
            new OA\Response(response: 404, description: 'Book not found'),
        ],
    )]
    public function destroy($book_id)
    {
        $book = Book::findOrFail($book_id);
        $book->delete();

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }

    /**
     * Add tags to a book.
     *
     * @param BookTagRequest $request The validated request containing the tag identifiers.
     * @param string         $book_id The book to attach the tags to.
     *
     * @return JsonResponse Confirmation that the tags were attached.
     */
    #[OA\Post(
        path: '/api/books/{book_id}/tags',
        summary: 'Add tags to a book',
        tags: ['Biblioteca/Tags', 'Biblioteca/Books'],
        parameters: [
            new OA\Parameter(
                name: 'book_id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
                description: 'UUID of the book',
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/BookTagRequest'),
        ),
        responses: [
            new OA\Response(response: 200, description: 'Tags added successfully'),
            new OA\Response(response: 404, description: 'Book not found'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ],
    )]
    public function addTags(BookTagRequest $request, $book_id): JsonResponse
    {
        $book = Book::findOrFail($book_id);
        $book->tags()->syncWithoutDetaching($request->validated('tag_ids'));

        return response()->json(['message' => 'Tags added successfully']);
    }

    /**
     * Remove a tag from a book.
     */
    #[OA\Delete(
        path: '/api/books/{book_id}/tags/{tag_id}',
        summary: 'Remove a tag from a book',
        tags: ['Biblioteca/Tags', 'Biblioteca/Books'],
        parameters: [
            new OA\Parameter(
                name: 'book_id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
                description: 'UUID of the book',
            ),
            new OA\Parameter(
                name: 'tag_id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
                description: 'UUID of the tag',
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Tag removed successfully'),
            new OA\Response(response: 404, description: 'Book or tag not found'),
        ],
    )]
    public function removeTag($book_id, $tag_id): JsonResponse
    {
        $book = Book::findOrFail($book_id);
        /* Resolve rather than detaching the raw path value, so an unknown identifier is a 404. */
        $tag = Tag::findOrFail($tag_id);
        $book->tags()->detach($tag->tag_id);

        return response()->json(['message' => 'Tag removed successfully']);
    }

    /**
     * Add genres to a book.
     *
     * @param BookGenreRequest $request The validated request containing the genre identifiers.
     * @param string           $book_id The book to attach the genres to.
     *
     * @return JsonResponse Confirmation that the genres were attached.
     */
    #[OA\Post(
        path: '/api/books/{book_id}/genres',
        summary: 'Add genres to a book',
        tags: ['Biblioteca/Genres', 'Biblioteca/Books'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'book_id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
                description: 'UUID of the book',
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/BookGenreRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Genres added successfully',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Genres added successfully',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 404, description: 'Book not found'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ],
    )]
    public function addGenres(BookGenreRequest $request, $book_id): JsonResponse
    {
        $book = Book::findOrFail($book_id);
        $book->genres()->syncWithoutDetaching($request->validated('genre_ids'));

        return response()->json(['message' => 'Genres added successfully']);
    }

    /**
     * Remove a genre from a book.
     */
    #[OA\Delete(
        path: '/api/books/{book_id}/genres/{genre_id}',
        summary: 'Remove a genre from a book',
        tags: ['Biblioteca/Genres', 'Biblioteca/Books'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'book_id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
                description: 'UUID of the book',
            ),
            new OA\Parameter(
                name: 'genre_id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
                description: 'UUID of the genre to be removed',
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Genre removed successfully',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Genre removed successfully',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 404, description: 'Book or genre not found'),
        ],
    )]
    public function removeGenre($book_id, $genre_id): JsonResponse
    {
        $book = Book::findOrFail($book_id);
        /* Resolve rather than detaching the raw path value, so an unknown identifier is a 404. */
        $genre = Genre::findOrFail($genre_id);
        $book->genres()->detach($genre->genre_id);

        return response()->json(['message' => 'Genre removed successfully']);
    }
}
