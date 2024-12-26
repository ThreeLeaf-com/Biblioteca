<?php

namespace ThreeLeaf\Biblioteca\Repositories;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Models\Paragraph;

/** {@link Chapter} repository. */
class ChapterRepository
{

    /**
     * Creates a new Chapter record in the database.
     *
     * @param array $data An associative array containing the data for the new Chapter record.
     *
     * @return Chapter The newly created Chapter model instance.
     */
    public function create(array $data): Chapter
    {
        return Chapter::create($data);
    }

    /**
     * Retrieves a Chapter record from the database based on the provided chapter ID.
     *
     * This function uses Eloquent's model find method to fetch a Chapter record from the database based on the provided chapter ID.
     * If a matching record is found, the function returns an instance of the Chapter model representing the record.
     * If no matching record is found, the function returns null.
     *
     * @param string $chapter_id The unique identifier of the Chapter record to be retrieved.
     *
     * @return Chapter|null The Chapter model instance representing the retrieved record, or null if no matching record is found.
     */
    public function read(string $chapter_id): ?Chapter
    {
        return Chapter::find($chapter_id);
    }

    /**
     * Retrieves a Chapter record from the database based on the provided chapter ID.
     * If no matching record is found, the function will throw a ModelNotFoundException.
     *
     * @param string $chapter_id The unique identifier of the Chapter record to be retrieved.
     *
     * @return Chapter The Chapter model instance representing the retrieved record.
     *
     * @throws ModelNotFoundException If no matching record is found.
     */
    public function readOrFail(string $chapter_id): Chapter
    {
        return Chapter::findOrFail($chapter_id);
    }

    /**
     * Retrieves all Chapter records from the database, optionally filtered by book ID.
     *
     * This function uses Eloquent's query builder to fetch all Chapter records from the database.
     * If a book ID is provided, the function will filter the results to only include chapters belonging to the specified book.
     * The retrieved records are sorted by chapter number in ascending order.
     *
     * @param string|null $bookId The unique identifier of the book for which to retrieve chapters.
     *                            If null, all chapters will be retrieved.
     *
     * @return Chapter[] An array of Chapter model instances, representing all Chapter records in the database,
     *                  optionally filtered by book ID.
     */
    public function readAll(?string $bookId = null): array
    {
        $query = Chapter::query();

        if ($bookId) {
            $query->where('book_id', $bookId);
        }

        return $query->orderBy('chapter_number')->get()->all();
    }

    /**
     * Updates the provided Chapter record in the database with the given data.
     *
     * This function takes a Chapter model instance and an associative array of data as parameters.
     * It updates the attributes of the chapter with the provided data and saves the changes to the database.
     *
     * @param Chapter $chapter The Chapter model instance to be updated.
     *                         This parameter should be an instance of the Chapter model, representing the chapter in the database.
     *
     * @param array   $data    An associative array containing the updated data for the chapter.
     *                         This parameter should be an array of key-value pairs, where the keys correspond to the attributes of the Chapter model.
     *
     * @return Chapter The updated Chapter model instance.
     *                 The function returns the updated Chapter model instance to allow for method chaining or further processing.
     */
    public function update(Chapter $chapter, array $data): Chapter
    {
        $chapter->update($data);

        return $chapter;
    }

    /**
     * Updates or creates a Chapter record in the database based on the provided data.
     *
     * This function uses Eloquent's updateOrCreate method to update an existing Chapter record in the database,
     * or create a new record if no matching record is found. The function takes an associative array of data as a parameter,
     * which should contain the 'book_id' and 'chapter_number' keys to identify the chapter, along with any other attributes to be updated.
     *
     * @param array $data An associative array containing the data for the Chapter record to be updated or created.
     *                    The array should include the 'book_id' and 'chapter_number' keys to identify the chapter,
     *                    and any other attributes to be updated.
     *
     * @return Chapter The updated or newly created Chapter model instance.
     *                 The function returns the Chapter model instance to allow for method chaining or further processing.
     */
    public function updateOrCreate(array $data): Chapter
    {
        return Chapter::updateOrCreate(
            ['book_id' => $data['book_id'], 'chapter_number' => $data['chapter_number']],
            $data
        );
    }

    /**
     * Deletes a Chapter record from the database based on the provided chapter ID.
     *
     * This function retrieves the Chapter record from the database using the provided chapter ID,
     * and then deletes the record from the database. If a matching record is found, the function
     * returns true; otherwise, it returns false.
     *
     * @param string $chapter_id The unique identifier of the Chapter record to be deleted.
     *
     * @return bool True if the Chapter record was successfully deleted, false otherwise.
     */
    public function delete(string $chapter_id): bool
    {
        $chapter = $this->read($chapter_id);

        return $chapter && $this->deleteChapter($chapter);
    }

    /**
     * Deletes the provided Chapter record from the database.
     *
     * This function takes a Chapter model instance as a parameter and deletes the corresponding record from the database.
     * The function returns a boolean value indicating whether the deletion was successful.
     *
     * @param Chapter $chapter The Chapter model instance to be deleted.
     *                         This parameter should be an instance of the Chapter model, representing the chapter in the database.
     *
     * @return bool True if the chapter was successfully deleted, false otherwise.
     */
    public function deleteChapter(Chapter $chapter): bool
    {
        return $chapter->delete();
    }

    /**
     * Deletes all Paragraph records associated with the given Chapter.
     *
     * This function takes a Chapter model instance as a parameter and uses Eloquent's relationship methods to delete all Paragraph records associated with the chapter.
     * The function does not return any value, but it modifies the database by removing the associations between the chapter and its paragraphs.
     *
     * @param Chapter $chapter The Chapter model instance for which all associated Paragraph records will be deleted.
     */
    public function deleteAllParagraphs(Chapter $chapter): void
    {
        $chapter->paragraphs()->delete();
    }

    /**
     * Adds the provided paragraphs to the given chapter.
     *
     * This function associates an array of Paragraph model instances with a given Chapter model instance in the database.
     * It extracts the IDs of the paragraphs from the array and saves them as associated records in the database.
     *
     * @param Chapter     $chapter    The Chapter model instance to which the paragraphs will be attached.
     *                                This parameter should be an instance of the Chapter model, representing the chapter in the database.
     *
     * @param Paragraph[] $paragraphs An array of Paragraph model instances to be attached to the chapter.
     *                                This parameter should be an array of instances of the Paragraph model, representing the paragraphs to be associated with the chapter.
     */
    public function addParagraphs(Chapter $chapter, array $paragraphs): void
    {
        $chapter->paragraphs()->saveMany($paragraphs);
    }
}
