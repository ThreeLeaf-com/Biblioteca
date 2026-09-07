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
    $jsonContent = $openApi->toJson();
    file_put_contents($targetDirectory . '/api-docs.json', $jsonContent);

    $pathCount = is_countable($openApi->paths) ? count($openApi->paths) : 0;
    $schemaCount = is_countable($openApi->components?->schemas ?? null) ? count($openApi->components->schemas) : 0;

    echo "\nSwagger documentation generated successfully.\n\n";
    echo "Scanned paths: \n* " . implode("\n* ", $paths) . "\n\n";
    echo 'Number of paths found: ' . $pathCount . "\n";
    echo 'Number of schemas found: ' . $schemaCount . "\n\n";

    /* An empty document means the annotations were not read at all. That is a build failure,
     * not a success with nothing to say: see issue #20, where swagger-php silently stopped
     * reading docblock annotations and every build kept passing on `{"openapi":"3.0.0"}`.
     */
    if ($pathCount === 0 || $schemaCount === 0) {
        fwrite(STDERR, "The generated OpenAPI document is empty: $pathCount paths, $schemaCount schemas.\n");
        fwrite(STDERR, "Expected the #[OA\\...] attributes in src/ to produce both.\n\n");
        exit(1);
    }
} catch (Throwable $e) {
    fwrite(STDERR, "\nError generating Swagger documentation: " . $e->getMessage() . "\n\n");
    exit(1);
}
