<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use ThreeLeaf\Biblioteca\Http\Controllers\Controller;
use ThreeLeaf\Biblioteca\Http\Requests\FigureRequest;
use ThreeLeaf\Biblioteca\Http\Resources\FigureResource;
use ThreeLeaf\Biblioteca\Models\Figure;

/**
 * Controller for {@link Figure}.
 *
 * @OA\Tag(
 *     name="Biblioteca/Figures",
 *     description="API Endpoints for managing Figures in Biblioteca"
 * )
 */
class FigureController extends Controller
{
    /**
     * Display a listing of the figures.
     *
     * @OA\Get(
     *     path="/api/figures",
     *     summary="Get a list of figures",
     *     tags={"Biblioteca/Figures"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/FigureResource")
     *         )
     *     )
     * )
     *
     * @return ResourceCollection<FigureResource> A collection of figure resources.
     */
    public function index(): ResourceCollection
    {
        $figures = Figure::all();

        return FigureResource::collection($figures);
    }

    /**
     * Store a newly created figure in storage.
     *
     * @OA\Post(
     *     path="/api/figures",
     *     summary="Create a new figure",
     *     tags={"Biblioteca/Figures"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/FigureRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Figure created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/FigureResource")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     )
     * )
     *
     * @param FigureRequest $request The request object containing the figure data.
     *
     * @return JsonResponse The created figure resource.
     */
    public function store(FigureRequest $request)
    {
        $validatedData = $request->validated();
        $figure = Figure::create($validatedData);

        return (new FigureResource($figure))
            ->response()
            ->setStatusCode(HttpCodes::HTTP_CREATED);
    }

    /**
     * Display the specified figure.
     *
     * @OA\Get(
     *     path="/api/figures/{figure_id}",
     *     summary="Get a specific figure by ID",
     *     tags={"Biblioteca/Figures"},
     *     @OA\Parameter(
     *         name="figure_id",
     *         in="path",
     *         required=true,
     *         description="ID of the figure",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/FigureResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Figure not found"
     *     )
     * )
     *
     * @param string $figure_id The unique ID of the figure to retrieve.
     *
     * @return FigureResource The requested figure resource.
     */
    public function show(string $figure_id): FigureResource
    {
        $figure = Figure::findOrFail($figure_id);

        return new FigureResource($figure);
    }

    /**
     * Update the specified figure in storage.
     *
     * @OA\Put(
     *     path="/api/figures/{figure_id}",
     *     summary="Update an existing figure",
     *     tags={"Biblioteca/Figures"},
     *     @OA\Parameter(
     *         name="figure_id",
     *         in="path",
     *         required=true,
     *         description="ID of the figure",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/FigureRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Figure updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/FigureResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Figure not found"
     *     )
     * )
     *
     * @param FigureRequest $request   The request object containing the updated figure data.
     * @param string        $figure_id The unique ID of the figure to update.
     *
     * @return FigureResource The updated figure resource.
     */
    public function update(FigureRequest $request, string $figure_id): FigureResource
    {
        $figure = Figure::findOrFail($figure_id);
        $validatedData = $request->validated();
        $figure->update($validatedData);

        return new FigureResource($figure);
    }

    /**
     * Remove the specified figure from storage.
     *
     * @OA\Delete(
     *     path="/api/figures/{figure_id}",
     *     summary="Delete a specific figure",
     *     tags={"Biblioteca/Figures"},
     *     @OA\Parameter(
     *         name="figure_id",
     *         in="path",
     *         required=true,
     *         description="ID of the figure",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Figure deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Figure not found"
     *     )
     * )
     *
     * @param string $figure_id The unique ID of the figure to delete.
     *
     * @return JsonResponse A JSON response with a HTTP 204 status code indicating success.
     */
    public function destroy(string $figure_id): JsonResponse
    {
        $figure = Figure::findOrFail($figure_id);
        $figure->delete();

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }
}
