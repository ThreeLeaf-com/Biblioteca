<?php

namespace ThreeLeaf\Biblioteca\Repositories;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Models\Sentence;

/** {@link Paragraph} repository. */
class ParagraphRepository
{
    /**
     * Creates a new Paragraph record in the database.
     *
     * @param array $data An associative array containing the data for the new Paragraph record.
     *
     * @return Paragraph The newly created Paragraph model instance.
     */
    public function create(array $data): Paragraph
    {
        return Paragraph::create($data);
    }

    /**
     * Retrieves a Paragraph record from the database based on the provided paragraph ID.
     *
     * @param string $paragraph_id The unique identifier of the Paragraph record to be retrieved.
     *
     * @return Paragraph|null The Paragraph model instance representing the retrieved record, or null if no matching record is found.
     */
    public function read(string $paragraph_id): ?Paragraph
    {
        return Paragraph::find($paragraph_id);
    }

    /**
     * Retrieves a Paragraph record from the database based on the provided paragraph ID.
     * If no matching record is found, the function will throw a ModelNotFoundException.
     *
     * @param string $paragraph_id The unique identifier of the Paragraph record to be retrieved.
     *
     * @return Paragraph The Paragraph model instance representing the retrieved record.
     *
     * @throws ModelNotFoundException If no matching record is found.
     */
    public function readOrFail(string $paragraph_id): Paragraph
    {
        return Paragraph::findOrFail($paragraph_id);
    }

    /**
     * Retrieves all Paragraph records from the database, optionally filtered by chapter ID.
     *
     * @param string|null $chapterId The unique identifier of the chapter for which to retrieve paragraphs.
     *                               If null, all paragraphs will be retrieved.
     *
     * @return Paragraph[] An array of Paragraph model instances, representing all Paragraph records in the database,
     *                     optionally filtered by chapter ID.
     */
    public function readAll(?string $chapterId = null): array
    {
        $query = Paragraph::query();

        if ($chapterId) {
            $query->where('chapter_id', $chapterId);
        }

        return $query->orderBy('paragraph_number')->get()->all();
    }

    /**
     * Updates the provided Paragraph record in the database with the given data.
     *
     * @param Paragraph $paragraph The Paragraph model instance to be updated.
     *                             This parameter should be an instance of the Paragraph model, representing the paragraph in the database.
     *
     * @param array     $data      An associative array containing the updated data for the paragraph.
     *                             This parameter should be an array of key-value pairs, where the keys correspond to the attributes of the Paragraph model.
     *
     * @return Paragraph The updated Paragraph model instance.
     *                   The function returns the updated Paragraph model instance to allow for method chaining or further processing.
     */
    public function update(Paragraph $paragraph, array $data): Paragraph
    {
        $paragraph->update($data);

        return $paragraph;
    }

    /**
     * Updates or creates a Paragraph record in the database based on the provided data.
     *
     * @param array $data An associative array containing the data for the Paragraph record to be updated or created.
     *                    The array should include the 'chapter_id' and 'paragraph_number' keys to identify the paragraph,
     *                    and any other attributes to be updated.
     *
     * @return Paragraph The updated or newly created Paragraph model instance.
     *                   The function returns the Paragraph model instance to allow for method chaining or further processing.
     */
    public function updateOrCreate(array $data): Paragraph
    {
        return Paragraph::updateOrCreate(
            ['chapter_id' => $data['chapter_id'], 'paragraph_number' => $data['paragraph_number']],
            $data
        );
    }

    /**
     * Deletes a Paragraph record from the database based on the provided paragraph ID.
     *
     * @param string $paragraph_id The unique identifier of the Paragraph record to be deleted.
     *
     * @return bool True if the Paragraph record was successfully deleted, false otherwise.
     */
    public function delete(string $paragraph_id): bool
    {
        $paragraph = $this->read($paragraph_id);

        return $paragraph && $this->deleteParagraph($paragraph);
    }

    /**
     * Deletes the provided Paragraph record from the database.
     *
     * @param Paragraph $paragraph The Paragraph model instance to be deleted.
     *                             This parameter should be an instance of the Paragraph model, representing the paragraph in the database.
     *
     * @return bool True if the paragraph was successfully deleted, false otherwise.
     */
    public function deleteParagraph(Paragraph $paragraph): bool
    {
        return $paragraph->delete();
    }

    /**
     * Deletes all Sentence records associated with the given Paragraph.
     *
     * @param Paragraph $paragraph The Paragraph model instance for which all associated Sentence records will be deleted.
     */
    public function deleteAllSentences(Paragraph $paragraph): void
    {
        $paragraph->sentences()->delete();
    }

    /**
     * Adds the provided Sentences to the given Paragraph.
     *
     * @param Paragraph  $paragraph The Paragraph model instance to which the sentences will be attached.
     * @param Sentence[] $sentences An array of Sentence model instances to be attached to the paragraph.
     */
    public function addSentences(Paragraph $paragraph, array $sentences): void
    {
        $paragraph->sentences()->saveMany($sentences);
    }
}
