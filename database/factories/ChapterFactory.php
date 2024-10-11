<?php

namespace Database\Factories\ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use ThreeLeaf\Biblioteca\Models\Author;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Chapter;

/**
 * Generate random {@link Chapter} data.
 *
 * @mixin Chapter
 */
class ChapterFactory extends Factory
{
    protected $model = Chapter::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->author_id]);
        return [
            'book_id' => $book->book_id,
            'chapter_image_url' => $this->faker->url(),
            'content' => $this->faker->paragraphs(5, true),  // Generate 5 paragraphs with 2 sentences each.
            'title' => $this->faker->sentence(),
            'summary' => $this->faker->paragraph(),
            'chapter_number' => $this->faker->numberBetween(1, 100),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
