<?php

namespace Database\Factories\ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use ThreeLeaf\Biblioteca\Models\Sentence;

/**
 * Generate random {@link Sentence} data.
 *
 * @mixin Sentence
 */
class SentenceFactory extends Factory
{
    protected $model = Sentence::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'paragraph_id' => $this->faker->uuid(),
            'sentence_number' => $this->faker->numberBetween(1, 100),
            'content' => $this->faker->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
