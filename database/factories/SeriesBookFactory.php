<?php

namespace Database\Factories\ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Series;
use ThreeLeaf\Biblioteca\Models\SeriesBook;

/**
 * @extends Factory<SeriesBook>
 */
class SeriesBookFactory extends Factory
{
    protected $model = SeriesBook::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $series = Series::factory()->create();
        $book = Book::factory()->create();
        return [
            'series_id' => $series->series_id,
            'book_id' => $book->book_id,
            'number' => $this->faker->unique()->numberBetween(1, 1000),
        ];
    }
}
