<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use ThreeLeaf\Biblioteca\Http\Controllers\Controller;
use ThreeLeaf\Biblioteca\Http\Requests\ChapterRequest;
use ThreeLeaf\Biblioteca\Http\Resources\ChapterResource;
use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Services\ChapterService;

/**
 * Controller for {@link Chapter}.
 *
 * @OA\Tag(
 *     name="Biblioteca/Chapters",
 *     description="API Endpoints for managing Chapters in Biblioteca"
 * )
 */
class ChapterController extends Controller
{

    public function __construct(
        private readonly ChapterService $chapterService,
    )
    {
    }

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
     * @return ResourceCollection<ChapterResource> The {@link ChapterResource}
     */
    public function index(): ResourceCollection
    {
        $chapters = $this->chapterService->getAll();

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
     *
     * @param ChapterRequest $request The chapter request
     *
     * @return JsonResponse The {@link ChapterResource}
     */
    public function store(ChapterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $chapter = $this->chapterService->create($data);

        return (new ChapterResource($chapter))
            ->response()
            ->setStatusCode(HttpCodes::HTTP_CREATED);
    }

    /**
     * Display the specified chapter.
     *
     * @OA\Get(
     *     path="/api/chapters/{chapter_id}",
     *     summary="Get a specific chapter by ID",
     *     tags={"Biblioteca/Chapters"},
     *     @OA\Parameter(
     *         name="chapter_id",
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
     *
     * @param string $chapter_id The chapter ID.
     *
     * @return JsonResource The {@link ChapterResource}
     */
    public function show(string $chapter_id): JsonResource
    {
        $chapter = Chapter::findOrFail($chapter_id);

        return new ChapterResource($chapter);
    }

    /**
     * Update an existing chapter in storage.
     *
     * @OA\Put(
     *     path="/api/chapters/{chapter_id}",
     *     summary="Update an existing chapter",
     *     tags={"Biblioteca/Chapters"},
     *     @OA\Parameter(
     *         name="chapter_id",
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
     * @param ChapterRequest $request    The validated request containing the updated chapter data.
     * @param string         $chapter_id The chapter ID.
     *
     * @return JsonResource The {@link ChapterResource}
     */
    public function update(ChapterRequest $request, string $chapter_id): JsonResource
    {
        $chapter = Chapter::findOrFail($chapter_id);
        $data = $request->validated();
        $chapter = $this->chapterService->update($chapter, $data);

        return new ChapterResource($chapter);
    }

    /**
     * Remove the specified chapter from storage.
     *
     * @OA\Delete(
     *     path="/api/chapters/{chapter_id}",
     *     summary="Delete a specific chapter",
     *     tags={"Biblioteca/Chapters"},
     *     @OA\Parameter(
     *         name="chapter_id",
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
     *
     * @param string $chapter_id The chapter ID.
     *
     * @return JsonResponse A JSON response with HTTP status code 204 (No Content) indicating successful deletion.
     */
    public function destroy(string $chapter_id): JsonResponse
    {
        $chapter = $this->chapterService->findOrFail($chapter_id);
        $this->chapterService->delete($chapter);

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }
}
