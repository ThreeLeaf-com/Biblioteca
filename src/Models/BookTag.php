<?php

namespace ThreeLeaf\Biblioteca\Models;

use ThreeLeaf\Biblioteca\Constants\Biblioteca;

/** The {@link Tag} associations with {@link Book}. */
class BookTag
{

    public const TABLE_NAME = Biblioteca::TABLE_PREFIX . 'book_tags';
}
