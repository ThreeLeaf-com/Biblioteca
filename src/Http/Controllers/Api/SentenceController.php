<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use ThreeLeaf\Biblioteca\Http\Controllers\Controller;
use ThreeLeaf\Biblioteca\Http\Requests\SentenceRequest;
use ThreeLeaf\Biblioteca\Http\Resources\SentenceResource;
use ThreeLeaf\Biblioteca\Models\Sentence;

/**
 * Controller for {@link Sentence}.
 */
#[OA\Tag(name: 'Biblioteca/Sentences', description: 'API Endpoints for managing Sentences in Biblioteca')]
class SentenceController extends Controller
{
    /**
     * Display a listing of the sentences.
     *
     * @return ResourceCollection<SentenceResource> A collection of sentence resources.
     */
    #[OA\Get(
        path: '/api/sentences',
        summary: 'Get a list of sentences',
        tags: ['Biblioteca/Sentences'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/SentenceResource'),
                ),
            ),
        ],
    )]
    public function index(): ResourceCollection
    {
        $sentences = Sentence::all();

        return SentenceResource::collection($sentences);
    }

    /**
     * Store a newly created sentence in storage.
     *
     * @param SentenceRequest $request The request object containing the sentence data.
     *
     * @return JsonResponse The created sentence resource.
     */
    #[OA\Post(
        path: '/api/sentences',
        summary: 'Create a new sentence',
        tags: ['Biblioteca/Sentences'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/SentenceRequest'),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Sentence created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/SentenceResource'),
            ),
            new OA\Response(response: 400, description: 'Bad Request'),
        ],
    )]
    public function store(SentenceRequest $request)
    {
        $validatedData = $request->validated();
        $sentence = Sentence::create($validatedData);

        return (new SentenceResource($sentence))
            ->response()
            ->setStatusCode(HttpCodes::HTTP_CREATED);
    }

    /**
     * Display the specified sentence.
     *
     * @param string $sentence_id The unique ID of the sentence to retrieve.
     *
     * @return SentenceResource The requested sentence resource.
     */
    #[OA\Get(
        path: '/api/sentences/{sentence_id}',
        summary: 'Get a specific sentence by ID',
        tags: ['Biblioteca/Sentences'],
        parameters: [
            new OA\Parameter(
                name: 'sentence_id',
                in: 'path',
                required: true,
                description: 'ID of the sentence',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(ref: '#/components/schemas/SentenceResource'),
            ),
            new OA\Response(response: 404, description: 'Sentence not found'),
        ],
    )]
    public function show(string $sentence_id): SentenceResource
    {
        $sentence = Sentence::findOrFail($sentence_id);

        return new SentenceResource($sentence);
    }

    /**
     * Update the specified sentence in storage.
     *
     * @param SentenceRequest $request     The request object containing the updated sentence data.
     * @param string          $sentence_id The unique ID of the sentence to update.
     *
     * @return SentenceResource The updated sentence resource.
     */
    #[OA\Put(
        path: '/api/sentences/{sentence_id}',
        summary: 'Update an existing sentence',
        tags: ['Biblioteca/Sentences'],
        parameters: [
            new OA\Parameter(
                name: 'sentence_id',
                in: 'path',
                required: true,
                description: 'ID of the sentence',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/SentenceRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sentence updated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/SentenceResource'),
            ),
            new OA\Response(response: 404, description: 'Sentence not found'),
        ],
    )]
    public function update(SentenceRequest $request, string $sentence_id): SentenceResource
    {
        $sentence = Sentence::findOrFail($sentence_id);
        $validatedData = $request->validated();
        $sentence->update($validatedData);

        return new SentenceResource($sentence);
    }

    /**
     * Remove the specified sentence from storage.
     *
     * @param string $sentence_id The unique ID of the sentence to delete.
     *
     * @return JsonResponse A JSON response with a HTTP 204 status code indicating success.
     */
    #[OA\Delete(
        path: '/api/sentences/{sentence_id}',
        summary: 'Delete a specific sentence',
        tags: ['Biblioteca/Sentences'],
        parameters: [
            new OA\Parameter(
                name: 'sentence_id',
                in: 'path',
                required: true,
                description: 'ID of the sentence',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Sentence deleted successfully'),
            new OA\Response(response: 404, description: 'Sentence not found'),
        ],
    )]
    public function destroy(string $sentence_id): JsonResponse
    {
        $sentence = Sentence::findOrFail($sentence_id);
        $sentence->delete();

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }
}
