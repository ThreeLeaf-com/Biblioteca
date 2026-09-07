<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use ThreeLeaf\Biblioteca\Http\Controllers\Controller;
use ThreeLeaf\Biblioteca\Http\Requests\GenreRequest;
use ThreeLeaf\Biblioteca\Http\Resources\GenreResource;
use ThreeLeaf\Biblioteca\Models\Genre;

/**
 * Controller for {@link Genre}.
 */
#[OA\Tag(name: 'Biblioteca/Genres', description: 'API Endpoints for managing Genres in Biblioteca')]
class GenreController extends Controller
{
    /**
     * Display a listing of the genres.
     *
     * @return ResourceCollection<GenreResource> A collection of genre resources.
     */
    #[OA\Get(
        path: '/api/genres',
        summary: 'Get a list of genres',
        tags: ['Biblioteca/Genres'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/GenreResource'),
                ),
            ),
        ],
    )]
    public function index(): ResourceCollection
    {
        $genres = Genre::all();

        return GenreResource::collection($genres);
    }

    /**
     * Store a newly created genre in storage.
     *
     * @param GenreRequest $request The request object containing the genre data.
     *
     * @return JsonResponse The created genre resource.
     */
    #[OA\Post(
        path: '/api/genres',
        summary: 'Create a new genre',
        tags: ['Biblioteca/Genres'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/GenreRequest'),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Genre created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/GenreResource'),
            ),
            new OA\Response(response: 400, description: 'Bad Request'),
        ],
    )]
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
     * @param string $genre_id The unique ID of the genre to retrieve.
     *
     * @return GenreResource The requested genre resource.
     */
    #[OA\Get(
        path: '/api/genres/{genre_id}',
        summary: 'Get a specific genre by ID',
        tags: ['Biblioteca/Genres'],
        parameters: [
            new OA\Parameter(
                name: 'genre_id',
                in: 'path',
                required: true,
                description: 'ID of the genre',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(ref: '#/components/schemas/GenreResource'),
            ),
            new OA\Response(response: 404, description: 'Genre not found'),
        ],
    )]
    public function show(string $genre_id): GenreResource
    {
        $genre = Genre::findOrFail($genre_id);

        return new GenreResource($genre);
    }

    /**
     * Update the specified genre in storage.
     *
     * @param GenreRequest $request  The request object containing the updated genre data.
     * @param string       $genre_id The unique ID of the genre to update.
     *
     * @return GenreResource The updated genre resource.
     */
    #[OA\Put(
        path: '/api/genres/{genre_id}',
        summary: 'Update an existing genre',
        tags: ['Biblioteca/Genres'],
        parameters: [
            new OA\Parameter(
                name: 'genre_id',
                in: 'path',
                required: true,
                description: 'ID of the genre',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/GenreRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Genre updated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/GenreResource'),
            ),
            new OA\Response(response: 404, description: 'Genre not found'),
        ],
    )]
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
     * @param string $genre_id The unique ID of the genre to delete.
     *
     * @return JsonResponse A JSON response with a HTTP 204 status code indicating success.
     */
    #[OA\Delete(
        path: '/api/genres/{genre_id}',
        summary: 'Delete a specific genre',
        tags: ['Biblioteca/Genres'],
        parameters: [
            new OA\Parameter(
                name: 'genre_id',
                in: 'path',
                required: true,
                description: 'ID of the genre',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Genre deleted successfully'),
            new OA\Response(response: 404, description: 'Genre not found'),
        ],
    )]
    public function destroy(string $genre_id): JsonResponse
    {
        $genre = Genre::findOrFail($genre_id);
        $genre->delete();

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }
}
