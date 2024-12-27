<?php

namespace Database\Factories\ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Models\Paragraph;

/**
 * Generate random {@link Paragraph} data.
 *
 * @mixin Paragraph
 */
class ParagraphFactory extends Factory
{
    protected $model = Paragraph::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $chapter = Chapter::factory()->create();
        return [
            'chapter_id' => $chapter->chapter_id,
            'paragraph_number' => $this->faker->unique()->numberBetween(1, 1000),
            'content' => $this->faker->paragraph(),
        ];
    }
}
