<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

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
 *     description="APIs related to Tags in Biblioteca"
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
     */
    public function index()
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
     *     )
     * )
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
     *     path="/api/tags/{id}",
     *     summary="Get a specific tag by ID",
     *     tags={"Biblioteca/Tags"},
     *     @OA\Parameter(
     *         name="id",
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
     */
    public function show($tag_id)
    {
        $tag = Tag::findOrFail($tag_id);

        return new TagResource($tag);
    }

    /**
     * Update the specified tag in storage.
     *
     * @OA\Put(
     *     path="/api/tags/{id}",
     *     summary="Update an existing tag",
     *     tags={"Biblioteca/Tags"},
     *     @OA\Parameter(
     *         name="id",
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
     */
    public function update(TagRequest $request, $tag_id)
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
     *     path="/api/tags/{id}",
     *     summary="Delete a specific tag",
     *     tags={"Biblioteca/Tags"},
     *     @OA\Parameter(
     *         name="id",
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
     */
    public function destroy($tag_id)
    {
        $tag = Tag::findOrFail($tag_id);
        $tag->delete();

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }
}
