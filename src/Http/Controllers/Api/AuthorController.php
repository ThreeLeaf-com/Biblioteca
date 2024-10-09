<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

use Symfony\Component\HttpFoundation\Response as HttpCodes;
use ThreeLeaf\Biblioteca\Http\Controllers\Controller;
use ThreeLeaf\Biblioteca\Http\Requests\AuthorRequest;
use ThreeLeaf\Biblioteca\Http\Resources\AuthorResource;
use ThreeLeaf\Biblioteca\Models\Author;

/**
 * Controller for {@link Author}.
 *
 * @OA\Tag(
 *     name="Biblioteca/Authors",
 *     description="APIs related to Authors in Biblioteca"
 * )
 */
class AuthorController extends Controller
{
    /**
     * Display a listing of the authors.
     *
     * @OA\Get(
     *     path="/api/authors",
     *     summary="Get a list of authors",
     *     tags={"Biblioteca/Authors"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/AuthorResource")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $authors = Author::all();

        return AuthorResource::collection($authors);
    }

    /**
     * Store a newly created author in storage.
     *
     * @OA\Post(
     *     path="/api/authors",
     *     summary="Create a new author",
     *     tags={"Biblioteca/Authors"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/AuthorRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Author created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/AuthorResource")
     *     )
     * )
     */
    public function store(AuthorRequest $request)
    {
        $validatedData = $request->validated();
        $author = Author::create($validatedData);

        return (new AuthorResource($author))
            ->response()
            ->setStatusCode(HttpCodes::HTTP_CREATED);
    }

    /**
     * Display the specified author.
     *
     * @OA\Get(
     *     path="/api/authors/{id}",
     *     summary="Get a specific author by ID",
     *     tags={"Biblioteca/Authors"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the author",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/AuthorResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Author not found"
     *     )
     * )
     */
    public function show($author_id)
    {
        $author = Author::findOrFail($author_id);

        return new AuthorResource($author);
    }

    /**
     * Update the specified author in storage.
     *
     * @OA\Put(
     *     path="/api/authors/{id}",
     *     summary="Update an existing author",
     *     tags={"Biblioteca/Authors"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the author",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/AuthorRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Author updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/AuthorResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Author not found"
     *     )
     * )
     */
    public function update(AuthorRequest $request, $author_id)
    {
        $author = Author::findOrFail($author_id);
        $validatedData = $request->validated();
        $author->update($validatedData);

        return new AuthorResource($author);
    }

    /**
     * Remove the specified author from storage.
     *
     * @OA\Delete(
     *     path="/api/authors/{id}",
     *     summary="Delete a specific author",
     *     tags={"Biblioteca/Authors"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the author",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Author deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Author not found"
     *     )
     * )
     */
    public function destroy($author_id)
    {
        $author = Author::findOrFail($author_id);
        $author->delete();

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }
}
