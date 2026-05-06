<?php

declare(strict_types=1);

define('_PS_VERSION_', '9.0.0');

function everblockNormalizePath(string $path): string
{
    return str_replace('\\', '/', trim($path));
}

function everblockForbiddenReleasePatterns(): array
{
    return [
        '/(^|\/)\.env(\.|$)/i',
        '/(^|\/)\.php-cs-fixer\.cache$/i',
        '/(^|\/)composer\.phar$/i',
        '/\.(sql|sqlite|sqlite3|log|bak|backup|pem|key|p12|pfx|ppk)$/i',
        '/(^|\/)(cache|tmp|logs?|backup|backups)\//i',
        '/^views\/templates\/hook\/generated_wp_posts\/.*\.json$/i',
    ];
}

function everblockIsForbiddenReleaseFile(string $path): bool
{
    $path = everblockNormalizePath($path);
    foreach (everblockForbiddenReleasePatterns() as $pattern) {
        if (preg_match($pattern, $path)) {
            return true;
        }
    }

    return false;
}

function everblockAllowedFiles(string $root): array
{
    $allowedFile = $root . '/config/allowed_files.php';
    if (!is_file($allowedFile)) {
        fwrite(STDERR, "Missing release allowlist: config/allowed_files.php\n");
        exit(1);
    }

    $allowedFiles = include $allowedFile;
    if (!is_array($allowedFiles)) {
        fwrite(STDERR, "Invalid release allowlist: config/allowed_files.php\n");
        exit(1);
    }

    $allowedFiles = array_values(array_unique(array_map('everblockNormalizePath', $allowedFiles)));
    sort($allowedFiles);

    return $allowedFiles;
}

function everblockTrackedFiles(string $root): array
{
    $cwd = getcwd();
    chdir($root);
    $tracked = shell_exec('git ls-files -z');
    chdir($cwd);

    if (!is_string($tracked)) {
        fwrite(STDERR, "Unable to list tracked files.\n");
        exit(1);
    }

    return array_values(array_filter(array_map('everblockNormalizePath', explode("\0", $tracked))));
}
