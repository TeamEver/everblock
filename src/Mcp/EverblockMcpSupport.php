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

namespace Everblock\Tools\Mcp;

use Everblock\Tools\Handler\DeleteAdminItemHandler;
use Everblock\Tools\Handler\SaveAdminItemHandler;
use Everblock\Tools\Repository\BlockRepository;
use Everblock\Tools\Repository\RepositoryProvider;
use Everblock\Tools\Service\ShortcodeDocumentationProvider;
use PrestaShop\Module\PsMcpServer\Server\Exceptions\PsMcpToolCallException;

/**
 * Outils communs aux classes d'outils MCP d'Ever Block : contexte (module, boutique, langue),
 * gestionnaires du back-office, résolution des produits, contrôle du HTML, purge des fiches.
 * Aucune méthode n'est publiée : seules les méthodes portant PsMcpTool le sont.
 */
trait EverblockMcpSupport
{
    private function module(): \Everblock
    {
        $module = \Module::getInstanceByName('everblock');
        if (!$module instanceof \Everblock) {
            throw new PsMcpToolCallException('Ever Block n\'est pas disponible.');
        }

        return $module;
    }

    private function shopId(): int
    {
        $shop = \Context::getContext()->shop;

        return $shop instanceof \Shop && (int) $shop->id > 0 ? (int) $shop->id : (int) \Configuration::get('PS_SHOP_DEFAULT');
    }

    private function blockRepository(): BlockRepository
    {
        /** @var BlockRepository $repository */
        $repository = RepositoryProvider::get('everblock.repository.block');

        return $repository;
    }

    private function saveHandler(): SaveAdminItemHandler
    {
        return new SaveAdminItemHandler(
            $this->blockRepository(),
            RepositoryProvider::get('everblock.repository.shortcode'),
            RepositoryProvider::get('everblock.repository.page'),
            RepositoryProvider::get('everblock.repository.hook')
        );
    }

    private function resolveLang(string $lang, bool $strict = false): int
    {
        $lang = trim($lang);
        if ($lang === '') {
            $current = \Context::getContext()->language;

            return $current instanceof \Language && (int) $current->id > 0 ? (int) $current->id : (int) \Configuration::get('PS_LANG_DEFAULT');
        }
        $langId = ctype_digit($lang) ? (int) $lang : (int) \Language::getIdByIso($lang);
        if ($langId <= 0 || !\Language::getIsoById($langId)) {
            if ($strict) {
                throw new PsMcpToolCallException(sprintf('Langue « %s » inconnue (code ISO ou ID).', $lang));
            }

            return (int) \Configuration::get('PS_LANG_DEFAULT');
        }

        return $langId;
    }

    private function date(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' || str_starts_with($value, '0000-00-00') ? null : $value;
    }

