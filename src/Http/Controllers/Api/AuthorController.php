<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use ThreeLeaf\Biblioteca\Http\Controllers\Controller;
use ThreeLeaf\Biblioteca\Http\Requests\AuthorRequest;
use ThreeLeaf\Biblioteca\Http\Resources\AuthorResource;
use ThreeLeaf\Biblioteca\Models\Author;

/**
 * Controller for {@link Author}.
 */
#[OA\Tag(name: 'Biblioteca/Authors', description: 'API Endpoints for managing Authors in Biblioteca')]
class AuthorController extends Controller
{
    /**
     * Display a listing of the authors.
     *
     * @return ResourceCollection<AuthorResource> A collection of author resources.
     */
    #[OA\Get(
        path: '/api/authors',
        summary: 'Get a list of authors',
        tags: ['Biblioteca/Authors'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/AuthorResource'),
                ),
            ),
        ],
    )]
    public function index(): ResourceCollection
    {
        $authors = Author::all();

        return AuthorResource::collection($authors);
    }

    /**
     * Store a newly created author in storage.
     *
     * @param AuthorRequest $request The request object containing the author data.
     *
     * @return JsonResponse The created author resource.
     */
    #[OA\Post(
        path: '/api/authors',
        summary: 'Create a new author',
        tags: ['Biblioteca/Authors'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/AuthorRequest'),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Author created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthorResource'),
            ),
            new OA\Response(response: 400, description: 'Bad Request'),
        ],
    )]
    public function store(AuthorRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $author = Author::create($validatedData);

        return (new AuthorResource($author))
            ->response()
            ->setStatusCode(HttpCodes::HTTP_CREATED);
    }

    /**
     * Display the specified author.
     *
     * @param string $author_id The unique ID of the author to retrieve.
     *
     * @return AuthorResource The requested author resource.
     */
    #[OA\Get(
        path: '/api/authors/{author_id}',
        summary: 'Get a specific author by ID',
        tags: ['Biblioteca/Authors'],
        parameters: [
            new OA\Parameter(
                name: 'author_id',
                in: 'path',
                required: true,
                description: 'ID of the author',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthorResource'),
            ),
            new OA\Response(response: 404, description: 'Author not found'),
        ],
    )]
    public function show(string $author_id): AuthorResource
    {
        $author = Author::findOrFail($author_id);

        return new AuthorResource($author);
    }

    /**
     * Update the specified author in storage.
     *
     * @param AuthorRequest $request   The request object containing the updated author data.
     * @param string        $author_id The unique ID of the author to update.
     *
     * @return AuthorResource The updated author resource.
     */
    #[OA\Put(
        path: '/api/authors/{author_id}',
        summary: 'Update an existing author',
        tags: ['Biblioteca/Authors'],
        parameters: [
            new OA\Parameter(
                name: 'author_id',
                in: 'path',
                required: true,
                description: 'ID of the author',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/AuthorRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Author updated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthorResource'),
            ),
            new OA\Response(response: 404, description: 'Author not found'),
        ],
    )]
    public function update(AuthorRequest $request, string $author_id): AuthorResource
    {
        $author = Author::findOrFail($author_id);
        $validatedData = $request->validated();
        $author->update($validatedData);

        return new AuthorResource($author);
    }

    /**
     * Remove the specified author from storage.
     *
     * @param string $author_id The unique ID of the author to delete.
     *
     * @return JsonResponse A JSON response with a HTTP 204 status code indicating success.
     */
    #[OA\Delete(
        path: '/api/authors/{author_id}',
        summary: 'Delete a specific author',
        tags: ['Biblioteca/Authors'],
        parameters: [
            new OA\Parameter(
                name: 'author_id',
                in: 'path',
                required: true,
                description: 'ID of the author',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Author deleted successfully'),
            new OA\Response(response: 404, description: 'Author not found'),
        ],
    )]
    public function destroy(string $author_id): JsonResponse
    {
        $author = Author::findOrFail($author_id);
        $author->delete();

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }
}
