<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

use Symfony\Component\HttpFoundation\Response as HttpCodes;
use ThreeLeaf\Biblioteca\Http\Controllers\Controller;
use ThreeLeaf\Biblioteca\Http\Requests\SeriesRequest;
use ThreeLeaf\Biblioteca\Http\Resources\SeriesResource;
use ThreeLeaf\Biblioteca\Models\Series;

/**
 * Controller for {@link Series}.
 *
 * @OA\Tag(
 *      name="Biblioteca/Series",
 *      description="APIs related to Series in Biblioteca"
 *  )
 */
class SeriesController extends Controller
{
    /**
     * Display a listing of the series.
     *
     * @OA\Get(
     *     path="/api/series",
     *     summary="Get a list of series",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/SeriesResource")
     *         )
     *     )
     * )
     */
    public function index()
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SeriesRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Series created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/SeriesResource")
     *     )
     * )
     */
    public function store(SeriesRequest $request)
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
     *     path="/api/series/{id}",
     *     summary="Get a specific series by ID",
     *     @OA\Parameter(
     *         name="id",
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
     */
    public function show($series_id)
    {
        $series = Series::findOrFail($series_id);

        return new SeriesResource($series);
    }

    /**
     * Update the specified series in storage.
     *
     * @OA\Put(
     *     path="/api/series/{id}",
     *     summary="Update an existing series",
     *     @OA\Parameter(
     *         name="id",
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
     */
    public function update(SeriesRequest $request, $series_id)
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
     *     path="/api/series/{id}",
     *     summary="Delete a specific series",
     *     @OA\Parameter(
     *         name="id",
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
     */
    public function destroy($series_id)
    {
        $series = Series::findOrFail($series_id);
        $series->delete();

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }
}