    private function excerpt(string $html): string
    {
        $text = trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));

        return mb_strlen($text) > 160 ? mb_substr($text, 0, 157) . '...' : $text;
    }

    /**
     * @return list<array{shortcode: string, title: string}>
     */
    private function customShortcodes(): array
    {
        $rows = \Db::getInstance()->executeS(
            'SELECT s.shortcode, sl.title FROM ' . _DB_PREFIX_ . 'everblock_shortcode s
             LEFT JOIN ' . _DB_PREFIX_ . 'everblock_shortcode_lang sl
                ON sl.id_everblock_shortcode = s.id_everblock_shortcode AND sl.id_lang = ' . $this->resolveLang('') . '
             WHERE s.id_shop = ' . $this->shopId() . '
             ORDER BY s.shortcode'
        ) ?: [];

        return array_map(static fn (array $r): array => ['shortcode' => (string) $r['shortcode'], 'title' => (string) $r['title']], $rows);
    }

    /**
     * @return array<string, true> noms de shortcodes connus (documentés ou personnalisés), en minuscules
     */
    private function knownShortcodes(): array
    {
        $known = [];
        $collect = static function (string $code) use (&$known): void {
            if (preg_match('/^\[\/?([a-z][a-z0-9_-]*)/i', trim($code), $match)) {
                $known[mb_strtolower($match[1])] = true;
            }
        };
        try {
            foreach (ShortcodeDocumentationProvider::getDocumentation($this->module()) as $family) {
                foreach ((array) ($family['entries'] ?? []) as $entry) {
                    $collect((string) ($entry['code'] ?? ''));
                }
            }
        } catch (\Throwable) {
            // documentation indisponible : seuls les shortcodes personnalisés sont connus
        }
        foreach ($this->customShortcodes() as $row) {
            $collect($row['shortcode']);
        }

        return $known;
    }

    private function deleteHandler(): DeleteAdminItemHandler
    {
        return new DeleteAdminItemHandler(
            $this->blockRepository(),
            RepositoryProvider::get('everblock.repository.shortcode'),
            RepositoryProvider::get('everblock.repository.page'),
            RepositoryProvider::get('everblock.repository.hook')
        );
    }

    /**
     * Valeurs par langue {iso|id: texte} vers [id_lang => texte], langues contrôlées.
     *
     * @param array<string|int, mixed>|null $values
     *
     * @return array<int, string>
     */
    private function localizedInput(?array $values, bool $html = true): array
    {
        $out = [];
        foreach ((array) $values as $langKey => $value) {
            $value = (string) $value;
            if ($html && $value !== '' && !\Validate::isCleanHtml($value, (bool) \Configuration::get('PS_ALLOW_HTML_IFRAME'))) {
                throw new PsMcpToolCallException(sprintf('Contenu refusé (%s) : HTML non autorisé par PrestaShop (script, iframe ou attribut d\'événement).', (string) $langKey));
            }
            $out[$this->resolveLang((string) $langKey, true)] = $value;
        }

        return $out;
    }

    /**
     * @param array<int, mixed> $values [id_lang => valeur]
     *
     * @return array<string, string> [iso => valeur]
     */
    private function byIso(array $values): array
    {
        $out = [];
        foreach ($values as $langId => $value) {
            $iso = \Language::getIsoById((int) $langId);
            $out[$iso ?: (string) $langId] = (string) $value;
        }

        return $out;
    }

    /**
     * Produit par ID, référence produit ou référence de déclinaison (une seule correspondance).
     *
     * @return array{id: int, reference: string, name: string}
     */
    private function resolveProduct(string $value): array
    {
        $value = trim($value);
        if ($value === '') {
            throw new PsMcpToolCallException('Produit manquant.');
        }
        $db = \Db::getInstance();
        $ids = [];
        if (ctype_digit($value)) {
            $ids = [(int) $db->getValue('SELECT id_product FROM ' . _DB_PREFIX_ . 'product WHERE id_product = ' . (int) $value)];
        }
        $ids = array_filter($ids);
        if ($ids === []) {
            foreach (['product', 'product_attribute'] as $table) {
                $rows = $db->executeS('SELECT DISTINCT id_product FROM ' . _DB_PREFIX_ . $table . ' WHERE reference = "' . pSQL($value) . '"') ?: [];
                $ids = array_map(static fn (array $r): int => (int) $r['id_product'], $rows);
                if ($ids !== []) {
                    break;
                }
            }
        }
        if ($ids === []) {
            throw new PsMcpToolCallException(sprintf('Produit « %s » introuvable.', $value));
        }
        if (count($ids) > 1) {
            throw new PsMcpToolCallException(sprintf('Référence « %s » portée par plusieurs produits : utiliser l\'ID.', $value));
        }
        $id = (int) reset($ids);
        $row = $db->getRow(
            'SELECT p.reference, pl.name FROM ' . _DB_PREFIX_ . 'product p
             LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON pl.id_product = p.id_product
                AND pl.id_lang = ' . $this->resolveLang('') . ' AND pl.id_shop = ' . $this->shopId() . '
             WHERE p.id_product = ' . $id
        ) ?: [];

        return ['id' => $id, 'reference' => (string) ($row['reference'] ?? ''), 'name' => (string) ($row['name'] ?? '')];
    }

    /**
     * Fiches produit dont le contenu Ever Block a changé : purge du cache HTTP par les hooks
     * qu'écoute qcdvarnish (sans effet si aucun module ne les écoute).
     *
     * @param int[] $productIds
     */
    private function purgeProductPages(array $productIds): void
    {
        $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds))));
        if ($productIds === []) {
            return;
        }
        try {
            if (count($productIds) <= 50) {
                \Hook::exec('actionPurgeMultipleProductsCache', ['product_ids' => $productIds]);
            } else {
                \Hook::exec('actionClearCache');
            }
        } catch (\Throwable $exception) {
            \PrestaShopLogger::addLog('everblock MCP: purge: ' . $exception->getMessage(), 2);
        }
    }
}
