<?php

namespace ThreeLeaf\Biblioteca\Services;

use Illuminate\Support\Facades\DB;
use ThreeLeaf\Biblioteca\Models\Series;

/** {@link Series} services. */
class SeriesService
{
    /**
     * Create a new series and link its books in one transaction.
     *
     * The books are numbered 1, 2, 3 … in the order of `book_ids`. A repeated id is linked once,
     * at its first position.
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
     * One insert replaces a per-book existence check and `max(number)` lookup, which are
     * redundant when the series starts with no books.
     *
     * @param Series   $series  A series with no linked books.
     * @param string[] $bookIds The book IDs in series order; a repeated id is linked once, at its first position.
     *
     * @return void
     */
    private function linkBooks(Series $series, array $bookIds): void
    {
        $pivotRows = [];
        foreach (array_values(array_unique($bookIds)) as $index => $bookId) {
            $pivotRows[$bookId] = ['number' => $index + 1];
        }

        if (!empty($pivotRows)) {
            $series->books()->attach($pivotRows);
        }
    }
}
