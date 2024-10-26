<?php

namespace Database\Factories\ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use ThreeLeaf\Biblioteca\Models\Annotation;
use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Models\Sentence;

/**
 * Generate random {@link Annotation} data.
 * By default, this factory will randomly create either a Sentence or Paragraph reference type.
 *
 * @mixin Annotation
 *
 * ### Usage Examples:
 *
 * - **Create an annotation with a random type**:
 *   ```php
 *   $annotation = Annotation::factory()->create();
 *   ```
 *
 * - **Create an annotation specifically with a Sentence reference**:
 *   ```php
 *   $annotation = Annotation::factory()->sentence()->create();
 *   ```
 *
 * - **Create an annotation specifically with a Paragraph reference**:
 *   ```php
 *   $annotation = Annotation::factory()->paragraph()->create();
 *   ```
 */
class AnnotationFactory extends Factory
{
    protected $model = Annotation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $referenceType = $this->faker->boolean ? Sentence::class : Paragraph::class;

        return $this->generateAnnotationData($referenceType);
    }

    /**
     * State to create an Annotation with a Sentence reference.
     *
     * ```php
     * $annotation = Annotation::factory()->sentence()->create();
     * ```
     *
     * @return AnnotationFactory
     */
    public function sentence(): AnnotationFactory
    {
        return $this->state(fn() => $this->generateAnnotationData(Sentence::class));
    }

    /**
     * State to create an Annotation with a Paragraph reference.
     *
     * ```php
     * $annotation = Annotation::factory()->paragraph()->create();
     * ```
     *
     * @return AnnotationFactory
     */
    public function paragraph(): AnnotationFactory
    {
        return $this->state(fn() => $this->generateAnnotationData(Paragraph::class));
    }

    /**
     * Helper method to generate annotation data for a specific reference type.
     *
     * @param class-string<Sentence|Paragraph> $referenceType
     *
     * @return array<string, mixed>
     */
    private function generateAnnotationData(string $referenceType): array
    {
        return [
            'reference_id' => $referenceType::factory(),
            'reference_type' => $referenceType,
            'content' => $referenceType === Sentence::class
                ? $this->faker->sentence()
                : $this->faker->paragraph(),
        ];
    }
}
