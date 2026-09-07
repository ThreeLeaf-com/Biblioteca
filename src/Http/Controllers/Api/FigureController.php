<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use ThreeLeaf\Biblioteca\Http\Controllers\Controller;
use ThreeLeaf\Biblioteca\Http\Requests\FigureRequest;
use ThreeLeaf\Biblioteca\Http\Resources\FigureResource;
use ThreeLeaf\Biblioteca\Models\Figure;

/**
 * Controller for {@link Figure}.
 */
#[OA\Tag(name: 'Biblioteca/Figures', description: 'API Endpoints for managing Figures in Biblioteca')]
class FigureController extends Controller
{
    /**
     * Display a listing of the figures.
     *
     * @return ResourceCollection<FigureResource> A collection of figure resources.
     */
    #[OA\Get(
        path: '/api/figures',
        summary: 'Get a list of figures',
        tags: ['Biblioteca/Figures'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/FigureResource'),
                ),
            ),
        ],
    )]
    public function index(): ResourceCollection
    {
        $figures = Figure::all();

        return FigureResource::collection($figures);
    }

    /**
     * Store a newly created figure in storage.
     *
     * @param FigureRequest $request The request object containing the figure data.
     *
     * @return JsonResponse The created figure resource.
     */
    #[OA\Post(
        path: '/api/figures',
        summary: 'Create a new figure',
        tags: ['Biblioteca/Figures'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/FigureRequest'),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Figure created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/FigureResource'),
            ),
            new OA\Response(response: 400, description: 'Bad Request'),
        ],
    )]
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
     * @param string $figure_id The unique ID of the figure to retrieve.
     *
     * @return FigureResource The requested figure resource.
     */
    #[OA\Get(
        path: '/api/figures/{figure_id}',
        summary: 'Get a specific figure by ID',
        tags: ['Biblioteca/Figures'],
        parameters: [
            new OA\Parameter(
                name: 'figure_id',
                in: 'path',
                required: true,
                description: 'ID of the figure',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(ref: '#/components/schemas/FigureResource'),
            ),
            new OA\Response(response: 404, description: 'Figure not found'),
        ],
    )]
    public function show(string $figure_id): FigureResource
    {
        $figure = Figure::findOrFail($figure_id);

        return new FigureResource($figure);
    }

    /**
     * Update the specified figure in storage.
     *
     * @param FigureRequest $request   The request object containing the updated figure data.
     * @param string        $figure_id The unique ID of the figure to update.
     *
     * @return FigureResource The updated figure resource.
     */
    #[OA\Put(
        path: '/api/figures/{figure_id}',
        summary: 'Update an existing figure',
        tags: ['Biblioteca/Figures'],
        parameters: [
            new OA\Parameter(
                name: 'figure_id',
                in: 'path',
                required: true,
                description: 'ID of the figure',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/FigureRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Figure updated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/FigureResource'),
            ),
            new OA\Response(response: 404, description: 'Figure not found'),
        ],
    )]
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
     * @param string $figure_id The unique ID of the figure to delete.
     *
     * @return JsonResponse A JSON response with a HTTP 204 status code indicating success.
     */
    #[OA\Delete(
        path: '/api/figures/{figure_id}',
        summary: 'Delete a specific figure',
        tags: ['Biblioteca/Figures'],
        parameters: [
            new OA\Parameter(
                name: 'figure_id',
                in: 'path',
                required: true,
                description: 'ID of the figure',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Figure deleted successfully'),
            new OA\Response(response: 404, description: 'Figure not found'),
        ],
    )]
    public function destroy(string $figure_id): JsonResponse
    {
        $figure = Figure::findOrFail($figure_id);
        $figure->delete();

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }
}
