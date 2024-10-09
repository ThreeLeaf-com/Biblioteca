<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

use Symfony\Component\HttpFoundation\Response as HttpCodes;
use ThreeLeaf\Biblioteca\Http\Controllers\Controller;
use ThreeLeaf\Biblioteca\Http\Requests\ParagraphRequest;
use ThreeLeaf\Biblioteca\Http\Resources\ParagraphResource;
use ThreeLeaf\Biblioteca\Models\Paragraph;

/**
 * Controller for {@link Paragraph}.
 *
 * @OA\Tag(
 *     name="Biblioteca/Paragraphs",
 *     description="APIs related to Paragraphs in Biblioteca"
 * )
 */
class ParagraphController extends Controller
{
    /**
     * Display a listing of the paragraphs.
     *
     * @OA\Get(
     *     path="/api/paragraphs",
     *     summary="Get a list of paragraphs",
     *     tags={"Biblioteca/Paragraphs"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ParagraphResource")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $paragraphs = Paragraph::all();

        return ParagraphResource::collection($paragraphs);
    }

    /**
     * Store a newly created paragraph in storage.
     *
     * @OA\Post(
     *     path="/api/paragraphs",
     *     summary="Create a new paragraph",
     *     tags={"Biblioteca/Paragraphs"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ParagraphRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Paragraph created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ParagraphResource")
     *     )
     * )
     */
    public function store(ParagraphRequest $request)
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
     * @OA\Get(
     *     path="/api/paragraphs/{id}",
     *     summary="Get a specific paragraph by ID",
     *     tags={"Biblioteca/Paragraphs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the paragraph",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ParagraphResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Paragraph not found"
     *     )
     * )
     */
    public function show($paragraph_id)
    {
        $paragraph = Paragraph::findOrFail($paragraph_id);

        return new ParagraphResource($paragraph);
    }

    /**
     * Update the specified paragraph in storage.
     *
     * @OA\Put(
     *     path="/api/paragraphs/{id}",
     *     summary="Update an existing paragraph",
     *     tags={"Biblioteca/Paragraphs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the paragraph",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ParagraphRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paragraph updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ParagraphResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Paragraph not found"
     *     )
     * )
     */
    public function update(ParagraphRequest $request, $paragraph_id)
    {
        $paragraph = Paragraph::findOrFail($paragraph_id);
        $validatedData = $request->validated();
        $paragraph->update($validatedData);

        return new ParagraphResource($paragraph);
    }

    /**
     * Remove the specified paragraph from storage.
     *
     * @OA\Delete(
     *     path="/api/paragraphs/{id}",
     *     summary="Delete a specific paragraph",
     *     tags={"Biblioteca/Paragraphs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the paragraph",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Paragraph deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Paragraph not found"
     *     )
     * )
     */
    public function destroy($paragraph_id)
    {
        $paragraph = Paragraph::findOrFail($paragraph_id);
        $paragraph->delete();

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }
}
