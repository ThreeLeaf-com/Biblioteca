<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use ThreeLeaf\Biblioteca\Http\Controllers\Controller;
use ThreeLeaf\Biblioteca\Http\Requests\SeriesRequest;
use ThreeLeaf\Biblioteca\Http\Resources\SeriesResource;
use ThreeLeaf\Biblioteca\Models\Series;

/**
 * Controller for {@link Series}.
 *
 * @OA\Tag(
 *     name="Biblioteca/Series",
 *     description="API Endpoints for managing Series in Biblioteca"
 * )
 */
class SeriesController extends Controller
{
    /**
     * Display a listing of the series.
     *
     * @OA\Get(
     *     path="/api/series",
     *     summary="Get a list of series",
     *     tags={"Biblioteca/Series"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/SeriesResource")
     *         )
     *     )
     * )
     *
     * @return ResourceCollection<SeriesResource> A collection of series resources.
     */
    public function index(): ResourceCollection
    {
        $series = Series::all();

        return SeriesResource::collection($series);
    }

    /**
     * Store a newly created series in storage.
     *
     * @OA\Post(
     *     path="/api/series",
     *     summary="Create a new series",
     *     tags={"Biblioteca/Series"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SeriesRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Series created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/SeriesResource")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     )
     * )
     *
     * @param SeriesRequest $request The request object containing the series data.
     *
     * @return JsonResponse The created series resource.
     */
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
     * @OA\Get(
     *     path="/api/series/{series_id}",
     *     summary="Get a specific series by ID",
     *     tags={"Biblioteca/Series"},
     *     @OA\Parameter(
     *         name="series_id",
     *         in="path",
     *         required=true,
     *         description="ID of the series",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SeriesResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Series not found"
     *     )
     * )
     *
     * @param string $series_id The unique ID of the series to retrieve.
     *
     * @return SeriesResource The requested series resource.
     */
    public function show(string $series_id): SeriesResource
    {
        $series = Series::findOrFail($series_id);

        return new SeriesResource($series);
    }

    /**
     * Update the specified series in storage.
     *
     * @OA\Put(
     *     path="/api/series/{series_id}",
     *     summary="Update an existing series",
     *     tags={"Biblioteca/Series"},
     *     @OA\Parameter(
     *         name="series_id",
     *         in="path",
     *         required=true,
     *         description="ID of the series",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SeriesRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Series updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/SeriesResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Series not found"
     *     )
     * )
     *
     * @param SeriesRequest $request   The request object containing the updated series data.
     * @param string        $series_id The unique ID of the series to update.
     *
     * @return SeriesResource The updated series resource.
     */
    public function update(SeriesRequest $request, string $series_id): SeriesResource
    {
        $series = Series::findOrFail($series_id);
        $validatedData = $request->validated();
        $series->update($validatedData);

        return new SeriesResource($series);
    }

    /**
     * Remove the specified series from storage.
     *
     * @OA\Delete(
     *     path="/api/series/{series_id}",
     *     summary="Delete a specific series",
     *     tags={"Biblioteca/Series"},
     *     @OA\Parameter(
     *         name="series_id",
     *         in="path",
     *         required=true,
     *         description="ID of the series",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Series deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Series not found"
     *     )
     * )
     *
     * @param string $series_id The unique ID of the series to delete.
     *
     * @return JsonResponse A JSON response with a HTTP 204 status code indicating success.
     */
    public function destroy(string $series_id): JsonResponse
    {
        $series = Series::findOrFail($series_id);
        $series->delete();

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }
}
