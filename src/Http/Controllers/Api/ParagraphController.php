<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use ThreeLeaf\Biblioteca\Http\Controllers\Controller;
use ThreeLeaf\Biblioteca\Http\Requests\ParagraphRequest;
use ThreeLeaf\Biblioteca\Http\Resources\ParagraphResource;
use ThreeLeaf\Biblioteca\Models\Paragraph;

/**
 * Controller for {@link Paragraph}.
 */
#[OA\Tag(name: 'Biblioteca/Paragraphs', description: 'API Endpoints for managing Paragraphs in Biblioteca')]
class ParagraphController extends Controller
{
    /**
     * Display a listing of the paragraphs.
     *
     * @return ResourceCollection<ParagraphResource> A collection of paragraph resources.
     */
    #[OA\Get(
        path: '/api/paragraphs',
        summary: 'Get a list of paragraphs',
        tags: ['Biblioteca/Paragraphs'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/ParagraphResource'),
                ),
            ),
        ],
    )]
    public function index(): ResourceCollection
    {
        $paragraphs = Paragraph::all();

        return ParagraphResource::collection($paragraphs);
    }

    /**
     * Store a newly created paragraph in storage.
     *
     * @param ParagraphRequest $request The request object containing the paragraph data.
     *
     * @return JsonResponse The created paragraph resource.
     */
    #[OA\Post(
        path: '/api/paragraphs',
        summary: 'Create a new paragraph',
        tags: ['Biblioteca/Paragraphs'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ParagraphRequest'),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Paragraph created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/ParagraphResource'),
            ),
            new OA\Response(response: 400, description: 'Bad Request'),
        ],
    )]
    public function store(ParagraphRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $paragraph = Paragraph::create($validatedData);

        return (new ParagraphResource($paragraph))
            ->response()
            ->setStatusCode(HttpCodes::HTTP_CREATED);
    }

    /**
     * Display the specified paragraph.
     *
     * @param string $paragraph_id The unique ID of the paragraph to retrieve.
     *
     * @return ParagraphResource The requested paragraph resource.
     */
    #[OA\Get(
        path: '/api/paragraphs/{paragraph_id}',
        summary: 'Get a specific paragraph by ID',
        tags: ['Biblioteca/Paragraphs'],
        parameters: [
            new OA\Parameter(
                name: 'paragraph_id',
                in: 'path',
                required: true,
                description: 'ID of the paragraph',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(ref: '#/components/schemas/ParagraphResource'),
            ),
            new OA\Response(response: 404, description: 'Paragraph not found'),
        ],
    )]
    public function show(string $paragraph_id): ParagraphResource
    {
        $paragraph = Paragraph::findOrFail($paragraph_id);

        return new ParagraphResource($paragraph);
    }

    /**
     * Update the specified paragraph in storage.
     *
     * @param ParagraphRequest $request      The request object containing the updated paragraph data.
     * @param string           $paragraph_id The unique ID of the paragraph to update.
     *
     * @return ParagraphResource The updated paragraph resource.
     */
    #[OA\Put(
        path: '/api/paragraphs/{paragraph_id}',
        summary: 'Update an existing paragraph',
        tags: ['Biblioteca/Paragraphs'],
        parameters: [
            new OA\Parameter(
                name: 'paragraph_id',
                in: 'path',
                required: true,
                description: 'ID of the paragraph',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ParagraphRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paragraph updated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/ParagraphResource'),
            ),
            new OA\Response(response: 404, description: 'Paragraph not found'),
        ],
    )]
    public function update(ParagraphRequest $request, string $paragraph_id): ParagraphResource
    {
        $paragraph = Paragraph::findOrFail($paragraph_id);
        $validatedData = $request->validated();
        $paragraph->update($validatedData);

        return new ParagraphResource($paragraph);
    }

    /**
     * Remove the specified paragraph from storage.
     *
     * @param string $paragraph_id The unique ID of the paragraph to delete.
     *
     * @return JsonResponse A JSON response with a HTTP 204 status code indicating success.
     */
    #[OA\Delete(
        path: '/api/paragraphs/{paragraph_id}',
        summary: 'Delete a specific paragraph',
        tags: ['Biblioteca/Paragraphs'],
        parameters: [
            new OA\Parameter(
                name: 'paragraph_id',
                in: 'path',
                required: true,
                description: 'ID of the paragraph',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Paragraph deleted successfully'),
            new OA\Response(response: 404, description: 'Paragraph not found'),
        ],
    )]
    public function destroy(string $paragraph_id): JsonResponse
    {
        $paragraph = Paragraph::findOrFail($paragraph_id);
        $paragraph->delete();

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }
}
