<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use ThreeLeaf\Biblioteca\Http\Controllers\Controller;
use ThreeLeaf\Biblioteca\Http\Requests\SeriesRequest;
use ThreeLeaf\Biblioteca\Http\Resources\SeriesResource;
use ThreeLeaf\Biblioteca\Models\Series;
use ThreeLeaf\Biblioteca\Services\SeriesService;

/**
 * Controller for {@link Series}.
 */
#[OA\Tag(name: 'Biblioteca/Series', description: 'API Endpoints for managing Series in Biblioteca')]
class SeriesController extends Controller
{
    public function __construct(
        private readonly SeriesService $seriesService,
    )
    {
    }

    /**
     * Display a listing of the series.
     *
     * @return ResourceCollection<SeriesResource> A collection of series resources.
     */
    #[OA\Get(
        path: '/api/series',
        summary: 'Get a list of series',
        tags: ['Biblioteca/Series'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/SeriesResource'),
                ),
            ),
        ],
    )]
    public function index(): ResourceCollection
    {
        $series = Series::with(['author', 'books'])->get();

        return SeriesResource::collection($series);
    }

    /**
     * Store a newly created series in storage.
     *
     * @param SeriesRequest $request The request object containing the series data.
     *
     * @return JsonResponse The created series resource.
     */
    #[OA\Post(
        path: '/api/series',
        summary: 'Create a new series',
        tags: ['Biblioteca/Series'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/SeriesRequest'),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Series created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/SeriesResource'),
            ),
            new OA\Response(response: 400, description: 'Bad Request'),
        ],
    )]
    public function store(SeriesRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $series = Series::create($validatedData);

        return (new SeriesResource($series))
            ->response()
            ->setStatusCode(HttpCodes::HTTP_CREATED);
    }

    /**
     * Display the specified series.
     *
     * @param string $series_id The unique ID of the series to retrieve.
     *
     * @return SeriesResource The requested series resource.
     */
    #[OA\Get(
        path: '/api/series/{series_id}',
        summary: 'Get a specific series by ID',
        tags: ['Biblioteca/Series'],
        parameters: [
            new OA\Parameter(
                name: 'series_id',
                in: 'path',
                required: true,
                description: 'ID of the series',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(ref: '#/components/schemas/SeriesResource'),
            ),
            new OA\Response(response: 404, description: 'Series not found'),
        ],
    )]
    public function show(string $series_id): SeriesResource
    {
        $series = Series::with(['author', 'books'])->findOrFail($series_id);

        return new SeriesResource($series);
    }

    /**
     * Update the specified series in storage.
     *
     * @param SeriesRequest $request   The request object containing the updated series data.
     * @param string        $series_id The unique ID of the series to update.
     *
     * @return SeriesResource The updated series resource.
     */
    #[OA\Put(
        path: '/api/series/{series_id}',
        summary: 'Update an existing series',
        tags: ['Biblioteca/Series'],
        parameters: [
            new OA\Parameter(
                name: 'series_id',
                in: 'path',
                required: true,
                description: 'ID of the series',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/SeriesRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Series updated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/SeriesResource'),
            ),
            new OA\Response(response: 404, description: 'Series not found'),
        ],
    )]
    public function update(SeriesRequest $request, string $series_id): SeriesResource
    {
        $series = Series::findOrFail($series_id);
        $validatedData = $request->validated();
        $series = $this->seriesService->update($series, $validatedData);

        return new SeriesResource($series);
    }

    /**
     * Remove the specified series from storage.
     *
     * @param string $series_id The unique ID of the series to delete.
     *
     * @return JsonResponse A JSON response with a HTTP 204 status code indicating success.
     */
    #[OA\Delete(
        path: '/api/series/{series_id}',
        summary: 'Delete a specific series',
        tags: ['Biblioteca/Series'],
        parameters: [
            new OA\Parameter(
                name: 'series_id',
                in: 'path',
                required: true,
                description: 'ID of the series',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Series deleted successfully'),
            new OA\Response(response: 404, description: 'Series not found'),
        ],
    )]
    public function destroy(string $series_id): JsonResponse
    {
        $series = Series::findOrFail($series_id);
        $series->delete();

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }
}
