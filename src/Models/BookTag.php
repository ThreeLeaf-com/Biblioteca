<?php

namespace ThreeLeaf\Biblioteca\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use ThreeLeaf\Biblioteca\Constants\BibliotecaConstants;

/** The {@link Tag} associations with {@link Book}. */
class BookTag extends Pivot
{

    public const TABLE_NAME = BibliotecaConstants::TABLE_PREFIX . 'book_tags';
}
