<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
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
 *
 * @OA\Tag(
 *     name="Biblioteca/Books",
 *     description="API Endpoints for managing Books in Biblioteca"
 * )
 */
class BookController extends Controller
{
    /**
     * Display a listing of the books.
     *
     * @OA\Get(
     *     path="/api/books",
     *     summary="Get a list of books",
     *     tags={"Biblioteca/Books"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/BookResource")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $books = Book::with(['author', 'publisher', 'series', 'tags', 'genres'])->get();

        return BookResource::collection($books);
    }

    /**
     * Store a newly created book in storage.
     *
     * @OA\Post(
     *     path="/api/books",
     *     summary="Create a new book",
     *     tags={"Biblioteca/Books"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/BookRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Book created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/BookResource")
     *     )
     * )
     */
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
     *
     * @OA\Get(
     *     path="/api/books/{book_id}",
     *     summary="Get a specific book by ID",
     *     tags={"Biblioteca/Books"},
     *     @OA\Parameter(
     *         name="book_id",
     *         in="path",
     *         required=true,
     *         description="ID of the book",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/BookResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found"
     *     )
     * )
     */
    public function show($book_id)
    {
        $book = Book::with(['author', 'publisher', 'series', 'tags', 'genres'])->findOrFail($book_id);

        return new BookResource($book);
    }

    /**
     * Update the specified book in storage.
     *
     * @OA\Put(
     *     path="/api/books/{book_id}",
     *     summary="Update an existing book",
     *     tags={"Biblioteca/Books"},
     *     @OA\Parameter(
     *         name="book_id",
     *         in="path",
     *         required=true,
     *         description="ID of the book",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/BookRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Book updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/BookResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found"
     *     )
     * )
     */
    public function update(BookRequest $request, $book_id)
    {
        $book = Book::findOrFail($book_id);
        $validatedData = $request->validated();
        $book->update($validatedData);

        return new BookResource($book);
    }

    /**
     * Remove the specified book from storage.
     *
     * @OA\Delete(
     *     path="/api/books/{book_id}",
     *     summary="Delete a specific book",
     *     tags={"Biblioteca/Books"},
     *     @OA\Parameter(
     *         name="book_id",
     *         in="path",
     *         required=true,
     *         description="ID of the book",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Book deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found"
     *     )
     * )
     */
    public function destroy($book_id)
    {
        $book = Book::findOrFail($book_id);
        $book->delete();

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }

    /**
     * Add tags to a book.
     *
     * @OA\Post(
     *     path="/api/books/{book_id}/tags",
     *     summary="Add tags to a book",
     *     tags={"Biblioteca/Tags", "Biblioteca/Books"},
     *     @OA\Parameter(
     *         name="book_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="UUID of the book"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/BookTagRequest")
     *     ),
     *     @OA\Response(response=200, description="Tags added successfully"),
     *     @OA\Response(response=404, description="Book not found"),
     *     @OA\Response(response=422, description="Validation failed")
     * )
     *
     * @param BookTagRequest $request The validated request containing the tag identifiers.
     * @param string         $book_id The book to attach the tags to.
     *
     * @return JsonResponse Confirmation that the tags were attached.
     */
    public function addTags(BookTagRequest $request, $book_id): JsonResponse
    {
        $book = Book::findOrFail($book_id);
        $book->tags()->syncWithoutDetaching($request->validated('tag_ids'));

        return response()->json(['message' => 'Tags added successfully']);
    }

    /**
     * Remove a tag from a book.
     *
     * @OA\Delete(
     *     path="/api/books/{book_id}/tags/{tag_id}",
     *     summary="Remove a tag from a book",
     *     tags={"Biblioteca/Tags", "Biblioteca/Books"},
     *     @OA\Parameter(
     *         name="book_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="UUID of the book"
     *     ),
     *     @OA\Parameter(
     *         name="tag_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="UUID of the tag"
     *     ),
     *     @OA\Response(response=200, description="Tag removed successfully"),
     *     @OA\Response(response=404, description="Book or tag not found")
     * )
     */
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
     * @OA\Post(
     *     path="/api/books/{book_id}/genres",
     *     summary="Add genres to a book",
     *     tags={"Biblioteca/Genres", "Biblioteca/Books"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="book_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="UUID of the book"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/BookGenreRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Genres added successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Genres added successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found"
     *     )
     * )
     */
    public function addGenres(BookGenreRequest $request, $book_id): JsonResponse
    {
        $book = Book::findOrFail($book_id);
        $book->genres()->syncWithoutDetaching($request->validated('genre_ids'));

        return response()->json(['message' => 'Genres added successfully']);
    }

    /**
     * Remove a genre from a book.
     *
     * @OA\Delete(
     *     path="/api/books/{book_id}/genres/{genre_id}",
     *     summary="Remove a genre from a book",
     *     tags={"Biblioteca/Genres", "Biblioteca/Books"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="book_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="UUID of the book"
     *     ),
     *     @OA\Parameter(
     *         name="genre_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="UUID of the genre to be removed"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Genre removed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Genre removed successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book or genre not found"
     *     )
     * )
     */
    public function removeGenre($book_id, $genre_id): JsonResponse
    {
        $book = Book::findOrFail($book_id);
        /* Resolve rather than detaching the raw path value, so an unknown identifier is a 404. */
        $genre = Genre::findOrFail($genre_id);
        $book->genres()->detach($genre->genre_id);

        return response()->json(['message' => 'Genre removed successfully']);
    }
}
