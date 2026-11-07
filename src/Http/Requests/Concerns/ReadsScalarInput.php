<?php

namespace ThreeLeaf\Biblioteca\Http\Requests\Concerns;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Reads a request input as a scalar, for use as a column value in a database rule.
 *
 * A composite <code>Rule::unique()->where()</code> takes its scope values from other inputs. Those inputs
 * are validated by their own rules, but the unique rule is built before validation runs. An array value
 * would turn the <code>where</code> into a <code>whereIn</code>, so a non-scalar value is read as null.
 *
 * @mixin FormRequest
 */
trait ReadsScalarInput
{
    /**
     * Get an input value when it is a scalar.
     *
     * @param string $key The input key.
     *
     * @return string|int|float|bool|null The input value, or null when it is missing or not a scalar.
     */
    protected function scalarInput(string $key): string|int|float|bool|null
    {
        $value = $this->input($key);

        return is_scalar($value) ? $value : null;
    }
}
