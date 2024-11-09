<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use ThreeLeaf\Biblioteca\Http\Controllers\Controller;
use ThreeLeaf\Biblioteca\Http\Requests\SentenceRequest;
use ThreeLeaf\Biblioteca\Http\Resources\SentenceResource;
use ThreeLeaf\Biblioteca\Models\Sentence;

/**
 * Controller for {@link Sentence}.
 *
 * @OA\Tag(
 *     name="Biblioteca/Sentences",
 *     description="API Endpoints for managing Sentences in Biblioteca"
 * )
 */
class SentenceController extends Controller
{
    /**
     * Display a listing of the sentences.
     *
     * @OA\Get(
     *     path="/api/sentences",
     *     summary="Get a list of sentences",
     *     tags={"Biblioteca/Sentences"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/SentenceResource")
     *         )
     *     )
     * )
     *
     * @return ResourceCollection<SentenceResource> A collection of sentence resources.
     */
    public function index(): ResourceCollection
    {
        $sentences = Sentence::all();

        return SentenceResource::collection($sentences);
    }

    /**
     * Store a newly created sentence in storage.
     *
     * @OA\Post(
     *     path="/api/sentences",
     *     summary="Create a new sentence",
     *     tags={"Biblioteca/Sentences"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SentenceRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Sentence created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/SentenceResource")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     )
     * )
     *
     * @param SentenceRequest $request The request object containing the sentence data.
     *
     * @return JsonResponse The created sentence resource.
     */
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
     * @OA\Get(
     *     path="/api/sentences/{sentence_id}",
     *     summary="Get a specific sentence by ID",
     *     tags={"Biblioteca/Sentences"},
     *     @OA\Parameter(
     *         name="sentence_id",
     *         in="path",
     *         required=true,
     *         description="ID of the sentence",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SentenceResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sentence not found"
     *     )
     * )
     *
     * @param string $sentence_id The unique ID of the sentence to retrieve.
     *
     * @return SentenceResource The requested sentence resource.
     */
    public function show(string $sentence_id): SentenceResource
    {
        $sentence = Sentence::findOrFail($sentence_id);

        return new SentenceResource($sentence);
    }

    /**
     * Update the specified sentence in storage.
     *
     * @OA\Put(
     *     path="/api/sentences/{sentence_id}",
     *     summary="Update an existing sentence",
     *     tags={"Biblioteca/Sentences"},
     *     @OA\Parameter(
     *         name="sentence_id",
     *         in="path",
     *         required=true,
     *         description="ID of the sentence",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SentenceRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sentence updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/SentenceResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sentence not found"
     *     )
     * )
     *
     * @param SentenceRequest $request     The request object containing the updated sentence data.
     * @param string          $sentence_id The unique ID of the sentence to update.
     *
     * @return SentenceResource The updated sentence resource.
     */
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
     * @OA\Delete(
     *     path="/api/sentences/{sentence_id}",
     *     summary="Delete a specific sentence",
     *     tags={"Biblioteca/Sentences"},
     *     @OA\Parameter(
     *         name="sentence_id",
     *         in="path",
     *         required=true,
     *         description="ID of the sentence",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Sentence deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sentence not found"
     *     )
     * )
     *
     * @param string $sentence_id The unique ID of the sentence to delete.
     *
     * @return JsonResponse A JSON response with a HTTP 204 status code indicating success.
     */
    public function destroy(string $sentence_id): JsonResponse
    {
        $sentence = Sentence::findOrFail($sentence_id);
        $sentence->delete();

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }
}
