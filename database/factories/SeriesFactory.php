<?php

namespace Database\Factories\ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use ThreeLeaf\Biblioteca\Models\Author;
use ThreeLeaf\Biblioteca\Models\Series;

/**
 * Generate random {@link Series} data.
 *
 * @mixin Series
 */
class SeriesFactory extends Factory
{
    protected $model = Series::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $author = Author::factory()->create();
        return [
            'title' => $this->faker->sentence(5),
            'subtitle' => $this->faker->sentence(2),
            'author_id' => $author->author_id,
            'description' => $this->faker->paragraph(),
        ];
    }
}
