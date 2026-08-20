<?php

/**
 * 2019-2025 Team Ever
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Team Ever <https://www.team-ever.com/>
 *  @copyright 2019-2025 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

declare(strict_types=1);

namespace Everblock\Tools\Service;

use DOMDocument;
use ZipArchive;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Server side validation of every file the module writes on disk.
 *
 * Two rules drive this class:
 *
 *  1. The destination extension is decided by the SERVER, from the real content of the file,
 *     never taken from the name supplied by the client. Sanitising the file name is not enough:
 *     "shell.php" sanitises to "shell.php".
 *  2. Extensions that a web server may interpret are refused unconditionally, whatever the
 *     detected content type and whatever the profile allows.
 *
 * The .htaccess written next to the uploads is only a secondary Apache-specific measure; it is
 * useless behind nginx, which is precisely why the whitelist above has to be sufficient on its own.
 */
final class EverblockUploadGuard
{
    /** Raster images only. */
    public const PROFILE_IMAGE = 'image';

    /** SVG only (store locator marker). */
    public const PROFILE_VECTOR = 'vector';

    /** Product modal attachment: image, vector, video or PDF (see views/templates/front/modal.tpl). */
    public const PROFILE_MODAL = 'modal';

    /** Product tabs import file. */
    public const PROFILE_SPREADSHEET = 'spreadsheet';

    /**
     * Business need per upload surface, expressed as server side extensions.
     *
     * @var array<string, string[]>
     */
    private const PROFILES = [
        self::PROFILE_IMAGE => ['jpg', 'png', 'gif', 'webp', 'avif'],
        self::PROFILE_VECTOR => ['svg'],
        self::PROFILE_MODAL => ['jpg', 'png', 'gif', 'webp', 'avif', 'svg', 'mp4', 'webm', 'ogv', 'pdf'],
        self::PROFILE_SPREADSHEET => ['xlsx'],
    ];

    /**
     * Extensions that must never be written in a directory served by the web server, whatever
     * the detected content type is. Kept deliberately wider than "PHP only": .html and .js in an
     * upload directory are a stored XSS, .htaccess is a configuration takeover.
     *
     * @var string[]
     */
    private const FORBIDDEN_EXTENSIONS = [
        'php', 'php2', 'php3', 'php4', 'php5', 'php6', 'php7', 'php8', 'phps', 'phpt',
        'phtml', 'phtm', 'pht', 'phar', 'inc', 'module',
        'htaccess', 'htpasswd', 'hta', 'ini', 'user',
        'cgi', 'fcgi', 'pl', 'py', 'rb', 'sh', 'bash', 'exe', 'com', 'bat', 'cmd', 'dll', 'so',
        'jsp', 'jspx', 'jsw', 'jsv', 'asp', 'aspx', 'asa', 'asax', 'ascx', 'ashx', 'asmx', 'cer',
        'shtml', 'shtm', 'stm', 'htm', 'html', 'xhtml', 'xht', 'js', 'mjs', 'swf', 'svgz', 'xml',
    ];

