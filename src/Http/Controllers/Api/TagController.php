<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use ThreeLeaf\Biblioteca\Http\Controllers\Controller;
use ThreeLeaf\Biblioteca\Http\Requests\TagRequest;
use ThreeLeaf\Biblioteca\Http\Resources\TagResource;
use ThreeLeaf\Biblioteca\Models\Tag;

/**
 * Controller for {@link Tag}.
 *
 * @OA\Tag(
 *     name="Biblioteca/Tags",
 *     description="API Endpoints for managing Tags in Biblioteca"
 * )
 */
class TagController extends Controller
{
    /**
     * Display a listing of the tags.
     *
     * @OA\Get(
     *     path="/api/tags",
     *     summary="Get a list of tags",
     *     tags={"Biblioteca/Tags"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/TagResource")
     *         )
     *     )
     * )
     *
     * @return ResourceCollection<TagResource> A collection of tag resources.
     */
    public function index(): ResourceCollection
    {
        $tags = Tag::all();

        return TagResource::collection($tags);
    }

    /**
     * Store a newly created tag in storage.
     *
     * @OA\Post(
     *     path="/api/tags",
     *     summary="Create a new tag",
     *     tags={"Biblioteca/Tags"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TagRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tag created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/TagResource")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     )
     * )
     *
     * @param TagRequest $request The request object containing the tag data.
     *
     * @return JsonResponse The created tag resource.
     */
    public function store(TagRequest $request)
    {
        $validatedData = $request->validated();
        $tag = Tag::create($validatedData);

        return (new TagResource($tag))
            ->response()
            ->setStatusCode(HttpCodes::HTTP_CREATED);
    }

    /**
     * Display the specified tag.
     *
     * @OA\Get(
     *     path="/api/tags/{tag_id}",
     *     summary="Get a specific tag by ID",
     *     tags={"Biblioteca/Tags"},
     *     @OA\Parameter(
     *         name="tag_id",
     *         in="path",
     *         required=true,
     *         description="ID of the tag",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TagResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found"
     *     )
     * )
     *
     * @param string $tag_id The unique ID of the tag to retrieve.
     *
     * @return TagResource The requested tag resource.
     */
    public function show(string $tag_id): TagResource
    {
        $tag = Tag::findOrFail($tag_id);

        return new TagResource($tag);
    }

    /**
     * Update the specified tag in storage.
     *
     * @OA\Put(
     *     path="/api/tags/{tag_id}",
     *     summary="Update an existing tag",
     *     tags={"Biblioteca/Tags"},
     *     @OA\Parameter(
     *         name="tag_id",
     *         in="path",
     *         required=true,
     *         description="ID of the tag",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TagRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/TagResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found"
     *     )
     * )
     *
     * @param TagRequest $request The request object containing the updated tag data.
     * @param string     $tag_id  The unique ID of the tag to update.
     *
     * @return TagResource The updated tag resource.
     */
    public function update(TagRequest $request, string $tag_id): TagResource
    {
        $tag = Tag::findOrFail($tag_id);
        $validatedData = $request->validated();
        $tag->update($validatedData);

        return new TagResource($tag);
    }

    /**
     * Remove the specified tag from storage.
     *
     * @OA\Delete(
     *     path="/api/tags/{tag_id}",
     *     summary="Delete a specific tag",
     *     tags={"Biblioteca/Tags"},
     *     @OA\Parameter(
     *         name="tag_id",
     *         in="path",
     *         required=true,
     *         description="ID of the tag",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Tag deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found"
     *     )
     * )
     *
     * @param string $tag_id The unique ID of the tag to delete.
     *
     * @return JsonResponse A JSON response with a HTTP 204 status code indicating success.
     */
    public function destroy(string $tag_id): JsonResponse
    {
        $tag = Tag::findOrFail($tag_id);
        $tag->delete();

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }
}
