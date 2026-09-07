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

try {
    $targetDirectory = __DIR__ . '/../target';
    if (!is_dir($targetDirectory)) {
        mkdir($targetDirectory, 0755, true);
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
     * failing run does not leave a plausible-looking artifact behind a success banner.
     */
    if ($pathCount === 0 || $schemaCount === 0 || Generator::isDefault($openApi->info)) {
        fwrite(STDERR, "\nThe generated OpenAPI document is incomplete: $pathCount paths, $schemaCount schemas");
        fwrite(STDERR, Generator::isDefault($openApi->info) ? ", no Info block.\n" : ".\n");
        fwrite(STDERR, "Expected the #[OA\\...] attributes in src/ to produce paths and schemas,\n");
        fwrite(STDERR, "and the #[OA\\Info] attribute in this file to produce the Info block.\n\n");
        exit(1);
    }

    file_put_contents($targetDirectory . '/api-docs.json', $openApi->toJson());

    echo "\nSwagger documentation generated successfully.\n\n";
    echo "Scanned paths: \n* " . implode("\n* ", $paths) . "\n\n";
    echo 'Number of paths found: ' . $pathCount . "\n";
    echo 'Number of schemas found: ' . $schemaCount . "\n\n";
} catch (Throwable $e) {
    fwrite(STDERR, "\nError generating Swagger documentation: " . $e->getMessage() . "\n\n");
    exit(1);
}
