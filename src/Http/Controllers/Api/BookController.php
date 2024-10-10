<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use ThreeLeaf\Biblioteca\Http\Controllers\Controller;
use ThreeLeaf\Biblioteca\Http\Requests\BookRequest;
use ThreeLeaf\Biblioteca\Http\Resources\BookResource;
use ThreeLeaf\Biblioteca\Models\Book;

/**
 * Controller for {@link Book}.
 *
 * @OA\Tag(
 *     name="Biblioteca/Books",
 *     description="APIs related to Books in Biblioteca"
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
        $books = Book::all();

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
     *     path="/api/books/{id}",
     *     summary="Get a specific book by ID",
     *     tags={"Biblioteca/Books"},
     *     @OA\Parameter(
     *         name="id",
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
        $book = Book::findOrFail($book_id);

        return new BookResource($book);
    }

    /**
     * Update the specified book in storage.
     *
     * @OA\Put(
     *     path="/api/books/{id}",
     *     summary="Update an existing book",
     *     tags={"Biblioteca/Books"},
     *     @OA\Parameter(
     *         name="id",
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
     *     path="/api/books/{id}",
     *     summary="Delete a specific book",
     *     tags={"Biblioteca/Books"},
     *     @OA\Parameter(
     *         name="id",
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
     *         @OA\JsonContent(type="array", @OA\Items(type="string", example="b1234567-89ab-cdef-0123-456789abcdef")),
     *         description="Array of tag IDs to add"
     *     ),
     *     @OA\Response(response=200, description="Tags added successfully"),
     *     @OA\Response(response=400, description="Invalid request"),
     *     @OA\Response(response=404, description="Book not found")
     * )
     */
    public function addTags(Request $request, $book_id)
    {
        $book = Book::findOrFail($book_id);
        $tagIds = $request->input('tag_ids', []);
        $book->tags()->syncWithoutDetaching($tagIds);

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
    public function removeTag($book_id, $tag_id)
    {
        $book = Book::findOrFail($book_id);
        $book->tags()->detach($tag_id);

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
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="genre_ids",
     *                 type="array",
     *                 @OA\Items(type="string", example="b1234567-89ab-cdef-0123-456789abcdef"),
     *                 description="Array of genre IDs to add"
     *             )
     *         )
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
    public function addGenres(Request $request, $book_id)
    {
        $book = Book::findOrFail($book_id);
        $genreIds = $request->input('genre_ids', []);
        $book->genres()->syncWithoutDetaching($genreIds);

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
    public function removeGenre($book_id, $genre_id)
    {
        $book = Book::findOrFail($book_id);
        $book->genres()->detach($genre_id);

        return response()->json(['message' => 'Genre removed successfully']);
    }
}
