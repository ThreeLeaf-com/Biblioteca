<?php

namespace Database\Factories\ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use ThreeLeaf\Biblioteca\Models\Author;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Publisher;

/**
 * Generate random {@link Book} data.
 *
 * @mixin Book
 */
class BookFactory extends Factory
{
    protected $model = Book::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $author = Author::factory()->create();
        $publisher = Publisher::factory()->create();
        return [
            'title' => $this->faker->sentence(),
            'author_id' => $author->author_id,
            'publisher_id' => $publisher->publisher_id,
            'published_date' => $this->faker->date(),
            'isbn' => $this->faker->isbn13(),
            'summary' => $this->faker->paragraph(),
        ];
    }
}
