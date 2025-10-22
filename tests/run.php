<?php

require __DIR__ . '/../vendor/autoload.php';

$tests = [
    __DIR__ . '/Functional/BulkActionIdsExtractorTest.php',
    __DIR__ . '/Functional/DuplicateEverBlockHandlerTest.php',
];

foreach ($tests as $testFile) {
    require $testFile;
}

echo "All functional tests executed successfully\n";
