<?php

namespace ThreeLeaf\Biblioteca\Http\Requests\Concerns;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * Reads a request input or route parameter as a UUID, for use in a composite unique rule.
 *
 * A <code>Rule::unique()->where()</code> takes its scope values from other inputs, and the rule is built
 * before those inputs are validated. Laravel writes each scope value into a rule string and parses it back,
 * so a crafted value with quotes or backslashes can add a <code>where</code> on a column of the client's
 * choosing, and the query then fails. An array value would turn the <code>where</code> into a
 * <code>whereIn</code>. Every scope column is a UUID, so any value that is not a well-formed UUID is read as
 * null. The request then fails on that input's own <code>uuid</code> rule.
 *
 * @mixin FormRequest
 */
trait ReadsUuidInput
{
    /**
     * Get an input value when it is a well-formed UUID.
     *
     * @param string $key The input key.
     *
     * @return string|null The UUID, or null when the input is missing or is not a UUID string.
     */
    protected function uuidInput(string $key): ?string
    {
        $value = $this->input($key);

        return is_string($value) && Str::isUuid($value) ? $value : null;
    }

    /**
     * Get a route parameter value when it is a well-formed UUID.
     *
     * The value is used as the id to ignore in a unique rule, or to look up the stored record. The form
     * request is resolved before the controller's <code>findOrFail()</code>, so the value is not checked
     * yet. A value that is not a UUID would fail the query on a database with a native UUID type.
     *
     * @param string $name The route parameter name.
     *
     * @return string|null The UUID, or null when the parameter is missing or is not a UUID string.
     */
    protected function uuidRouteParameter(string $name): ?string
    {
        $value = $this->route($name);

        return is_string($value) && Str::isUuid($value) ? $value : null;
    }
}
