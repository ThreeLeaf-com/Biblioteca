<?php

namespace ThreeLeaf\Biblioteca\Models;

use ThreeLeaf\Biblioteca\Constants\Biblioteca;

/** The {@link Genre} associations with {@link Book}. */
class BookGenre
{

    public const TABLE_NAME = Biblioteca::TABLE_PREFIX . 'book_genres';
}
