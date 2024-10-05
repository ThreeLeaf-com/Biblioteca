<?php

namespace ThreeLeaf\Biblioteca\Enums;

/**
 * Enum representing different note types.
 *
 * @OA\Schema(
 *     schema="NoteType",
 *     type="string",
 *     description="Enumeration of possible note types.",
 *     enum={"FOOTNOTE", "ENDNOTE", "BOTH"},
 *     example="FOOTNOTE"
 * )
 */
enum NoteType: string
{
    /**
     * Indicates the note is a footnote.
     *
     * Used to reference additional content at the bottom of a page.
     */
    case FOOTNOTE = 'FOOTNOTE';

    /**
     * Indicates the note is an endnote.
     *
     * Used to reference additional content at the end of a chapter or document.
     */
    case ENDNOTE = 'ENDNOTE';

    /**
     * Indicates the note can be both a footnote and an endnote.
     *
     * Used for content that can appear as both a footnote and an endnote depending on context.
     */
    case BOTH = 'BOTH';
}
