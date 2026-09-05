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
     * @return array<string, mixed> The default annotation attributes.
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
     * @return AnnotationFactory The factory with the sentence reference state applied.
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
     * @return AnnotationFactory The factory with the paragraph reference state applied.
     */
    public function paragraph(): AnnotationFactory
    {
        return $this->state(fn() => $this->generateAnnotationData(Paragraph::class));
    }

    /**
     * Helper method to generate annotation data for a specific reference type.
     *
     * The stored <code>reference_type</code> is the model's morph alias, not its class name,
     * which is what {@link Annotation::assertReferenceType()} writes and what the column has
     * held since 3.0.0. It is read from the morph map rather than from
     * {@link Annotation::REFERENCE_TYPES}, so generated rows follow a host application's own
     * morph map exactly as application writes do. Rows built with <code>create()</code> pass
     * through the model's mutator and would be normalized anyway; this keeps
     * <code>make()</code> and <code>raw()</code> in the same shape.
     *
     * @param class-string<Sentence|Paragraph> $referenceType The model the annotation references.
     *
     * @return array<string, mixed> The generated annotation attributes.
     */
    private function generateAnnotationData(string $referenceType): array
    {
        return [
            'reference_id' => $referenceType::factory(),
            'reference_type' => Annotation::assertReferenceType($referenceType),
            'content' => $referenceType === Sentence::class
                ? $this->faker->sentence()
                : $this->faker->paragraph(),
        ];
    }
}
