<?php

namespace Database\Factories\ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use ThreeLeaf\Biblioteca\Models\TableOfContents;

/**
 * Generate random {@link TableOfContents} data.
 *
 * @mixin TableOfContents
 */
class TableOfContentsFactory extends Factory
{
    protected $model = TableOfContents::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'page_number' => $this->faker->numberBetween(1, 1000),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
