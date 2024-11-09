<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
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
 *     description="API Endpoints for managing Authors in Biblioteca"
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
     *
     * @return ResourceCollection<AuthorResource> A collection of author resources.
     */
    public function index(): ResourceCollection
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
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     )
     * )
     *
     * @param AuthorRequest $request The request object containing the author data.
     *
     * @return JsonResponse The created author resource.
     */
    public function store(AuthorRequest $request): JsonResponse
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
     *     path="/api/authors/{author_id}",
     *     summary="Get a specific author by ID",
     *     tags={"Biblioteca/Authors"},
     *     @OA\Parameter(
     *         name="author_id",
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
     *
     * @param string $author_id The unique ID of the author to retrieve.
     *
     * @return AuthorResource The requested author resource.
     */
    public function show(string $author_id): AuthorResource
    {
        $author = Author::findOrFail($author_id);

        return new AuthorResource($author);
    }

    /**
     * Update the specified author in storage.
     *
     * @OA\Put(
     *     path="/api/authors/{author_id}",
     *     summary="Update an existing author",
     *     tags={"Biblioteca/Authors"},
     *     @OA\Parameter(
     *         name="author_id",
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
     *
     * @param AuthorRequest $request   The request object containing the updated author data.
     * @param string        $author_id The unique ID of the author to update.
     *
     * @return AuthorResource The updated author resource.
     */
    public function update(AuthorRequest $request, string $author_id): AuthorResource
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
     *     path="/api/authors/{author_id}",
     *     summary="Delete a specific author",
     *     tags={"Biblioteca/Authors"},
     *     @OA\Parameter(
     *         name="author_id",
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
     *
     * @param string $author_id The unique ID of the author to delete.
     *
     * @return JsonResponse A JSON response with a HTTP 204 status code indicating success.
     */
    public function destroy(string $author_id): JsonResponse
    {
        $author = Author::findOrFail($author_id);
        $author->delete();

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }
}
