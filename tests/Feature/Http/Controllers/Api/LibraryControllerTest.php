<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Series;

/** Test {@link LibraryController}. */
class LibraryControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test {@link LibraryController::index()}. */
    public function indexReturnsSeriesAndBookIds(): void
    {
        $series = Series::factory()->count(2)->create();
        $books = Book::factory()->count(3)->create();

        $expectedSeriesIds = $series->pluck('series_id')->toArray();
        $expectedBookIds = $books->pluck('book_id')->toArray();

        $response = $this->getJson(route('library.index'));

        $response->assertStatus(HttpCodes::HTTP_OK)
            ->assertJson([
                'series_ids' => $expectedSeriesIds,
                'book_ids' => $expectedBookIds,
            ]);
    }

}
