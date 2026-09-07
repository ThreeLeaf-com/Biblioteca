<?php

namespace Tests\Unit\OpenApi;

use OpenApi\Annotations\OpenApi;
use OpenApi\Generator;
use OpenApi\SourceFinder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Test that the package's `#[OA\...]` attributes still produce an OpenAPI document.
 *
 * Issue #20: swagger-php 6.7.1 reads docblock annotations only through
 * `doctrine/annotations`, which is not installed, so every `@OA\` docblock in the
 * package was inert and the generated document held nothing but `openapi: 3.0.0`.
 * The generator exited 0 either way, so nothing failed. These tests are the guard:
 * an empty document is a failure, whatever the reason for it.
 */
class OpenApiDocumentTest extends TestCase
{

    /** The document generated once for the whole class. */
    private static ?OpenApi $document = null;

    /**
     * Generate the document from the package source.
     *
     * Validation is off because `src/` alone carries no `OA\Info`: that lives in
     * `util/generate-open-api.php`, which cannot be scanned here without executing it.
     * The build script generates with validation on.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$document = (new Generator())->generate(new SourceFinder([__DIR__ . '/../../../src']), validate: false);
    }

    /** Release the generated document. */
    public static function tearDownAfterClass(): void
    {
        self::$document = null;

        parent::tearDownAfterClass();
    }

    /** The document declares operation paths. */
    #[Test]
    public function documentHasPaths()
    {
        $this->assertIsIterable(self::$document->paths, 'No paths were generated at all.');
        $this->assertNotEmpty(self::$document->paths, 'The generated document has zero paths.');
    }

    /** The document declares component schemas. */
    #[Test]
    public function documentHasSchemas()
    {
        $this->assertFalse(Generator::isDefault(self::$document->components), 'No schemas were generated at all.');
        $this->assertIsIterable(self::$document->components->schemas, 'No schemas were generated at all.');
        $this->assertNotEmpty(self::$document->components->schemas, 'The generated document has zero schemas.');
    }

    /**
     * A representative path from each layer is present.
     *
     * Paths come from the controllers, so this fails if the controller attributes stop being read.
     */
    #[Test]
    public function documentCoversTheResourceEndpoints()
    {
        $paths = array_map(static fn ($path) => $path->path, self::$document->paths);

        $this->assertContains('/api/books', $paths);
        $this->assertContains('/api/books/{book_id}', $paths);
        $this->assertContains('/api/authors', $paths);
        $this->assertContains('/api/chapters', $paths);
    }

    /**
     * A representative schema from each annotated layer is present.
     *
     * Schemas come from the models, form requests and API resources, so this fails if any one
     * of those groups of attributes stops being read.
     */
    #[Test]
    public function documentCoversTheModelsRequestsAndResources()
    {
        $schemas = array_map(static fn ($schema) => $schema->schema, self::$document->components->schemas);

        $this->assertContains('Book', $schemas, 'The model attributes were not read.');
        $this->assertContains('BookRequest', $schemas, 'The form request attributes were not read.');
        $this->assertContains('BookResource', $schemas, 'The API resource attributes were not read.');
    }

    /**
     * Path parameters are named after the entity key, never `id`.
     *
     * That is the package's own naming convention, recorded in the style conventions. A path
     * that breaks it splits the entity's operations across two entries in the document —
     * `AnnotationController::update()` published `/api/annotations/{id}` while every other
     * annotation operation published `/api/annotations/{annotation_id}` — so the two halves
     * of one resource are documented as unrelated. Nothing caught it for as long as the
     * document was empty. See issue #20.
     */
    #[Test]
    public function pathParametersAreNamedAfterTheEntityKey()
    {
        foreach (self::$document->paths as $path) {
            $this->assertStringNotContainsString(
                '{id}',
                $path->path,
                "$path->path uses {id}; path parameters are named after the entity key.",
            );
        }
    }

    /**
     * Every operation carries a tag, namespaced `Biblioteca/<Entity>`.
     *
     * swagger-php synthesises a description-less tag for an operation that references one no
     * class declares, and silently drops the declared tag nobody used. `LibraryController`
     * tagged its operation `Biblioteca` while declaring `Biblioteca/Library`, and lost its
     * own tag that way. Every controller declares its tag namespaced, so an un-namespaced tag
     * names one that does not exist. An operation with no tag at all is the adjacent failure:
     * it groups under no entity, and the sentinel swagger-php leaves in place of an absent
     * `tags` is a string, which iterates as nothing rather than failing.
     */
    #[Test]
    public function everyOperationTagIsNamespaced()
    {
        foreach (self::$document->paths as $path) {
            foreach ($path->operations() as $operation) {
                $this->assertFalse(
                    Generator::isDefault($operation->tags),
                    "$path->path has an operation with no tags.",
                );
                $this->assertNotEmpty($operation->tags, "$path->path has an operation with no tags.");

                foreach ($operation->tags as $tag) {
                    $this->assertStringStartsWith(
                        'Biblioteca/',
                        $tag,
                        "$path->path is tagged '$tag', which no controller declares.",
                    );
                }
            }
        }
    }
}
