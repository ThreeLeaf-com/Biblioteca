<?php

namespace ThreeLeaf\Biblioteca\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * Provides functionality to compare Eloquent models for data equality.
 *
 * @mixin Model
 */
trait Equals
{
    /**
     * Compare this model with another model for data equality.
     *
     * Compares the values of specified attributes between two models.
     * If no attributes are specified, compares all fillable attributes.
     * Can optionally include or exclude the primary key in the comparison.
     *
     * @param Model              $other             The model to compare with
     * @param array<string>|null $attributes        Optional specific attributes to compare (defaults to fillable attributes)
     * @param bool               $includePrimaryKey Whether to include primary key in comparison (defaults to true)
     *
     * @return bool True if the specified attributes are equal between models, false otherwise
     */
    public function equals(
        Model $other,
        array $attributes = null,
        bool  $includePrimaryKey = true,
    ): bool
    {
        if (get_class($other) !== get_class($this)) {
            return false;
        }

        $baseAttributes = $attributes ?? $this->fillable;
        $compareAttributes = $includePrimaryKey
            ? array_merge($baseAttributes, [$this->primaryKey])
            : $baseAttributes;

        return $this->only($compareAttributes) == $other->only($compareAttributes);
    }
}
