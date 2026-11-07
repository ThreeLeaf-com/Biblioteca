<?php

namespace ThreeLeaf\Biblioteca\Services;

use Illuminate\Support\Facades\DB;
use ThreeLeaf\Biblioteca\Models\Series;

/** {@link Series} services. */
class SeriesService
{
    /**
     * Pivot rows per insert. Each row binds five values, so this stays under the 2,100-parameter
     * limit of SQL Server and the 999-variable limit of SQLite before 3.32.
     */
    private const ATTACH_CHUNK_SIZE = 150;

    /**
     * Create a new series and link its books in one transaction.
     *
     * The books are numbered 1, 2, 3 … in the order of `book_ids`. A repeated id, compared
     * without regard to case, is linked once at its first position.
     *
     * @param array{title: string, author_id: string, subtitle?: string|null, description?: string|null, book_ids?: list<string>|null} $data The validated series attributes, with an optional ordered list of book IDs.
     *
     * @return Series The new series with its `author` and `books` relations loaded.
     */
    public function create(array $data): Series
    {
        return DB::transaction(function () use ($data) {
            $bookIds = $data['book_ids'] ?? [];
            unset($data['book_ids']);

            $series = Series::create($data);

            $this->linkBooks($series, $bookIds);

            return $series->load(['author', 'books']);
        });
    }

    /**
     * Update an existing series and, when `book_ids` is non-empty, replace its books.
     *
     * An absent, null or empty `book_ids` leaves the current books unchanged.
     *
     * @param Series                                                                                                                  $series The series to update.
     * @param array{title?: string, author_id?: string, subtitle?: string|null, description?: string|null, book_ids?: list<string>|null} $data   The validated series attributes, with an optional ordered list of book IDs.
     *
     * @return Series The updated series with its `author` and `books` relations loaded.
     */
    public function update(Series $series, array $data): Series
    {
        return DB::transaction(function () use ($series, $data) {
            $bookIds = $data['book_ids'] ?? [];
            unset($data['book_ids']);

            $series->update($data);

            if (!empty($bookIds)) {
                $series->books()->detach();
                $this->linkBooks($series, $bookIds);
            }

            return $series->load(['author', 'books']);
        });
    }

    /**
     * Link books to a series that has none, numbering them 1, 2, 3 … in the given order.
     *
     * Batched inserts replace a per-book existence check and `max(number)` lookup, which are
     * redundant when the series starts with no books. Repeats are compared without regard to
     * case because the database may match UUIDs that way, and a second pivot row for the same
     * book would break the pivot primary key.
     *
     * @param Series   $series  A series with no linked books.
     * @param string[] $bookIds The book IDs in series order; a repeated id is linked once, at its first position.
     *
     * @return void
     */
    private function linkBooks(Series $series, array $bookIds): void
    {
        $pivotRows = [];
        $seen = [];
        foreach ($bookIds as $bookId) {
            $key = strtolower($bookId);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $pivotRows[$bookId] = ['number' => count($pivotRows) + 1];
        }

        foreach (array_chunk($pivotRows, self::ATTACH_CHUNK_SIZE, true) as $chunk) {
            $series->books()->attach($chunk);
        }
    }
}
