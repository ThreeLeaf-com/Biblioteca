<?php

namespace ThreeLeaf\Biblioteca\Exceptions;

use RuntimeException;
use ThreeLeaf\Biblioteca\Models\Annotation;

/**
 * Thrown when an {@link Annotation} reference type is not one of the permitted models.
 *
 * <code>reference_type</code> decides the class Eloquent resolves when the polymorphic
 * reference is read, so a value that denotes no model in {@link Annotation::REFERENCE_TYPES}
 * is a class lookup driven by stored data. The exception is raised both when such a value is
 * written and when a row already holding one is resolved, so a value written by a release
 * that did not constrain the column cannot be resolved either.
 */
class InvalidReferenceTypeException extends RuntimeException
{

    /**
     * Build the exception for a rejected reference type.
     *
     * Both forms are listed. The aliases are what the column stores, and so what an operator
     * reading this message is most likely to be comparing against; the class names are still
     * accepted on input and are what a subclass of one of them extends.
     *
     * @param string $referenceType The rejected value.
     */
    public function __construct(string $referenceType)
    {
        parent::__construct(sprintf(
            'Invalid annotation reference_type "%s". Permitted aliases are: %s. '
            . 'Permitted classes, and their subclasses, are: %s.',
            $referenceType,
            implode(', ', array_keys(Annotation::REFERENCE_TYPES)),
            implode(', ', Annotation::REFERENCE_TYPES),
        ));
    }
}
