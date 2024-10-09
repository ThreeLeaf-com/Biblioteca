<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

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
}
