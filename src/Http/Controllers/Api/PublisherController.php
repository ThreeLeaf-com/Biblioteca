<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use ThreeLeaf\Biblioteca\Http\Controllers\Controller;
use ThreeLeaf\Biblioteca\Http\Requests\PublisherRequest;
use ThreeLeaf\Biblioteca\Http\Resources\PublisherResource;
use ThreeLeaf\Biblioteca\Models\Publisher;

/**
 * Controller for {@link Publisher}.
 *
 * @OA\Tag(
 *     name="Biblioteca/Publishers",
 *     description="API Endpoints for managing publishers"
 * )
 */
class PublisherController extends Controller
{
    /**
     * Display a listing of the publishers.
     *
     * @OA\Get(
     *     path="/api/publishers",
     *     summary="Get a list of publishers",
     *     tags={"Biblioteca/Publishers"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/PublisherResource")
     *         )
     *     )
     * )
     *
     * @return ResourceCollection<PublisherResource> A collection of publisher resources.
     */
    public function index(): ResourceCollection
    {
        $publishers = Publisher::all();

        return PublisherResource::collection($publishers);
    }

    /**
     * Store a newly created publisher in storage.
     *
     * @OA\Post(
     *     path="/api/publishers",
     *     summary="Create a new publisher",
     *     tags={"Biblioteca/Publishers"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PublisherRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Publisher created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/PublisherResource")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     )
     * )
     *
     * @param PublisherRequest $request The request object containing the publisher data.
     *
     * @return JsonResponse The created publisher resource.
     */
    public function store(PublisherRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $publisher = Publisher::create($validatedData);

        return (new PublisherResource($publisher))
            ->response()
            ->setStatusCode(HttpCodes::HTTP_CREATED);
    }

    /**
     * Display the specified publisher.
     *
     * @OA\Get(
     *     path="/api/publishers/{publisher_id}",
     *     summary="Get a specific publisher by ID",
     *     tags={"Biblioteca/Publishers"},
     *     @OA\Parameter(
     *         name="publisher_id",
     *         in="path",
     *         required=true,
     *         description="ID of the publisher",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/PublisherResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Publisher not found"
     *     )
     * )
     *
     * @param string $publisher_id The unique ID of the publisher to retrieve.
     *
     * @return PublisherResource The requested publisher resource.
     */
    public function show(string $publisher_id): PublisherResource
    {
        $publisher = Publisher::findOrFail($publisher_id);

        return new PublisherResource($publisher);
    }

    /**
     * Update the specified publisher in storage.
     *
     * @OA\Put(
     *     path="/api/publishers/{publisher_id}",
     *     summary="Update an existing publisher",
     *     tags={"Biblioteca/Publishers"},
     *     @OA\Parameter(
     *         name="publisher_id",
     *         in="path",
     *         required=true,
     *         description="ID of the publisher",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PublisherRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Publisher updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/PublisherResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Publisher not found"
     *     )
     * )
     *
     * @param PublisherRequest $request      The request object containing the updated publisher data.
     * @param string           $publisher_id The unique ID of the publisher to update.
     *
     * @return PublisherResource A JSON response containing the updated publisher resource.
     */
    public function update(PublisherRequest $request, string $publisher_id): PublisherResource
    {
        $publisher = Publisher::findOrFail($publisher_id);
        $validatedData = $request->validated();
        $publisher->update($validatedData);

        return new PublisherResource($publisher);
    }

    /**
     * Remove the specified publisher from storage.
     *
     * @OA\Delete(
     *     path="/api/publishers/{publisher_id}",
     *     summary="Delete a specific publisher",
     *     tags={"Biblioteca/Publishers"},
     *     @OA\Parameter(
     *         name="publisher_id",
     *         in="path",
     *         required=true,
     *         description="ID of the publisher",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Publisher deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Publisher not found"
     *     )
     * )
     *
     * @param string $publisher_id The unique ID of the publisher to delete.
     *
     * @return JsonResponse A JSON response with a HTTP 204 status code indicating success.
     */
    public function destroy(string $publisher_id): JsonResponse
    {
        $publisher = Publisher::findOrFail($publisher_id);
        $publisher->delete();

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }
}
