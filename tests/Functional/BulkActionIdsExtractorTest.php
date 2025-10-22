<?php

require __DIR__ . '/../../vendor/autoload.php';

use Everblock\PrestaShopBundle\Service\EverBlockBulkActionIdsExtractor;

function expectTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$extractor = new EverBlockBulkActionIdsExtractor('ever_block');

$ids = $extractor->extractFromArray([
    'grid' => [
        'ever_block' => [
            'bulk_action' => [
                'ids' => ['1', '2', 'foo', 3],
            ],
        ],
    ],
]);

expectTrue($ids === [1, 2, 3], 'Failed to extract ids from grid payload');

$ids = $extractor->extractFromArray([
    'ever_block' => [
        'bulk_action' => [
            'ids' => [10, '11'],
        ],
    ],
]);

expectTrue($ids === [10, 11], 'Failed to extract ids from direct bulk payload');

$ids = $extractor->extractFromArray([
    'ids' => ['not', 'numeric'],
]);

expectTrue($ids === [], 'Non numeric identifiers should be ignored');

echo "BulkActionIdsExtractorTest passed\n";
