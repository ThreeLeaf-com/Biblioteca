<?php

namespace Database\Factories\ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Models\Figure;

/**
 * Generate random {@link Figure} data.
 *
 * @mixin Figure
 */
class FigureFactory extends Factory
{
    protected $model = Figure::class;

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
            'figure_label' => $this->faker->randomLetter(),
            'caption' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'image_url' => $this->faker->imageUrl(),
        ];
    }
}