    /**
     * MIME type reported by finfo => canonical server side extension.
     *
     * @var array<string, string>
     */
    private const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'image/avif' => 'avif',
        'image/svg+xml' => 'svg',
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/ogg' => 'ogv',
        'application/pdf' => 'pdf',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
    ];

    /**
     * Extension a real image decode maps to, per IMAGETYPE_* constant. Authoritative for raster
     * images: unlike finfo it actually reads the image header.
     *
     * @return array<int, string>
     */
    private static function imageTypeExtensions(): array
    {
        $map = [
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_GIF => 'gif',
        ];

        if (defined('IMAGETYPE_WEBP')) {
            $map[IMAGETYPE_WEBP] = 'webp';
        }
        if (defined('IMAGETYPE_AVIF')) {
            $map[IMAGETYPE_AVIF] = 'avif';
        }

        return $map;
    }

    /**
     * @return string[] Extensions the given profile accepts
     */
    public static function allowedExtensions(string $profile): array
    {
        return self::PROFILES[$profile] ?? [];
    }

    /**
     * True for any extension a web server could interpret or that could hijack its configuration.
     */
    public static function isForbiddenExtension(string $extension): bool
    {
        $extension = strtolower(trim($extension, ". \t\n\r\0\x0B"));

        if ($extension === '') {
            return true;
        }

        return in_array($extension, self::FORBIDDEN_EXTENSIONS, true);
    }

    /**
     * Extension the file must be stored with, or null when the file has to be refused.
     *
     * $originalName is only used to disambiguate content types finfo cannot tell apart (SVG,
     * XLSX) and to refuse dangerous claimed extensions early. It never ends up in the result.
     */
    public static function resolveSafeExtension(string $sourcePath, string $originalName, string $profile): ?string
    {
        $allowed = self::allowedExtensions($profile);
        if ($allowed === []) {
            return null;
        }

        if ($sourcePath === '' || !is_file($sourcePath) || filesize($sourcePath) === 0) {
            return null;
        }

        // Defence in depth: a client claiming an interpretable extension is refused outright,
        // even if the content happens to look like a valid image.
        $claimed = self::claimedExtension($originalName);
        if ($claimed !== '' && self::isForbiddenExtension($claimed)) {
            return null;
        }

        $extension = self::detectExtension($sourcePath, $claimed, $allowed);

        if ($extension === null
            || self::isForbiddenExtension($extension)
            || !in_array($extension, $allowed, true)
        ) {
            return null;
        }

        return $extension;
    }

    /**
     * Builds a collision-free file name from a readable slug and the server side extension.
     */
    public static function buildSafeFileName(string $originalName, string $extension, string $fallbackSlug = 'file'): string
    {
        $slug = self::slugify(pathinfo(self::baseName($originalName), PATHINFO_FILENAME));
        if ($slug === '') {
            $slug = self::slugify($fallbackSlug);
        }
        if ($slug === '') {
            $slug = 'file';
        }

        return $slug . '.' . strtolower($extension);
    }

    /**
     * Same as buildSafeFileName() with a random suffix, when overwriting must be impossible.
     */
    public static function buildUniqueFileName(string $originalName, string $extension, string $fallbackSlug = 'file'): string
    {
        $slug = self::slugify(pathinfo(self::baseName($originalName), PATHINFO_FILENAME));
        if ($slug === '') {
            $slug = self::slugify($fallbackSlug);
        }
        if ($slug === '') {
            $slug = 'file';
        }

        try {
            $suffix = bin2hex(random_bytes(4));
        } catch (\Exception $exception) {
            $suffix = substr(sha1($slug . microtime(false)), 0, 8);
        }

        return $slug . '-' . $suffix . '.' . strtolower($extension);
    }

    /**
     * Best effort Apache hardening of an upload directory. Deliberately limited to the same
     * directives as the module own .htaccess, and never overwrites an existing file.
     *
     * This is a secondary measure: it does nothing on nginx, and the extension whitelist above
     * is what actually prevents an executable file from being written.
     */
    public static function protectDirectory(string $directory): void
    {
        $directory = rtrim($directory, '/\\');
        if ($directory === '' || !is_dir($directory)) {
            return;
        }

        $htaccess = $directory . DIRECTORY_SEPARATOR . '.htaccess';
        if (file_exists($htaccess)) {
            return;
        }

        $rules = "# Generated by the everblock module: refuse to serve interpretable files.\n"
            . "# Apache only, and only when AllowOverride permits it. The module also refuses to\n"
            . "# write such files in the first place, which is the actual protection.\n"
            . "<IfModule !mod_authz_core.c>\n"
            . "    <FilesMatch \"\\.(php[0-9]?|phtml|phar|pht|inc|cgi|pl|py|sh|htaccess|html?|js|xml|yml|sql|log)$\">\n"
            . "        Order allow,deny\n"
            . "        Deny from all\n"
            . "    </FilesMatch>\n"
            . "</IfModule>\n"
            . "<IfModule mod_authz_core.c>\n"
            . "    <FilesMatch \"\\.(php[0-9]?|phtml|phar|pht|inc|cgi|pl|py|sh|htaccess|html?|js|xml|yml|sql|log)$\">\n"
            . "        Require all denied\n"
            . "    </FilesMatch>\n"
            . "</IfModule>\n";

        @file_put_contents($htaccess, $rules);
    }

    /**
     * basename() that also treats the Windows separator as one, so a name crafted with
     * backslashes cannot smuggle a path segment through.
     */
    private static function baseName(string $name): string
    {
        return basename(str_replace('\\', '/', $name));
    }

    private static function claimedExtension(string $originalName): string
    {
        return strtolower((string) pathinfo(self::baseName($originalName), PATHINFO_EXTENSION));
    }

    /**
     * Content driven detection, from the most to the least trustworthy source.
     */
    private static function detectExtension(string $sourcePath, string $claimed, array $allowed): ?string
    {
        // 1. Real image decode: authoritative for raster images.
        $imageExtension = self::detectRasterImage($sourcePath);
        if ($imageExtension !== null) {
            return $imageExtension;
        }

        // 2. finfo MIME type.
        $mime = self::detectMimeType($sourcePath);
        if ($mime !== '' && isset(self::MIME_EXTENSIONS[$mime])) {
            $candidate = self::MIME_EXTENSIONS[$mime];
            // finfo alone must not be trusted to declare an SVG: text based formats are easily
            // confused. Confirm with the XML probe below instead.
            if ($candidate !== 'svg') {
                return $candidate;
            }
        }

        // 3. SVG: finfo commonly reports text/xml, text/plain or text/html for SVG files, so the
        //    document is really parsed. Content sanitisation itself is handled separately.
        if (in_array('svg', $allowed, true) && $claimed === 'svg' && self::isSvgDocument($sourcePath)) {
            return 'svg';
        }

        // 4. XLSX: finfo reports application/zip for most spreadsheets, so the archive is opened.
        if (in_array('xlsx', $allowed, true) && $claimed === 'xlsx' && self::isXlsxArchive($sourcePath)) {
            return 'xlsx';
        }

        // 5. PDF: magic bytes, for the rare setups where finfo lacks the PDF signature.
        if (in_array('pdf', $allowed, true) && self::hasMagic($sourcePath, '%PDF-')) {
            return 'pdf';
        }

        return null;
    }

    private static function detectRasterImage(string $sourcePath): ?string
    {
        $size = @getimagesize($sourcePath);
        if (!is_array($size) || !isset($size[2])) {
            return null;
        }

        $map = self::imageTypeExtensions();
        $type = (int) $size[2];

        return $map[$type] ?? null;
    }

    private static function detectMimeType(string $sourcePath): string
    {
        if (!function_exists('finfo_open')) {
            return '';
        }

        $finfo = @finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return '';
        }

        $mime = @finfo_file($finfo, $sourcePath);
        finfo_close($finfo);

        return is_string($mime) ? strtolower($mime) : '';
    }

    private static function isSvgDocument(string $sourcePath): bool
    {
        if (!class_exists(DOMDocument::class)) {
            return false;
        }

        $content = @file_get_contents($sourcePath, false, null, 0, 2 * 1024 * 1024);
        if (!is_string($content) || $content === '' || stripos($content, '<svg') === false) {
            return false;
        }

        $previousErrors = libxml_use_internal_errors(true);
        $document = new DOMDocument();
        // LIBXML_NONET blocks network access; entity substitution stays off (no LIBXML_NOENT).
        $loaded = @$document->loadXML($content, LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);

        if (!$loaded || $document->documentElement === null) {
            return false;
        }

        return strtolower($document->documentElement->localName) === 'svg';
    }

    private static function isXlsxArchive(string $sourcePath): bool
    {
        if (!self::hasMagic($sourcePath, "PK\x03\x04")) {
            return false;
        }

        if (!class_exists(ZipArchive::class)) {
            return false;
        }

        $zip = new ZipArchive();
        if ($zip->open($sourcePath) !== true) {
            return false;
        }

        $isSpreadsheet = $zip->locateName('xl/workbook.xml') !== false
            || $zip->locateName('xl/workbook.bin') !== false;
        $zip->close();

        return $isSpreadsheet;
    }

    private static function hasMagic(string $sourcePath, string $magic): bool
    {
        $handle = @fopen($sourcePath, 'rb');
        if ($handle === false) {
            return false;
        }

        $read = fread($handle, strlen($magic));
        fclose($handle);

        return $read === $magic;
    }

    private static function slugify(string $value): string
    {
        $value = (string) preg_replace('/[^A-Za-z0-9]+/', '-', $value);
        $value = (string) preg_replace('/-{2,}/', '-', $value);
        $value = trim($value, '-');

        if (strlen($value) > 60) {
            $value = substr($value, 0, 60);
            $value = trim($value, '-');
        }

        return strtolower($value);
    }
}
