<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use ThreeLeaf\Biblioteca\Http\Controllers\Controller;
use ThreeLeaf\Biblioteca\Http\Requests\AnnotationRequest;
use ThreeLeaf\Biblioteca\Http\Resources\AnnotationResource;
use ThreeLeaf\Biblioteca\Models\Annotation;

/**
 * Controller for {@link Annotation}.
 *
 * @OA\Tag(
 *     name="Biblioteca/Annotations",
 *     description="API Endpoints for managing Annotations in Biblioteca"
 * )
 */
class AnnotationController extends Controller
{
    /**
     * Display a listing of the annotations.
     *
     * @OA\Get(
     *     path="/api/annotations",
     *     summary="Get a list of annotations",
     *     tags={"Biblioteca/Annotations"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/AnnotationResource")
     *         )
     *     )
     * )
     *
     * @return ResourceCollection<AnnotationResource> A collection of annotation resources.
     */
    public function index(): ResourceCollection
    {
        $annotations = Annotation::all();

        return AnnotationResource::collection($annotations);
    }

    /**
     * Store a newly created annotation in storage.
     *
     * @OA\Post(
     *     path="/api/annotations",
     *     summary="Create a new annotation",
     *     tags={"Biblioteca/Annotations"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/AnnotationRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Annotation created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/AnnotationResource")
     *     )
     * )
     *
     * @param AnnotationRequest $request The request object containing the annotation data.
     *
     * @return JsonResponse The created annotation resource.
     */
    public function store(AnnotationRequest $request)
    {
        $validatedData = $request->validated();
        $annotation = Annotation::create($validatedData);

        return (new AnnotationResource($annotation))
            ->response()
            ->setStatusCode(HttpCodes::HTTP_CREATED);
    }

    /**
     * Display the specified annotation.
     *
     * @OA\Get(
     *     path="/api/annotations/{annotation_id}",
     *     summary="Get a specific annotation by ID",
     *     tags={"Biblioteca/Annotations"},
     *     @OA\Parameter(
     *         name="annotation_id",
     *         in="path",
     *         required=true,
     *         description="ID of the annotation",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/AnnotationResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Annotation not found"
     *     )
     * )
     *
     * @param string $annotation_id The unique ID of the annotation to retrieve.
     *
     * @return AnnotationResource The requested annotation resource.
     */
    public function show(string $annotation_id)
    {
        $annotation = Annotation::findOrFail($annotation_id);

        return new AnnotationResource($annotation);
    }

    /**
     * Update the specified annotation in storage.
     *
     * @OA\Put(
     *     path="/api/annotations/{id}",
     *     summary="Update an existing annotation",
     *     tags={"Biblioteca/Annotations"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the annotation",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/AnnotationRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Annotation updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/AnnotationResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Annotation not found"
     *     )
     * )
     *
     * @param AnnotationRequest $request       The request object containing the updated annotation data.
     * @param string            $annotation_id The unique ID of the annotation to update.
     *
     * @return AnnotationResource The updated annotation resource.
     */
    public function update(AnnotationRequest $request, string $annotation_id)
    {
        $annotation = Annotation::findOrFail($annotation_id);
        $validatedData = $request->validated();
        $annotation->update($validatedData);

        return new AnnotationResource($annotation);
    }

    /**
     * Remove the specified annotation from storage.
     *
     * @OA\Delete(
     *     path="/api/annotations/{annotation_id}",
     *     summary="Delete a specific annotation",
     *     tags={"Biblioteca/Annotations"},
     *     @OA\Parameter(
     *         name="annotation_id",
     *         in="path",
     *         required=true,
     *         description="ID of the annotation",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Annotation deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Annotation not found"
     *     )
     * )
     *
     * @param string $annotation_id The unique ID of the annotation to delete.
     *
     * @return JsonResponse A JSON response with a HTTP 204 status code indicating success.
     */
    public function destroy(string $annotation_id)
    {
        $annotation = Annotation::findOrFail($annotation_id);
        $annotation->delete();

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }
}
