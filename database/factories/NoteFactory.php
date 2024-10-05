<?php

namespace Database\Factories\ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use ThreeLeaf\Biblioteca\Enums\Context;
use ThreeLeaf\Biblioteca\Enums\NoteType;
use ThreeLeaf\Biblioteca\Models\Note;

/**
 * Generate random {@link Note} data.
 *
 * @mixin Note
 */
class NoteFactory extends Factory
{
    protected $model = Note::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'note_label' => $this->faker->word(),
            'note_type' => $this->faker->randomElement(NoteType::class),
            'context' => $this->faker->randomElement(Context::class),
            'content' => $this->faker->paragraph(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
