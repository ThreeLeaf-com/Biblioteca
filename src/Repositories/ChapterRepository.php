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
     * If the 'chapter_number' is not provided in the input data, the function will attempt to determine the next available chapter number for the given book.
     * It does this by fetching the highest chapter number for the book from the database and incrementing it by one.
     * If no chapters exist for the book, the function will default to chapter number 1.
     *
     * @param array $data An associative array containing the data for the new Chapter record.
     *
     * @return Chapter The newly created Chapter model instance.
     */
    public function create(array $data): Chapter
    {
        if (empty($data['chapter_number']) || !is_numeric($data['chapter_number']) || $data['chapter_number'] < 1) {
            $lastChapter = Chapter::where('book_id', $data['book_id'])
                ->orderBy('chapter_number', 'desc')
                ->first();

            if ($lastChapter) {
                $data['chapter_number'] = $lastChapter->chapter_number + 1;
            } else {
                $data['chapter_number'] = 1;
            }
        }

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
     * Retrieves all Chapter records from the database.
     *
     * This function uses Eloquent's query builder to fetch all Chapter records from the database.
     * It returns an array of Chapter model instances, representing the retrieved records.
     *
     * @return Chapter[] An array of Chapter model instances, representing all Chapter records in the database.
     */
    public function readAll(): array
    {
        return Chapter::all()->all();
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
