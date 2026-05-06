<?php

declare(strict_types=1);

require __DIR__ . '/release-files.php';

$root = dirname(__DIR__, 2);
$trackedFiles = everblockTrackedFiles($root);
$forbiddenFiles = array_values(array_filter(
    $trackedFiles,
    static function (string $file) use ($root): bool {
        return is_file($root . '/' . $file) && everblockIsForbiddenReleaseFile($file);
    }
));

if ($forbiddenFiles) {
    fwrite(STDERR, "Forbidden files are tracked and must not be released:\n");
    foreach ($forbiddenFiles as $file) {
        fwrite(STDERR, ' - ' . $file . "\n");
    }
    exit(1);
}

$moduleContent = file_get_contents($root . '/everblock.php');
if (!is_string($moduleContent) || !preg_match("/\\\$this->version\\s*=\\s*'([^']+)'/", $moduleContent, $moduleMatch)) {
    fwrite(STDERR, "Unable to read module version from everblock.php\n");
    exit(1);
}

$configFile = $root . '/config_fr.xml';
if (is_file($configFile)) {
    $configContent = file_get_contents($configFile);
    if (
        is_string($configContent)
        && preg_match('/<version><!\[CDATA\[([^]]+)\]\]><\/version>/', $configContent, $configMatch)
        && $configMatch[1] !== $moduleMatch[1]
    ) {
        fwrite(STDERR, "Version mismatch: everblock.php is {$moduleMatch[1]}, config_fr.xml is {$configMatch[1]}\n");
        exit(1);
    }
}

everblockAllowedFiles($root);

echo "Release validation passed for everblock {$moduleMatch[1]}.\n";
