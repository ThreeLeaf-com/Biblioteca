<?php

namespace Database\Factories\ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
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
        return [
            'chapter_id' => $this->faker->uuid(),
            'paragraph_number' => $this->faker->numberBetween(1, 100),
            'content' => $this->faker->paragraph(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
