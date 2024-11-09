<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use ThreeLeaf\Biblioteca\Http\Controllers\Controller;
use ThreeLeaf\Biblioteca\Http\Requests\GenreRequest;
use ThreeLeaf\Biblioteca\Http\Resources\GenreResource;
use ThreeLeaf\Biblioteca\Models\Genre;

/**
 * Controller for {@link Genre}.
 *
 * @OA\Tag(
 *     name="Biblioteca/Genres",
 *     description="API Endpoints for managing Genres in Biblioteca"
 * )
 */
class GenreController extends Controller
{
    /**
     * Display a listing of the genres.
     *
     * @OA\Get(
     *     path="/api/genres",
     *     summary="Get a list of genres",
     *     tags={"Biblioteca/Genres"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/GenreResource")
     *         )
     *     )
     * )
     *
     * @return ResourceCollection<GenreResource> A collection of genre resources.
     */
    public function index(): ResourceCollection
    {
        $genres = Genre::all();

        return GenreResource::collection($genres);
    }

    /**
     * Store a newly created genre in storage.
     *
     * @OA\Post(
     *     path="/api/genres",
     *     summary="Create a new genre",
     *     tags={"Biblioteca/Genres"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/GenreRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Genre created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/GenreResource")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     )
     * )
     *
     * @param GenreRequest $request The request object containing the genre data.
     *
     * @return JsonResponse The created genre resource.
     */
    public function store(GenreRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $genre = Genre::create($validatedData);

        return (new GenreResource($genre))
            ->response()
            ->setStatusCode(HttpCodes::HTTP_CREATED);
    }

    /**
     * Display the specified genre.
     *
     * @OA\Get(
     *     path="/api/genres/{genre_id}",
     *     summary="Get a specific genre by ID",
     *     tags={"Biblioteca/Genres"},
     *     @OA\Parameter(
     *         name="genre_id",
     *         in="path",
     *         required=true,
     *         description="ID of the genre",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/GenreResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Genre not found"
     *     )
     * )
     *
     * @param string $genre_id The unique ID of the genre to retrieve.
     *
     * @return GenreResource The requested genre resource.
     */
    public function show(string $genre_id): GenreResource
    {
        $genre = Genre::findOrFail($genre_id);

        return new GenreResource($genre);
    }

    /**
     * Update the specified genre in storage.
     *
     * @OA\Put(
     *     path="/api/genres/{genre_id}",
     *     summary="Update an existing genre",
     *     tags={"Biblioteca/Genres"},
     *     @OA\Parameter(
     *         name="genre_id",
     *         in="path",
     *         required=true,
     *         description="ID of the genre",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/GenreRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Genre updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/GenreResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Genre not found"
     *     )
     * )
     *
     * @param GenreRequest $request  The request object containing the updated genre data.
     * @param string       $genre_id The unique ID of the genre to update.
     *
     * @return GenreResource The updated genre resource.
     */
    public function update(GenreRequest $request, string $genre_id): GenreResource
    {
        $genre = Genre::findOrFail($genre_id);
        $validatedData = $request->validated();
        $genre->update($validatedData);

        return new GenreResource($genre);
    }

    /**
     * Remove the specified genre from storage.
     *
     * @OA\Delete(
     *     path="/api/genres/{genre_id}",
     *     summary="Delete a specific genre",
     *     tags={"Biblioteca/Genres"},
     *     @OA\Parameter(
     *         name="genre_id",
     *         in="path",
     *         required=true,
     *         description="ID of the genre",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Genre deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Genre not found"
     *     )
     * )
     *
     * @param string $genre_id The unique ID of the genre to delete.
     *
     * @return JsonResponse A JSON response with a HTTP 204 status code indicating success.
     */
    public function destroy(string $genre_id): JsonResponse
    {
        $genre = Genre::findOrFail($genre_id);
        $genre->delete();

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }
}
