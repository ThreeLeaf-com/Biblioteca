<?php

namespace ThreeLeaf\Biblioteca\Relations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use ThreeLeaf\Biblioteca\Models\Annotation;

/**
 * A {@link MorphTo} that resolves only the reference types {@link Annotation} permits.
 *
 * Eager loading does not go through the parent model when it turns a stored type into a
 * class: {@link MorphTo::createModelByType()} calls
 * <code>Model::getActualClassNameForMorph()</code> statically on the base {@link Model}
 * class, so an override on {@link Annotation} covers the lazy path only. This subclass
 * closes the eager path, so <code>Annotation::with('reference')</code> is constrained
 * exactly as <code>$annotation-&gt;reference</code> is.
 */
class ReferenceMorphTo extends MorphTo
{

    /**
     * Instantiate the model for a stored reference type.
     *
     * The class is taken from {@link Annotation::resolveReferenceType()} and instantiated
     * here rather than delegated to the parent, which would discard the resolved class and
     * resolve the raw string again.
     *
     * @param string $type The stored reference type.
     *
     * @return Model The related model instance.
     */
    public function createModelByType($type): Model
    {
        $class = Annotation::resolveReferenceType($type);

        return tap(new $class(), function (Model $instance) {
            if (!$instance->getConnectionName()) {
                $instance->setConnection($this->getConnection()->getName());
            }
        });
    }
}
