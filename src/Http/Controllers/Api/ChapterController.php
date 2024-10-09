<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

use Symfony\Component\HttpFoundation\Response as HttpCodes;
use ThreeLeaf\Biblioteca\Http\Controllers\Controller;
use ThreeLeaf\Biblioteca\Http\Requests\ChapterRequest;
use ThreeLeaf\Biblioteca\Http\Resources\ChapterResource;
use ThreeLeaf\Biblioteca\Models\Chapter;

/**
 * Controller for {@link Chapter}.
 *
 * @OA\Tag(
 *     name="Biblioteca/Chapters",
 *     description="APIs related to Chapters in Biblioteca"
 * )
 */
class ChapterController extends Controller
{
    /**
     * Display a listing of the chapters.
     *
     * @OA\Get(
     *     path="/api/chapters",
     *     summary="Get a list of chapters",
     *     tags={"Biblioteca/Chapters"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ChapterResource")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $chapters = Chapter::all();

        return ChapterResource::collection($chapters);
    }

    /**
     * Store a newly created chapter in storage.
     *
     * @OA\Post(
     *     path="/api/chapters",
     *     summary="Create a new chapter",
     *     tags={"Biblioteca/Chapters"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ChapterRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Chapter created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ChapterResource")
     *     )
     * )
     */
    public function store(ChapterRequest $request)
    {
        $validatedData = $request->validated();
        $chapter = Chapter::create($validatedData);

        return (new ChapterResource($chapter))
            ->response()
            ->setStatusCode(HttpCodes::HTTP_CREATED);
    }

    /**
     * Display the specified chapter.
     *
     * @OA\Get(
     *     path="/api/chapters/{id}",
     *     summary="Get a specific chapter by ID",
     *     tags={"Biblioteca/Chapters"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the chapter",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ChapterResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Chapter not found"
     *     )
     * )
     */
    public function show($chapter_id)
    {
        $chapter = Chapter::findOrFail($chapter_id);

        return new ChapterResource($chapter);
    }

    /**
     * Update the specified chapter in storage.
     *
     * @OA\Put(
     *     path="/api/chapters/{id}",
     *     summary="Update an existing chapter",
     *     tags={"Biblioteca/Chapters"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the chapter",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ChapterRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Chapter updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ChapterResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Chapter not found"
     *     )
     * )
     */
    public function update(ChapterRequest $request, $chapter_id)
    {
        $chapter = Chapter::findOrFail($chapter_id);
        $validatedData = $request->validated();
        $chapter->update($validatedData);

        return new ChapterResource($chapter);
    }

    /**
     * Remove the specified chapter from storage.
     *
     * @OA\Delete(
     *     path="/api/chapters/{id}",
     *     summary="Delete a specific chapter",
     *     tags={"Biblioteca/Chapters"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the chapter",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Chapter deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Chapter not found"
     *     )
     * )
     */
    public function destroy($chapter_id)
    {
        $chapter = Chapter::findOrFail($chapter_id);
        $chapter->delete();

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }
}
