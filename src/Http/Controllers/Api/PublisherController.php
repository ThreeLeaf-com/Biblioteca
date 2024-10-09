<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

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
 *     description="APIs related to Publishers in Biblioteca"
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
     */
    public function index()
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
     *     )
     * )
     */
    public function store(PublisherRequest $request)
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
     *     path="/api/publishers/{id}",
     *     summary="Get a specific publisher by ID",
     *     tags={"Biblioteca/Publishers"},
     *     @OA\Parameter(
     *         name="id",
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
     */
    public function show($publisher_id)
    {
        $publisher = Publisher::findOrFail($publisher_id);

        return new PublisherResource($publisher);
    }

    /**
     * Update the specified publisher in storage.
     *
     * @OA\Put(
     *     path="/api/publishers/{id}",
     *     summary="Update an existing publisher",
     *     tags={"Biblioteca/Publishers"},
     *     @OA\Parameter(
     *         name="id",
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
     */
    public function update(PublisherRequest $request, $publisher_id)
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
     *     path="/api/publishers/{id}",
     *     summary="Delete a specific publisher",
     *     tags={"Biblioteca/Publishers"},
     *     @OA\Parameter(
     *         name="id",
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
     */
    public function destroy($publisher_id)
    {
        $publisher = Publisher::findOrFail($publisher_id);
        $publisher->delete();

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }
}
