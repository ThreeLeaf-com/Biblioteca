<?php

namespace ThreeLeaf\Biblioteca\Models;

use ThreeLeaf\Biblioteca\Constants\BibliotecaConstants;

/** The {@link Tag} associations with {@link Book}. */
class BookTag
{

    public const TABLE_NAME = BibliotecaConstants::TABLE_PREFIX . 'book_tags';
}
