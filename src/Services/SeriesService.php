<?php

namespace ThreeLeaf\Biblioteca\Services;

use Illuminate\Support\Facades\DB;
use ThreeLeaf\Biblioteca\Models\Series;

/** {@link Series} services. */
class SeriesService
{
    /**
     * Create a new series with associated books.
     *
     * @param array $data
     *
     * @return Series
     */
    public function create(array $data): Series
    {
        return DB::transaction(function () use ($data) {
            $bookIds = $data['book_ids'] ?? [];
            unset($data['book_ids']);

            $series = Series::create($data);

            if (!empty($bookIds)) {
                foreach ($bookIds as $bookId) {
                    $series->attachBook($bookId);
                }
            }

            return $series->load(['author', 'books']);
        });
    }

    /**
     * Update an existing series and its associated books.
     *
     * @param Series $series
     * @param array  $data
     *
     * @return Series
     */
    public function update(Series $series, array $data): Series
    {
        return DB::transaction(function () use ($series, $data) {
            $bookIds = $data['book_ids'] ?? [];
            unset($data['book_ids']);

            $series->update($data);

            if (!empty($bookIds)) {
                $series->books()->detach();

                foreach ($bookIds as $bookId) {
                    $series->attachBook($bookId);
                }
            }

            return $series->load(['author', 'books']);
        });
    }
}
