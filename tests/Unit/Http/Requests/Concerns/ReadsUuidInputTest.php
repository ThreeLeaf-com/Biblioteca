<?php

namespace Tests\Unit\Http\Requests\Concerns;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ThreeLeaf\Biblioteca\Http\Requests\Concerns\ReadsUuidInput;

/** Test {@link ReadsUuidInput}. */
class ReadsUuidInputTest extends TestCase
{
    private const UUID = 'f7f9d3e0-434b-11ed-b878-0242ac120002';

    /**
     * Get the input values that are not a UUID.
     *
     * @return array<string, array{mixed}> The values, keyed by case name.
     */
    public static function notUuidValues(): array
    {
        return [
            'missing' => [null],
            'plain string' => ['abc'],
            'quote and backslash' => ['\\",nope.zzz,"1'],
            'integer' => [5],
            'boolean' => [true],
            'array' => [[self::UUID]],
            'nested array' => [[[self::UUID, self::UUID]]],
        ];
    }

    /** {@link ReadsUuidInput::uuidInput()} returns a well-formed UUID unchanged. */
    #[Test]
    public function uuidInputReturnsUuid(): void
    {
        $request = $this->makeRequest(['author_id' => self::UUID]);

        $this->assertSame(self::UUID, $request->readInput('author_id'));
    }

    /**
     * {@link ReadsUuidInput::uuidInput()} returns null for a value that is not a UUID string.
     *
     * @param mixed $value The input value.
     */
    #[Test]
    #[DataProvider('notUuidValues')]
    public function uuidInputReturnsNullForNonUuid(mixed $value): void
    {
        $request = $this->makeRequest($value === null ? [] : ['author_id' => $value]);

        $this->assertNull($request->readInput('author_id'));
    }

    /** {@link ReadsUuidInput::uuidRouteParameter()} returns a UUID route parameter unchanged. */
    #[Test]
    public function uuidRouteParameterReturnsUuid(): void
    {
        $request = $this->makeRequest([], ['book_id' => self::UUID]);

        $this->assertSame(self::UUID, $request->readRouteParameter('book_id'));
    }

    /** {@link ReadsUuidInput::uuidRouteParameter()} returns null for a missing or non-UUID route parameter. */
    #[Test]
    public function uuidRouteParameterReturnsNullForNonUuid(): void
    {
        $this->assertNull($this->makeRequest([], ['book_id' => 'abc'])->readRouteParameter('book_id'));
        $this->assertNull($this->makeRequest([], [])->readRouteParameter('book_id'));
        $this->assertNull($this->makeRequest([])->readRouteParameter('book_id'));
    }

    /**
     * Make a form request that uses the trait and exposes its methods.
     *
     * @param array<string, mixed>       $input       The request input.
     * @param array<string, string>|null $routeParams The route parameters, or null for a request with no route.
     *
     * @return FormRequest A request with <code>readInput()</code> and <code>readRouteParameter()</code>.
     */
    private function makeRequest(array $input, ?array $routeParams = null): FormRequest
    {
        $request = new class extends FormRequest {
            use ReadsUuidInput;

            /**
             * Expose {@link ReadsUuidInput::uuidInput()}.
             *
             * @param string $key The input key.
             *
             * @return string|null The UUID, or null.
             */
            public function readInput(string $key): ?string
            {
                return $this->uuidInput($key);
            }

            /**
             * Expose {@link ReadsUuidInput::uuidRouteParameter()}.
             *
             * @param string $name The route parameter name.
             *
             * @return string|null The UUID, or null.
             */
            public function readRouteParameter(string $name): ?string
            {
                return $this->uuidRouteParameter($name);
            }
        };
        $request->initialize($input);

        if ($routeParams !== null) {
            $route = new Route('PUT', 'books/{book_id}', []);
            $route->parameters = $routeParams;
            $request->setRouteResolver(static fn () => $route);
        }

        return $request;
    }
}
