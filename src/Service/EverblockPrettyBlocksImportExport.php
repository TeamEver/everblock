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

namespace Everblock\Tools\Service;

use Db;
use Exception;
use Module;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

class EverblockPrettyBlocksImportExport
{
    public function exportPrettyblocks(Module $module, array &$errors): void
    {
        $hookSelection = Tools::getValue('EVERBLOCK_PRETTYBLOCKS_HOOK');
        if (!$hookSelection) {
            $errors[] = $module->l('Please select a hook to export.');
            return;
        }

        $hookField = $this->resolvePrettyblocksHookField();
        if (!$hookField) {
            $errors[] = $module->l('PrettyBlocks table is missing hook information.');
            return;
        }

        $db = Db::getInstance();
        $hookName = '';
        if ($hookField === 'id_hook') {
            $hookId = (int) $hookSelection;
            $hookName = (string) $db->getValue(
                'SELECT name FROM `' . _DB_PREFIX_ . 'hook` WHERE id_hook = ' . (int) $hookId
            );
            $rows = $db->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'prettyblocks` WHERE id_hook = ' . (int) $hookId
            );
        } else {
            $hookName = (string) $hookSelection;
            $rows = $db->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'prettyblocks` WHERE hook = "' . pSQL($hookSelection) . '"'
            );
        }

        if (empty($rows)) {
            $errors[] = $module->l('No PrettyBlocks found for the selected hook.');
            return;
        }

        $payload = [
            'meta' => [
                'exported_at' => date('c'),
                'base_url' => Tools::getHttpHost(true) . __PS_BASE_URI__,
                'hook' => $hookName,
            ],
            'prettyblocks' => $rows,
        ];

        $safeHook = $hookName ?: 'hook';
        $safeHook = preg_replace('/[^a-zA-Z0-9\-_]+/', '-', (string) $safeHook);
        $filename = sprintf('prettyblocks-%s-%s.json', $safeHook, date('Y-m-d'));

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function importPrettyblocks(Module $module, array &$errors, array &$success): void
    {
        if (!isset($_FILES['PRETTYBLOCKS_IMPORT_FILE'])
            || empty($_FILES['PRETTYBLOCKS_IMPORT_FILE']['tmp_name'])
        ) {
            $errors[] = $module->l('Please select a JSON file to import.');
            return;
        }

        $filename = $_FILES['PRETTYBLOCKS_IMPORT_FILE']['name'];
        $exploded = explode('.', (string) $filename);
        $ext = Tools::strtolower((string) end($exploded));
        if ($ext !== 'json') {
            $errors[] = $module->l('Error: file is not a valid JSON export.');
            return;
        }

        $tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS');
        if (!$tmpName || !move_uploaded_file($_FILES['PRETTYBLOCKS_IMPORT_FILE']['tmp_name'], $tmpName)) {
            $errors[] = $module->l('Unable to upload the JSON file.');
            return;
        }

        $content = file_get_contents($tmpName);
        if ($content === false) {
            $errors[] = $module->l('Unable to read the JSON file.');
            return;
        }
        unlink($tmpName);

        $payload = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $errors[] = $module->l('The JSON file is not valid.');
            return;
        }

        $rows = $payload['prettyblocks'] ?? $payload;
        if (!is_array($rows)) {
            $errors[] = $module->l('The JSON file does not contain PrettyBlocks data.');
            return;
        }

        $sourceBaseUrl = is_array($payload) ? ($payload['meta']['base_url'] ?? null) : null;
        $currentBaseUrl = Tools::getHttpHost(true) . __PS_BASE_URI__;
        $columns = $this->getPrettyblocksTableColumns();
        if (empty($columns)) {
            $errors[] = $module->l('PrettyBlocks table is not available.');
            return;
        }

        $inserted = 0;
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $data = array_intersect_key($row, array_flip($columns));
            unset($data['id_prettyblocks']);

            if ($sourceBaseUrl) {
                foreach ($data as $key => $value) {
                    if (is_string($value)) {
                        $data[$key] = str_replace($sourceBaseUrl, $currentBaseUrl, $value);
                    }
                }
            }

            if (array_key_exists('state', $data)) {
                $state = is_array($data['state']) ? $data['state'] : json_decode((string) $data['state'], true);
                if (is_array($state)) {
                    $state = $this->preparePrettyblocksStateForImport($state, $sourceBaseUrl);
                    $data['state'] = pSQL(json_encode($state), true);
                }
            }

            if (array_key_exists('date_add', $data)) {
                $data['date_add'] = date('Y-m-d H:i:s');
            }
            if (array_key_exists('date_upd', $data)) {
                $data['date_upd'] = date('Y-m-d H:i:s');
            }

            if (Db::getInstance()->insert('prettyblocks', $data)) {
                $inserted++;
            }
        }

        if ($inserted === 0) {
            $errors[] = $module->l('No PrettyBlocks entries were imported.');
            return;
        }

        $success[] = sprintf(
            $module->l('%d PrettyBlocks entries have been imported.'),
            $inserted
        );
    }

    public function getPrettyblocksHookOptions(): array
    {
        $db = Db::getInstance();
        $hookField = $this->resolvePrettyblocksHookField();
        if (!$hookField) {
            return [];
        }

        if ($hookField === 'id_hook') {
            return $db->executeS(
                'SELECT h.id_hook AS id, h.name AS name, COUNT(*) AS total'
                . ' FROM `' . _DB_PREFIX_ . 'prettyblocks` a'
                . ' INNER JOIN `' . _DB_PREFIX_ . 'hook` h ON h.id_hook = a.id_hook'
                . ' GROUP BY h.id_hook, h.name'
                . ' HAVING total > 0'
                . ' ORDER BY h.name ASC'
            ) ?: [];
        }

        return $db->executeS(
            'SELECT a.hook AS id, a.hook AS name, COUNT(*) AS total'
            . ' FROM `' . _DB_PREFIX_ . 'prettyblocks` a'
            . ' WHERE a.hook IS NOT NULL AND a.hook != ""'
            . ' GROUP BY a.hook'
            . ' HAVING total > 0'
            . ' ORDER BY a.hook ASC'
        ) ?: [];
    }

    public function movePrettyblocksMedias(array $state, string $destinationDir): array
    {
        return $this->moveMediasRecursive($state, $destinationDir);
    }

    private function resolvePrettyblocksHookField(): ?string
    {
        $columns = $this->getPrettyblocksTableColumns();
        if (in_array('id_hook', $columns, true)) {
            return 'id_hook';
        }
        if (in_array('hook', $columns, true)) {
            return 'hook';
        }

        return null;
    }

    private function getPrettyblocksTableColumns(): array
    {
        $db = Db::getInstance();
        try {
            $columns = $db->executeS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'prettyblocks`');
        } catch (Exception $e) {
            return [];
        }

        $names = [];
        foreach ($columns as $column) {
            if (!empty($column['Field'])) {
                $names[] = $column['Field'];
            }
        }

        return $names;
    }

    private function preparePrettyblocksStateForImport(array $state, ?string $sourceBaseUrl = null): array
    {
        $currentBaseUrl = Tools::getHttpHost(true) . __PS_BASE_URI__;
        if ($sourceBaseUrl) {
            $state = $this->replaceUrlsRecursively(
                $state,
                rtrim($sourceBaseUrl, '/'),
                rtrim($currentBaseUrl, '/')
            );
        }

        $destinationDir = _PS_IMG_DIR_ . 'cms/prettyblocks/';
        if (!is_dir($destinationDir)) {
            @mkdir($destinationDir, 0755, true);
        }

        return $this->moveMediasRecursive($state, $destinationDir);
    }

    private function replaceUrlsRecursively($data, $oldUrl, $newUrl)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->replaceUrlsRecursively($value, $oldUrl, $newUrl);
            }

            return $data;
        }

        if (is_string($data)) {
            return str_replace($oldUrl, $newUrl, $data);
        }

        return $data;
    }

    private function moveMediasRecursive(array $data, string $destinationDir): array
    {
        foreach ($data as &$item) {
            if (is_array($item)) {
                if (($item['type'] ?? null) === 'fileupload' && isset($item['value']['url'])) {
                    $item = $this->moveSingleMediaField($item, $destinationDir);
                } else {
                    $item = $this->moveMediasRecursive($item, $destinationDir);
                }
            }
        }

        return $data;
    }

    private function moveSingleMediaField(array $field, string $destinationDir): array
    {
        $url = $field['value']['url'] ?? '';
        if ($url) {
            $sourcePath = EverblockTools::urlToFilePath($url);
            $filename = $field['value']['filename'] ?? basename($sourcePath);
            $extension = strtolower($field['value']['extension'] ?? pathinfo($filename, PATHINFO_EXTENSION));
            $mimeType = strtolower($field['value']['mime'] ?? '');
            $isSvg = in_array($extension, ['svg', 'svg+xml', 'svgz'], true) || strpos($mimeType, 'image/svg') === 0;
            if (file_exists($sourcePath)) {
                if (!is_dir($destinationDir)) {
                    @mkdir($destinationDir, 0755, true);
                }
                $destinationPath = $destinationDir . $filename;
                if ($sourcePath !== $destinationPath) {
                    @rename($sourcePath, $destinationPath);
                }
                $publicUrl = Tools::getHttpHost(true) . __PS_BASE_URI__ . 'img/cms/prettyblocks/' . $filename;
                $field['value']['url'] = $publicUrl;
                $field['value']['filename'] = $filename;

                if ($isSvg) {
                    $field['value']['extension'] = 'svg';
                } else {
                    $webpUrl = EverblockTools::convertToWebP($publicUrl);
                    if ($webpUrl) {
                        $field['value']['url'] = $webpUrl;
                        $webpPath = parse_url($webpUrl, PHP_URL_PATH);
                        $field['value']['filename'] = $webpPath ? basename($webpPath) : basename($webpUrl);
                        $field['value']['extension'] = 'webp';
                    } elseif (!isset($field['value']['extension'])) {
                        $field['value']['extension'] = $extension ?: pathinfo($filename, PATHINFO_EXTENSION);
                    }
                }
            }
        }
        $field['path'] = '$/img/cms/prettyblocks/';

        return $field;
    }
}
