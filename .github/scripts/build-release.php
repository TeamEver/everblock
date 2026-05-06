<?php

declare(strict_types=1);

require __DIR__ . '/release-files.php';

$root = dirname(__DIR__, 2);
$target = $argv[1] ?? ($root . '/build/everblock');
$target = rtrim(str_replace('\\', '/', $target), '/');

$devOnlyFiles = [
    '.gitignore',
    '.php-cs-fixer.php',
    'phpstan.neon',
];

if (is_dir($target)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($target, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }
    rmdir($target);
}

mkdir($target, 0755, true);

foreach (everblockAllowedFiles($root) as $file) {
    if (in_array($file, $devOnlyFiles, true) || everblockIsForbiddenReleaseFile($file)) {
        continue;
    }

    $source = $root . '/' . $file;
    if (!is_file($source)) {
        continue;
    }

    $destination = $target . '/' . $file;
    $destinationDir = dirname($destination);
    if (!is_dir($destinationDir)) {
        mkdir($destinationDir, 0755, true);
    }

    copy($source, $destination);
}

echo "Release package files copied to {$target}.\n";
