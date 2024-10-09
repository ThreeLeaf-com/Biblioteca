<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

use Symfony\Component\HttpFoundation\Response as HttpCodes;
use ThreeLeaf\Biblioteca\Http\Controllers\Controller;
use ThreeLeaf\Biblioteca\Http\Requests\FigureRequest;
use ThreeLeaf\Biblioteca\Http\Resources\FigureResource;
use ThreeLeaf\Biblioteca\Models\Figure;

/**
 * Controller for {@link Figure}.
 *
 * @OA\Tag(
 *      name="Biblioteca/Figures",
 *      description="APIs related to Figures in Biblioteca"
 *  )
 */
class FigureController extends Controller
{
    /**
     * Display a listing of the figures.
     *
     * @OA\Get(
     *     path="/api/figures",
     *     summary="Get a list of figures",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/FigureResource")
     *         )
     *     )
     * )
     */
    public function index()
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/FigureRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Figure created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/FigureResource")
     *     )
     * )
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
     *     path="/api/figures/{id}",
     *     summary="Get a specific figure by ID",
     *     @OA\Parameter(
     *         name="id",
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
     */
    public function show($figure_id)
    {
        $figure = Figure::findOrFail($figure_id);

        return new FigureResource($figure);
    }

    /**
     * Update the specified figure in storage.
     *
     * @OA\Put(
     *     path="/api/figures/{id}",
     *     summary="Update an existing figure",
     *     @OA\Parameter(
     *         name="id",
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
     */
    public function update(FigureRequest $request, $figure_id)
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
     *     path="/api/figures/{id}",
     *     summary="Delete a specific figure",
     *     @OA\Parameter(
     *         name="id",
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
     */
    public function destroy($figure_id)
    {
        $figure = Figure::findOrFail($figure_id);
        $figure->delete();

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }
}
