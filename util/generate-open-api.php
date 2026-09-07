<?php
/** @noinspection PhpUnused */

require_once __DIR__ . '/../vendor/autoload.php';

use OpenApi\Attributes as OA;
use OpenApi\Generator;
use OpenApi\SourceFinder;

$paths = [
    __FILE__,
    __DIR__ . '/../src',
];

#[OA\Info(title: 'Generated API', version: '1.0')]
class OpenApiSpec
{
}

/* swagger-php arrives through the require-dev `darkaonline/l5-swagger`, so a `--no-dev`
 * install has no generator. That is not a build failure — there is nothing to generate
 * with. Every environment that is meant to run this gate installs the dev dependencies.
 */
if (!class_exists(Generator::class)) {
    echo "\nswagger-php is not installed (--no-dev); skipping OpenAPI generation.\n\n";
    exit(0);
}

try {
    $targetDirectory = __DIR__ . '/../target';
    if (!is_dir($targetDirectory)) {
        mkdir($targetDirectory, 0755, true);
    }
    $targetFile = $targetDirectory . '/api-docs.json';

    /* Remove the previous document before generating, so a failed run leaves no stale
     * artifact that reads as current.
     */
    if (is_file($targetFile)) {
        unlink($targetFile);
    }

    $openApi = (new Generator())->generate(new SourceFinder($paths));

    /* Absent members are the `OpenApi\Generator::UNDEFINED` sentinel — a string, not null —
     * so `?->` and `??` do not detect them. Test with `Generator::isDefault()`.
     */
    $pathCount = Generator::isDefault($openApi->paths) ? 0 : count($openApi->paths);
    $schemaCount = Generator::isDefault($openApi->components) || Generator::isDefault($openApi->components->schemas)
        ? 0
        : count($openApi->components->schemas);

    /* An empty document means the attributes were not read at all, and a document with no
     * `Info` block is not a valid specification. Both are build failures, not successes with
     * nothing to say: see issue #20, where swagger-php silently stopped reading the docblock
     * annotations and every build kept passing on `{"openapi":"3.0.0"}`.
     *
     * This runs before the document is written and before anything reports success, so a
     * failing run leaves no artifact at all rather than a plausible-looking one behind a
     * success banner.
     */
    if ($pathCount === 0 || $schemaCount === 0 || Generator::isDefault($openApi->info)) {
        fwrite(STDERR, "\nThe generated OpenAPI document is incomplete: $pathCount paths, $schemaCount schemas");
        fwrite(STDERR, Generator::isDefault($openApi->info) ? ", no Info block.\n" : ".\n");
        fwrite(STDERR, "Expected the #[OA\\...] attributes in src/ to produce paths and schemas,\n");
        fwrite(STDERR, "and the #[OA\\Info] attribute in this file to produce the Info block.\n\n");
        exit(1);
    }

    file_put_contents($targetFile, $openApi->toJson());

    echo "\nSwagger documentation generated successfully.\n\n";
    echo "Scanned paths: \n* " . implode("\n* ", $paths) . "\n\n";
    echo 'Number of paths found: ' . $pathCount . "\n";
    echo 'Number of schemas found: ' . $schemaCount . "\n\n";
} catch (Throwable $e) {
    fwrite(STDERR, "\nError generating Swagger documentation: " . $e->getMessage() . "\n\n");
    exit(1);
}
