<?php

namespace ThreeLeaf\Biblioteca\Models;

use ThreeLeaf\Biblioteca\Constants\BibliotecaConstants;

/** The {@link Genre} associations with {@link Book}. */
class BookGenre
{

    public const TABLE_NAME = BibliotecaConstants::TABLE_PREFIX . 'book_genres';
}
