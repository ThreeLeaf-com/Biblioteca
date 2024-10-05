<?php

namespace Database\Factories\ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use ThreeLeaf\Biblioteca\Models\Annotation;

/**
 * Generate random {@link Annotation} data.
 *
 * @mixin Annotation
 */
class AnnotationFactory extends Factory
{
    protected $model = Annotation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content' => $this->faker->paragraph(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /** Indicate that the annotation should have a specific context. */
    public function forChapter(): static
    {
        return $this->state(fn(array $attributes) => [
            'context' => 'chapter',
        ]);
    }
}
