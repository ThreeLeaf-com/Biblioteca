<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

use Symfony\Component\HttpFoundation\Response as HttpCodes;
use ThreeLeaf\Biblioteca\Http\Controllers\Controller;
use ThreeLeaf\Biblioteca\Http\Requests\GenreRequest;
use ThreeLeaf\Biblioteca\Http\Resources\GenreResource;
use ThreeLeaf\Biblioteca\Models\Genre;

/**
 * Controller for {@link Genre}.
 *
 * @OA\Tag(
 *      name="Biblioteca/Genres",
 *      description="APIs related to Genres in Biblioteca"
 *  )
 */
class GenreController extends Controller
{
    /**
     * Display a listing of the genres.
     *
     * @OA\Get(
     *     path="/api/genres",
     *     summary="Get a list of genres",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/GenreResource")
     *         )
     *     )
     * )
     */
    public function index()
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/GenreRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Genre created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/GenreResource")
     *     )
     * )
     */
    public function store(GenreRequest $request)
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
     *     path="/api/genres/{id}",
     *     summary="Get a specific genre by ID",
     *     @OA\Parameter(
     *         name="id",
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
     */
    public function show($genre_id)
    {
        $genre = Genre::findOrFail($genre_id);

        return new GenreResource($genre);
    }

    /**
     * Update the specified genre in storage.
     *
     * @OA\Put(
     *     path="/api/genres/{id}",
     *     summary="Update an existing genre",
     *     @OA\Parameter(
     *         name="id",
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
     */
    public function update(GenreRequest $request, $genre_id)
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
     *     path="/api/genres/{id}",
     *     summary="Delete a specific genre",
     *     @OA\Parameter(
     *         name="id",
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
     */
    public function destroy($genre_id)
    {
        $genre = Genre::findOrFail($genre_id);
        $genre->delete();

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }
}
