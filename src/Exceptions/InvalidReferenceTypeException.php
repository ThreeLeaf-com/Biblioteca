<?php

namespace ThreeLeaf\Biblioteca\Exceptions;

use RuntimeException;
use ThreeLeaf\Biblioteca\Models\Annotation;

/**
 * Thrown when an {@link Annotation} reference type is not one of the permitted models.
 *
 * <code>reference_type</code> names the class Eloquent resolves when the polymorphic
 * reference is read, so a value outside {@link Annotation::REFERENCE_TYPES} is a class
 * lookup driven by stored data. The exception is raised both when such a value is written
 * and when a row already holding one is resolved, so a value written by a release that did
 * not constrain the column cannot be resolved either.
 */
class InvalidReferenceTypeException extends RuntimeException
{

    /**
     * Build the exception for a rejected reference type.
     *
     * @param string $referenceType The rejected value.
     */
    public function __construct(string $referenceType)
    {
        parent::__construct(sprintf(
            'Invalid annotation reference_type "%s". Permitted types are: %s.',
            $referenceType,
            implode(', ', Annotation::REFERENCE_TYPES),
        ));
    }
}
