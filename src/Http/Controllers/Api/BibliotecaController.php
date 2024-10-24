<?php

namespace ThreeLeaf\Biblioteca\Http\Controllers\Api;

use Exception;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use ThreeLeaf\Biblioteca\Http\Controllers\Controller;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Series;

/**
 * Controller for handling general library-related endpoints.
 *
 * @OA\Tag(
 *     name="Biblioteca/Library",
 *     description="General endpoints for the Biblioteca"
 * )
 */
class BibliotecaController extends Controller
{
    /**
     * Get an array of series IDs and book IDs.
     *
     * @OA\Get(
     *     path="/api/library",
     *     summary="Get series and book IDs",
     *     tags={"Biblioteca"},
     *     @OA\Response(
     *         response=200,
     *         description="An array containing series and book IDs",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="series_ids",
     *                 type="array",
     *                 @OA\Items(type="string", example="b1234567-89ab-cdef-0123-456789abcdef"),
     *                 description="Array of series IDs"
     *             ),
     *             @OA\Property(
     *                 property="book_ids",
     *                 type="array",
     *                 @OA\Items(type="string", example="a9876543-21ba-fedc-0123-456789abcdef"),
     *                 description="Array of book IDs"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function index()
    {
        try {
            $seriesIds = Series::pluck('series_id')->toArray();
            $bookIds = Book::pluck('book_id')->toArray();

            return response()->json([
                'series_ids' => $seriesIds,
                'book_ids' => $bookIds,
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => 'An error occurred while retrieving data'], HttpCodes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
