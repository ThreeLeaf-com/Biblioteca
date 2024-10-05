<?php

namespace ThreeLeaf\Biblioteca\Enums;

/**
 * Enum representing different contexts for a {@link Note}.
 *
 * @OA\Schema(
 *     schema="Context",
 *     type="string",
 *     description="Enumeration of possible contexts for a note.",
 *     enum={"PAGE", "CHAPTER", "BOOK"},
 *     example="PAGE"
 * )
 */
enum Context: string
{
    /**
     * Context is a page.
     *
     * Indicates the context is limited to a single page.
     */
    case PAGE = 'PAGE';

    /**
     * Context is a chapter.
     *
     * Indicates the context is for an entire chapter.
     */
    case CHAPTER = 'CHAPTER';

    /**
     * Context is the entire book.
     *
     * Indicates the context spans the whole book.
     */
    case BOOK = 'BOOK';
}
