<?php

namespace ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use ThreeLeaf\Biblioteca\Constants\BibliotecaConstants;

/** The {@link Genre} associations with {@link Book}. */
class BookGenre extends Pivot
{

    public const TABLE_NAME = BibliotecaConstants::TABLE_PREFIX . 'book_genres';
}
