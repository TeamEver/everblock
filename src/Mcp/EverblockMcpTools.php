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

use Everblock\Tools\Command\DeleteAdminItemCommand;
use Everblock\Tools\Command\SaveAdminItemCommand;
use Everblock\Tools\Entity\Block;
use Everblock\Tools\Handler\DeleteAdminItemHandler;
use Everblock\Tools\Handler\SaveAdminItemHandler;
use Everblock\Tools\Repository\BlockRepository;
use Everblock\Tools\Repository\RepositoryProvider;
use Everblock\Tools\Service\EverblockCache;
use Everblock\Tools\Service\EverblockPreviewBuilder;
use Everblock\Tools\Service\GithubReleaseChecker;
use Everblock\Tools\Service\ModuleUpdater;
use Everblock\Tools\Service\ShortcodeDocumentationProvider;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpSchema;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpTool;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpToolAnnotations;
use PrestaShop\Module\PsMcpServer\Server\Exceptions\PsMcpToolCallException;

/**
 * Outils MCP d'Ever Block : blocs HTML, hooks, shortcodes, aperçu, audit, cache.
 *
 * Attributs de ps_mcp_server : les outils sont publiés par le serveur MCP officiel de PrestaShop
 * comme par QCD MCP Server (qcdmcpserver), qui lit les mêmes attributs et applique en plus les
 * clés qcd_* de meta (droit back-office vérifié, prévalidation avant la file de validation). Noms
 * publiés : « everblock-<nom> ». Les attributs sont lus par réflexion : la classe reste chargeable
 * sans aucun des deux serveurs, et ses méthodes ne s'exécutent que pendant un appel MCP.
 *
 * Les écritures passent par les mêmes gestionnaires que le back-office (SaveAdminItemHandler,
 * DeleteAdminItemHandler) : mêmes champs, même conversion des images, même vidage de cache,
 * même enregistrement du module sur le hook choisi.
 */
final class EverblockMcpTools
{
    private const DEVICES = ['all' => 0, 'desktop' => 1, 'tablet' => 2, 'mobile' => 4];
    private const TARGET_LISTS = [
        'categories' => 'categories',
        'manufacturers' => 'manufacturers',
        'suppliers' => 'suppliers',
        'cmsCategories' => 'cms_categories',
        'groups' => 'groups',
    ];
    private const FLAGS = [
        'onlyHome' => 'only_home',
        'onlyCategory' => 'only_category',
        'onlyCategoryProduct' => 'only_category_product',
        'onlyManufacturer' => 'only_manufacturer',
        'onlySupplier' => 'only_supplier',
        'onlyCmsCategory' => 'only_cms_category',
        'addContainer' => 'add_container',
        'lazyload' => 'lazyload',
        'obfuscateLink' => 'obfuscate_link',
        'modal' => 'modal',
    ];
    private const TEXTS = [
        'cssClass' => 'css_class',
        'bootstrapClass' => 'bootstrap_class',
        'background' => 'background',
        'dataAttribute' => 'data_attribute',
    ];
    private const MAX_HTML = 100000;

    /* ------------------------------------------------------------------------------------
     * Lecture
     * ---------------------------------------------------------------------------------- */

    /**
     * @return array<string, mixed>
     */
    #[PsMcpTool(
        name: 'blocks_list',
        title: 'Ever Block : blocs HTML',
        description: 'Liste paginée des blocs HTML de la boutique : hook, position, statut (en ligne, programmé, expiré, inactif), appareil, dates de publication, extrait du contenu et langues renseignées. Filtres : hook (nom ou ID), statut, texte cherché dans le nom ou le contenu.',
        annotations: new PsMcpToolAnnotations(readOnlyHint: true, destructiveHint: false, idempotentHint: true, openWorldHint: false),
        meta: ['category' => 'content', 'qcd_permission' => 'AdminEverBlock:read']
    )]
    #[PsMcpSchema(
        properties: [
            'hook' => ['type' => 'string', 'description' => 'Nom ou ID du hook.'],
            'status' => ['type' => 'string', 'enum' => ['all', 'live', 'scheduled', 'expired', 'inactive'], 'default' => 'all'],
            'search' => ['type' => 'string', 'description' => 'Texte cherché dans le nom ou le contenu (toutes langues).'],
            'lang' => ['type' => 'string', 'description' => 'Langue de l\'extrait : code ISO (fr, en) ou ID. Défaut : langue du contexte.'],
            'page' => ['type' => 'integer', 'minimum' => 1, 'default' => 1],
            'perPage' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 200, 'default' => 50],
        ]
    )]
    public function blocksList(string $hook = '', string $status = 'all', string $search = '', string $lang = '', int $page = 1, int $perPage = 50): array
    {
        $shopId = $this->shopId();
        $langId = $this->resolveLang($lang);
        $where = ['b.id_shop = ' . $shopId];
        if (trim($hook) !== '') {
            $where[] = 'b.id_hook = ' . $this->resolveHook($hook)['id_hook'];
        }
        if (trim($search) !== '') {
            $like = pSQL('%' . trim($search) . '%', true);
            $where[] = '(b.name LIKE "' . $like . '" OR EXISTS (SELECT 1 FROM ' . _DB_PREFIX_ . 'everblock_lang s
                WHERE s.id_everblock = b.id_everblock AND (s.content LIKE "' . $like . '" OR s.custom_code LIKE "' . $like . '")))';
        }

        $rows = \Db::getInstance()->executeS(
            'SELECT b.id_everblock, b.name, b.id_hook, h.name AS hook_name, b.position, b.active, b.device, b.modal,
                    b.date_start, b.date_end, bl.content
             FROM ' . _DB_PREFIX_ . 'everblock b
             LEFT JOIN ' . _DB_PREFIX_ . 'hook h ON h.id_hook = b.id_hook
             LEFT JOIN ' . _DB_PREFIX_ . 'everblock_lang bl ON bl.id_everblock = b.id_everblock AND bl.id_lang = ' . $langId . '
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY h.name ASC, b.position ASC, b.id_everblock ASC'
        ) ?: [];

        $filled = $this->filledLanguages(array_map(static fn (array $r): int => (int) $r['id_everblock'], $rows));
        $items = [];
        foreach ($rows as $row) {
            $rowStatus = $this->status($row);
            if ($status !== 'all' && $rowStatus !== $status) {
                continue;
            }
            $items[] = [
                'id' => (int) $row['id_everblock'],
                'name' => (string) $row['name'],
                'hook' => ['id' => (int) $row['id_hook'], 'name' => (string) ($row['hook_name'] ?? '')],
                'position' => (int) $row['position'],
                'status' => $rowStatus,
                'device' => $this->deviceName((int) $row['device']),
                'modal' => (bool) $row['modal'],
                'dateStart' => $this->date($row['date_start']),
                'dateEnd' => $this->date($row['date_end']),
                'excerpt' => $this->excerpt((string) ($row['content'] ?? '')),
                'languagesWithContent' => $filled[(int) $row['id_everblock']] ?? [],
            ];
        }

        $page = max(1, $page);
        $perPage = max(1, min(200, $perPage));

        return [
            'total' => count($items),
            'page' => $page,
            'perPage' => $perPage,
            'items' => array_slice($items, ($page - 1) * $perPage, $perPage),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    #[PsMcpTool(
        name: 'block_get',
        title: 'Ever Block : détail d\'un bloc',
        description: 'Tous les réglages d\'un bloc HTML : contenu et code personnalisé par langue (clés ISO), hook, position, statut, appareil, dates, ciblage (accueil, catégories, fabricants, fournisseurs, catégories CMS, groupes clients), habillage (conteneur, classes, fond, attributs data), modale.',
        annotations: new PsMcpToolAnnotations(readOnlyHint: true, destructiveHint: false, idempotentHint: true, openWorldHint: false),
        meta: ['category' => 'content', 'qcd_permission' => 'AdminEverBlock:read']
    )]
    #[PsMcpSchema(properties: ['id' => ['type' => 'integer', 'minimum' => 1]], required: ['id'])]
    public function blockGet(int $id): array
    {
        return $this->describeBlock($this->loadBlock($id));
    }

    /**
     * @return array<string, mixed>
     */
    #[PsMcpTool(
        name: 'hooks_list',
        title: 'Ever Block : hooks d\'affichage',
        description: 'Hooks d\'affichage utilisables par les blocs, avec le nombre de blocs (actifs et total) et l\'indication que le module y est bien greffé. onlyUsed=true : seulement les hooks qui portent des blocs.',
        annotations: new PsMcpToolAnnotations(readOnlyHint: true, destructiveHint: false, idempotentHint: true, openWorldHint: false),
        meta: ['category' => 'content', 'qcd_permission' => 'AdminEverBlock:read']
    )]
    public function hooksList(string $search = '', bool $onlyUsed = false): array
    {
        $shopId = $this->shopId();
        $moduleId = (int) \Module::getModuleIdByName('everblock');
        $where = ['h.name NOT LIKE "action%"', 'h.name NOT LIKE "filter%"'];
        if (trim($search) !== '') {
            $like = pSQL('%' . trim($search) . '%');
            $where[] = '(h.name LIKE "' . $like . '" OR h.title LIKE "' . $like . '")';
        }

        $rows = \Db::getInstance()->executeS(
            'SELECT h.id_hook, h.name, h.title, h.description,
                    COUNT(b.id_everblock) AS blocks, COALESCE(SUM(b.active), 0) AS active_blocks,
                    MAX(hm.id_module IS NOT NULL) AS registered
             FROM ' . _DB_PREFIX_ . 'hook h
             LEFT JOIN ' . _DB_PREFIX_ . 'everblock b ON b.id_hook = h.id_hook AND b.id_shop = ' . $shopId . '
             LEFT JOIN ' . _DB_PREFIX_ . 'hook_module hm ON hm.id_hook = h.id_hook AND hm.id_module = ' . $moduleId . ' AND hm.id_shop = ' . $shopId . '
             WHERE ' . implode(' AND ', $where) . '
             GROUP BY h.id_hook
             ' . ($onlyUsed ? 'HAVING blocks > 0' : '') . '
             ORDER BY blocks DESC, h.name ASC'
        ) ?: [];

        return ['items' => array_map(static fn (array $r): array => [
            'id' => (int) $r['id_hook'],
            'name' => (string) $r['name'],
            'title' => (string) $r['title'],
            'description' => (string) $r['description'],
            'blocks' => (int) $r['blocks'],
            'activeBlocks' => (int) $r['active_blocks'],
            'moduleRegistered' => (bool) $r['registered'],
        ], $rows)];
    }

    /**
     * @return array<string, mixed>
     */
    #[PsMcpTool(
        name: 'shortcodes_doc',
        title: 'Ever Block : référence des shortcodes',
        description: 'Référence des shortcodes utilisables dans les blocs (syntaxe, description, paramètres), par famille, plus les shortcodes personnalisés de la boutique. À lire avant de rédiger un contenu qui en utilise. search filtre sur le code et la description.',
        annotations: new PsMcpToolAnnotations(readOnlyHint: true, destructiveHint: false, idempotentHint: true, openWorldHint: false),
        meta: ['category' => 'content', 'qcd_permission' => 'AdminEverBlock:read']
    )]
    public function shortcodesDoc(string $search = ''): array
    {
        $needle = mb_strtolower(trim($search));
        $families = [];
        foreach (ShortcodeDocumentationProvider::getDocumentation($this->module()) as $family) {
            $entries = [];
            foreach ((array) ($family['entries'] ?? []) as $entry) {
                $haystack = mb_strtolower((string) ($entry['code'] ?? '') . ' ' . (string) ($entry['description'] ?? ''));
                if ($needle !== '' && !str_contains($haystack, $needle)) {
                    continue;
                }
                $entries[] = [
                    'code' => (string) ($entry['code'] ?? ''),
                    'description' => (string) ($entry['description'] ?? ''),
                    'parameters' => array_map(static fn ($p): array => [
                        'name' => (string) ($p['name'] ?? ''),
                        'description' => (string) ($p['description'] ?? ''),
                        'required' => (bool) ($p['required'] ?? false),
                    ], (array) ($entry['parameters'] ?? [])),
                ];
            }
            if ($entries !== []) {
                $families[] = ['family' => (string) ($family['title'] ?? ''), 'entries' => $entries];
            }
        }

        $custom = [];
        foreach ($this->customShortcodes() as $row) {
            $haystack = mb_strtolower($row['shortcode'] . ' ' . $row['title']);
            if ($needle === '' || str_contains($haystack, $needle)) {
                $custom[] = $row;
            }
        }

        return ['families' => $families, 'customShortcodes' => $custom];
    }

    /**
     * @return array<string, mixed>
     */
    #[PsMcpTool(
        name: 'block_preview',
        title: 'Ever Block : aperçu rendu',
        description: 'Rend le hook d\'un bloc comme au front (shortcodes résolus) dans le contexte demandé : page (index, product, category, cms...), objet, langue, client (pour ses groupes). Le rendu contient tous les blocs du hook visibles dans ce contexte, pas seulement celui demandé. HTML tronqué à 100 000 caractères.',
        annotations: new PsMcpToolAnnotations(readOnlyHint: true, destructiveHint: false, idempotentHint: true, openWorldHint: false),
        meta: ['category' => 'content', 'qcd_permission' => 'AdminEverBlock:read']
    )]
    #[PsMcpSchema(
        properties: [
            'id' => ['type' => 'integer', 'minimum' => 1],
            'controller' => ['type' => 'string', 'default' => 'index', 'description' => 'Page simulée : index, product, category, cms, manufacturer, supplier, cart...'],
            'idProduct' => ['type' => 'integer', 'minimum' => 1],
            'idCategory' => ['type' => 'integer', 'minimum' => 1],
            'idCms' => ['type' => 'integer', 'minimum' => 1],
            'idManufacturer' => ['type' => 'integer', 'minimum' => 1],
            'idSupplier' => ['type' => 'integer', 'minimum' => 1],
            'idCustomer' => ['type' => 'integer', 'minimum' => 1, 'description' => 'Client simulé (ses groupes) ; défaut : visiteur.'],
            'lang' => ['type' => 'string', 'description' => 'Code ISO ou ID de langue.'],
        ],
        required: ['id']
    )]
    public function blockPreview(
        int $id,
        string $controller = 'index',
        ?int $idProduct = null,
        ?int $idCategory = null,
        ?int $idCms = null,
        ?int $idManufacturer = null,
        ?int $idSupplier = null,
        ?int $idCustomer = null,
        string $lang = '',
    ): array {
        $block = new \EverBlockClass($id, null, $this->shopId());
        if (empty($block->id)) {
            throw new PsMcpToolCallException(sprintf('Bloc #%d introuvable.', $id));
        }

        $params = array_filter([
            'controller' => preg_replace('/[^a-z0-9_-]/i', '', $controller) ?: 'index',
            'id_product' => $idProduct,
            'id_category' => $idCategory,
            'id_cms' => $idCms,
            'id_manufacturer' => $idManufacturer,
            'id_supplier' => $idSupplier,
            'id_customer' => $idCustomer,
            'id_lang' => $this->resolveLang($lang),
            'id_shop' => $this->shopId(),
        ], static fn ($v): bool => $v !== null && $v !== '');

        try {
            $preview = (new EverblockPreviewBuilder($this->module(), \Context::getContext()))->buildPreview($block, $params);
        } catch (\Throwable $exception) {
            throw new PsMcpToolCallException('Aperçu impossible : ' . $exception->getMessage());
        }

        $html = (string) ($preview['html'] ?? '');

        return [
            'block' => ['id' => (int) $block->id, 'name' => (string) $block->name, 'active' => (bool) $block->active],
            'hook' => (string) ($preview['hook'] ?? ''),
            'controller' => (string) ($preview['info']['controller'] ?? $params['controller']),
            'customerGroups' => array_values((array) ($preview['info']['groups'] ?? [])),
            'empty' => trim(strip_tags($html)) === '' && !str_contains($html, '<img'),
            'truncated' => mb_strlen($html) > self::MAX_HTML,
            'html' => mb_substr($html, 0, self::MAX_HTML),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    #[PsMcpTool(
        name: 'audit',
        title: 'Ever Block : audit des blocs',
        description: 'Contrôle les blocs de la boutique : actifs expirés, fin de publication dans les N prochains jours, programmés, contenu vide, traductions manquantes, hook absent ou sans le module greffé, shortcodes non reconnus ou retirés ([everfaq]), produits inactifs ou supprimés appelés par [product ...]. Chaque anomalie donne le bloc, le hook et le détail.',
        annotations: new PsMcpToolAnnotations(readOnlyHint: true, destructiveHint: false, idempotentHint: true, openWorldHint: false),
        meta: ['category' => 'content', 'qcd_permission' => 'AdminEverBlock:read']
    )]
    #[PsMcpSchema(properties: ['days' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 365, 'default' => 7, 'description' => 'Horizon des fins de publication proches.']])]
    public function audit(int $days = 7): array
    {
        $db = \Db::getInstance();
        $shopId = $this->shopId();
        $now = date('Y-m-d H:i:s');
        $soon = date('Y-m-d H:i:s', strtotime('+' . max(1, $days) . ' days'));
        $moduleId = (int) \Module::getModuleIdByName('everblock');
        $languages = \Language::getLanguages(true, $shopId);

        $blocks = $db->executeS(
            'SELECT b.id_everblock, b.name, b.id_hook, b.active, b.date_start, b.date_end, h.name AS hook_name,
                    (SELECT 1 FROM ' . _DB_PREFIX_ . 'hook_module hm WHERE hm.id_hook = b.id_hook AND hm.id_module = ' . $moduleId . ' AND hm.id_shop = ' . $shopId . ' LIMIT 1) AS registered
             FROM ' . _DB_PREFIX_ . 'everblock b
             LEFT JOIN ' . _DB_PREFIX_ . 'hook h ON h.id_hook = b.id_hook
             WHERE b.id_shop = ' . $shopId
        ) ?: [];
        $contents = [];
        foreach ($db->executeS(
            'SELECT bl.id_everblock, bl.id_lang, bl.content, bl.custom_code FROM ' . _DB_PREFIX_ . 'everblock_lang bl
             INNER JOIN ' . _DB_PREFIX_ . 'everblock b ON b.id_everblock = bl.id_everblock AND b.id_shop = ' . $shopId
        ) ?: [] as $row) {
            $contents[(int) $row['id_everblock']][(int) $row['id_lang']] = trim((string) $row['content'] . (string) $row['custom_code']);
        }

        $known = $this->knownShortcodes();
        $issues = [];
        $add = static function (string $type, array $block, string $detail) use (&$issues): void {
            $issues[] = [
                'type' => $type,
                'block' => ['id' => (int) $block['id_everblock'], 'name' => (string) $block['name'], 'active' => (bool) $block['active']],
                'hook' => (string) ($block['hook_name'] ?? ''),
                'detail' => $detail,
            ];
        };
        $productRefs = [];

        foreach ($blocks as $block) {
            $id = (int) $block['id_everblock'];
            $active = (bool) $block['active'];
            $dateStart = $this->date($block['date_start']);
            $dateEnd = $this->date($block['date_end']);
            $texts = $contents[$id] ?? [];

            if ($active && $dateEnd !== null && $dateEnd < $now) {
                $add('expired', $block, 'Fin de publication passée (' . $dateEnd . ') : le bloc est actif mais ne s\'affiche plus.');
            } elseif ($active && $dateEnd !== null && $dateEnd <= $soon) {
                $add('expiring', $block, 'Fin de publication le ' . $dateEnd . '.');
            }
            if ($active && $dateStart !== null && $dateStart > $now) {
                $add('scheduled', $block, 'Publication programmée le ' . $dateStart . '.');
            }
            if ($dateStart !== null && $dateEnd !== null && $dateStart > $dateEnd) {
                $add('invalid_dates', $block, 'Début (' . $dateStart . ') après la fin (' . $dateEnd . ') : le bloc ne s\'affiche jamais.');
            }
            if (empty($block['hook_name'])) {
                $add('missing_hook', $block, 'Le hook #' . (int) $block['id_hook'] . ' n\'existe plus.');
            } elseif ($active && !$block['registered']) {
                $add('hook_not_registered', $block, 'Ever Block n\'est pas greffé sur ' . $block['hook_name'] . ' : le bloc ne s\'affiche pas.');
            }

            $filled = array_filter($texts, static fn (string $t): bool => $t !== '');
            if ($active && $filled === []) {
                $add('empty', $block, 'Aucun contenu dans aucune langue.');
            } elseif ($active) {
                $missing = [];
                foreach ($languages as $language) {
                    if (($texts[(int) $language['id_lang']] ?? '') === '') {
                        $missing[] = (string) $language['iso_code'];
                    }
                }
                if ($missing !== []) {
                    $add('missing_translation', $block, 'Contenu vide en : ' . implode(', ', $missing) . '.');
                }
            }

            $unknown = [];
            $removed = [];
            foreach ($texts as $text) {
                if (preg_match_all('/\[([a-z][a-z0-9_-]{1,40})(?=[\s\]])/i', $text, $matches)) {
                    foreach ($matches[1] as $name) {
                        $name = mb_strtolower($name);
                        if (str_starts_with($name, 'everfaq')) {
                            $removed[$name] = true;
                        } elseif (!isset($known[$name])) {
                            $unknown[$name] = true;
                        }
                    }
                }
                if (preg_match_all('/\[product\s+([0-9][0-9,\s]*)/i', $text, $matches)) {
                    foreach ($matches[1] as $list) {
                        foreach (preg_split('/[\s,]+/', trim($list)) ?: [] as $productId) {
                            if ((int) $productId > 0) {
                                $productRefs[(int) $productId][$id] = $block;
                            }
                        }
                    }
                }
            }
            if ($removed !== []) {
                $add('removed_shortcode', $block, 'Shortcode de FAQ retiré, effacé au rendu : [' . implode('], [', array_keys($removed)) . ']. Le remplacer par un bloc QCD FAQ.');
            }
            if ($unknown !== []) {
                $add('unknown_shortcode', $block, 'Shortcode non documenté, à vérifier (fourni par un module tiers ou faute de frappe) : [' . implode('], [', array_keys($unknown)) . '].');
            }
        }

        if ($productRefs !== []) {
            $ids = array_keys($productRefs);
            $states = [];
            foreach ($db->executeS(
                'SELECT p.id_product, ps.active, ps.visibility FROM ' . _DB_PREFIX_ . 'product p
                 LEFT JOIN ' . _DB_PREFIX_ . 'product_shop ps ON ps.id_product = p.id_product AND ps.id_shop = ' . $shopId . '
                 WHERE p.id_product IN (' . implode(',', array_map('intval', $ids)) . ')'
            ) ?: [] as $row) {
                $states[(int) $row['id_product']] = $row;
            }
            foreach ($productRefs as $productId => $byBlock) {
                $state = $states[$productId] ?? null;
                $problem = match (true) {
                    $state === null => 'supprimé',
                    $state['active'] === null => 'absent de cette boutique',
                    !(int) $state['active'] => 'désactivé',
                    !in_array($state['visibility'], ['both', 'catalog'], true) => 'hors catalogue',
                    default => null,
                };
                if ($problem !== null) {
                    foreach ($byBlock as $block) {
                        $add('product_unavailable', $block, 'Produit #' . $productId . ' appelé par [product ...] : ' . $problem . '.');
                    }
                }
            }
        }

        $summary = [];
        foreach ($issues as $issue) {
            $summary[$issue['type']] = ($summary[$issue['type']] ?? 0) + 1;
        }
        ksort($summary);

        return [
            'blocks' => count($blocks),
            'activeBlocks' => count(array_filter($blocks, static fn (array $b): bool => (bool) $b['active'])),
            'issues' => count($issues),
            'summary' => $summary,
            'items' => $issues,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    #[PsMcpTool(
        name: 'update_check',
        title: 'Ever Block : mise à jour disponible ?',
        description: 'Compare la version installée d\'Ever Block à la dernière release GitHub (TeamEver/everblock) et renvoie ses notes de version. L\'installation passe par l\'outil console everblock-cli_everblock_update (validation dans le back-office) : elle remplace les fichiers du module, toute modification locale non publiée sur GitHub est perdue, une sauvegarde est gardée dans var/everblock_backups/.',
        annotations: new PsMcpToolAnnotations(readOnlyHint: true, destructiveHint: false, idempotentHint: true, openWorldHint: true),
        meta: ['category' => 'maintenance', 'qcd_permission' => 'AdminEverBlockConfiguration:read']
    )]
    public function updateCheck(bool $refresh = true): array
    {
        $module = $this->module();

        return (new ModuleUpdater($module, new GithubReleaseChecker((string) $module->version)))->status($refresh)
            + ['installCommand' => 'everblock-cli_everblock_update (ou php bin/console everblock:update)'];
    }

    /* ------------------------------------------------------------------------------------
     * Écriture
     * ---------------------------------------------------------------------------------- */

    /**
     * @param array<string, string>|null $content    HTML par langue, clés ISO (fr, en...) ou ID
     * @param array<string, string>|null $customCode code personnalisé par langue
     * @param list<int>|null $categories
     * @param list<int>|null $manufacturers
     * @param list<int>|null $suppliers
     * @param list<int>|null $cmsCategories
     * @param list<int>|null $groups
     *
     * @return array<string, mixed>
     */
    #[PsMcpTool(
        name: 'block_save',
        title: 'Ever Block : créer ou modifier un bloc',
        description: 'Crée un bloc HTML (sans id ; name, hook et content requis) ou modifie un bloc existant (id) : seuls les champs fournis changent, et seules les langues fournies dans content ou customCode sont réécrites. Dates au format AAAA-MM-JJ HH:MM:SS, chaîne vide pour retirer une date. Un bloc créé est inactif sauf active=true. Lire shortcodes_doc avant d\'insérer des shortcodes.',
        annotations: new PsMcpToolAnnotations(readOnlyHint: false, destructiveHint: false, idempotentHint: false, openWorldHint: false),
        meta: ['category' => 'content', 'qcd_permission' => 'AdminEverBlock:update', 'qcd_precheck' => 'checkBlockSave']
    )]
    #[PsMcpSchema(
        properties: [
            'id' => ['type' => 'integer', 'minimum' => 1, 'description' => 'Bloc à modifier ; absent : création.'],
            'name' => ['type' => 'string', 'maxLength' => 255],
            'hook' => ['type' => 'string', 'description' => 'Nom ou ID du hook d\'affichage (voir hooks_list).'],
            'content' => ['type' => 'object', 'additionalProperties' => ['type' => 'string'], 'description' => 'HTML par langue : {"fr": "<p>...</p>", "en": "..."}.'],
            'customCode' => ['type' => 'object', 'additionalProperties' => ['type' => 'string']],
            'active' => ['type' => 'boolean'],
            'position' => ['type' => 'integer', 'minimum' => 0],
            'device' => ['type' => 'string', 'enum' => ['all', 'desktop', 'tablet', 'mobile']],
            'dateStart' => ['type' => 'string'],
            'dateEnd' => ['type' => 'string'],
            'onlyHome' => ['type' => 'boolean'],
            'onlyCategory' => ['type' => 'boolean'],
            'onlyCategoryProduct' => ['type' => 'boolean'],
            'categories' => ['type' => 'array', 'items' => ['type' => 'integer']],
            'onlyManufacturer' => ['type' => 'boolean'],
            'manufacturers' => ['type' => 'array', 'items' => ['type' => 'integer']],
            'onlySupplier' => ['type' => 'boolean'],
            'suppliers' => ['type' => 'array', 'items' => ['type' => 'integer']],
            'onlyCmsCategory' => ['type' => 'boolean'],
            'cmsCategories' => ['type' => 'array', 'items' => ['type' => 'integer']],
            'groups' => ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => 'Groupes clients autorisés ; liste vide : tous.'],
            'addContainer' => ['type' => 'boolean'],
            'lazyload' => ['type' => 'boolean'],
            'obfuscateLink' => ['type' => 'boolean'],
            'modal' => ['type' => 'boolean'],
            'cssClass' => ['type' => 'string'],
            'bootstrapClass' => ['type' => 'string'],
            'background' => ['type' => 'string'],
            'dataAttribute' => ['type' => 'string'],
        ]
    )]
    public function blockSave(
        ?int $id = null,
        ?string $name = null,
        ?string $hook = null,
        ?array $content = null,
        ?array $customCode = null,
        ?bool $active = null,
        ?int $position = null,
        ?string $device = null,
        ?string $dateStart = null,
        ?string $dateEnd = null,
        ?bool $onlyHome = null,
        ?bool $onlyCategory = null,
        ?bool $onlyCategoryProduct = null,
        ?array $categories = null,
        ?bool $onlyManufacturer = null,
        ?array $manufacturers = null,
        ?bool $onlySupplier = null,
        ?array $suppliers = null,
        ?bool $onlyCmsCategory = null,
        ?array $cmsCategories = null,
        ?array $groups = null,
        ?bool $addContainer = null,
        ?bool $lazyload = null,
        ?bool $obfuscateLink = null,
        ?bool $modal = null,
        ?string $cssClass = null,
        ?string $bootstrapClass = null,
        ?string $background = null,
        ?string $dataAttribute = null,
    ): array {
        // Tous les paramètres, par nom : la prévalidation reçoit exactement les mêmes.
        $data = $this->checkBlockSave(...get_defined_vars());
        $languages = \Language::getLanguages(false);
        $savedId = $this->saveHandler()->handle(new SaveAdminItemCommand('blocks', $id, $this->shopId(), $data, $languages));
        if ($savedId <= 0) {
            throw new PsMcpToolCallException('Le bloc n\'a pas pu être enregistré.');
        }

        return ['saved' => true, 'created' => $id === null, 'block' => $this->describeBlock($this->loadBlock($savedId))];
    }

    /**
     * Prévalidation : construit les données complètes du bloc sans rien écrire.
     *
     * @return array<string, mixed> données au format du formulaire BO (SaveAdminItemHandler)
     */
    public function checkBlockSave(
        ?int $id = null,
        ?string $name = null,
        ?string $hook = null,
        ?array $content = null,
        ?array $customCode = null,
        ?bool $active = null,
        ?int $position = null,
        ?string $device = null,
        ?string $dateStart = null,
        ?string $dateEnd = null,
        ?bool $onlyHome = null,
        ?bool $onlyCategory = null,
        ?bool $onlyCategoryProduct = null,
        ?array $categories = null,
        ?bool $onlyManufacturer = null,
        ?array $manufacturers = null,
        ?bool $onlySupplier = null,
        ?array $suppliers = null,
        ?bool $onlyCmsCategory = null,
        ?array $cmsCategories = null,
        ?array $groups = null,
        ?bool $addContainer = null,
        ?bool $lazyload = null,
        ?bool $obfuscateLink = null,
        ?bool $modal = null,
        ?string $cssClass = null,
        ?string $bootstrapClass = null,
        ?string $background = null,
        ?string $dataAttribute = null,
    ): array {
        $creating = $id === null;
        if ($creating) {
            if ($name === null || trim($name) === '' || $hook === null || trim($hook) === '' || $content === null || $content === []) {
                throw new PsMcpToolCallException('Création : name, hook et content sont obligatoires.');
            }
            $data = $this->blockToData(new Block());
            $data['active'] = false;
        } else {
            $data = $this->blockToData($this->loadBlock($id));
        }

        if ($name !== null) {
            if (trim($name) === '') {
                throw new PsMcpToolCallException('Le nom ne peut pas être vide.');
            }
            $data['name'] = mb_substr(trim($name), 0, 255);
        }
        if ($hook !== null) {
            $data['id_hook'] = (int) $this->resolveHook($hook)['id_hook'];
        }
        foreach (['content' => $content, 'custom_code' => $customCode] as $field => $values) {
            foreach ((array) $values as $langKey => $html) {
                $data[$field . '_' . $this->resolveLang((string) $langKey, true)] = (string) $html;
            }
        }
        if ($active !== null) {
            $data['active'] = $active;
        }
        if ($position !== null) {
            $data['position'] = max(0, $position);
        } elseif ($creating || $hook !== null) {
            $data['position'] = (int) \Db::getInstance()->getValue(
                'SELECT COALESCE(MAX(position), 0) + 1 FROM ' . _DB_PREFIX_ . 'everblock
                 WHERE id_shop = ' . $this->shopId() . ' AND id_hook = ' . (int) $data['id_hook']
            );
        }
        if ($device !== null) {
            if (!isset(self::DEVICES[$device])) {
                throw new PsMcpToolCallException('device : all, desktop, tablet ou mobile.');
            }
            $data['device'] = self::DEVICES[$device];
        }
        foreach (['date_start' => $dateStart, 'date_end' => $dateEnd] as $field => $value) {
            if ($value === null) {
                continue;
            }
            $value = trim($value);
            if ($value !== '' && strtotime($value) === false) {
                throw new PsMcpToolCallException(sprintf('Date illisible : « %s » (format AAAA-MM-JJ HH:MM:SS).', $value));
            }
            $data[$field] = $value === '' ? null : date('Y-m-d H:i:s', (int) strtotime($value));
        }
        if (!empty($data['date_start']) && !empty($data['date_end']) && $data['date_start'] > $data['date_end']) {
            throw new PsMcpToolCallException('La date de début est postérieure à la date de fin.');
        }

        $args = get_defined_vars();
        foreach (self::FLAGS as $argument => $field) {
            if ($args[$argument] !== null) {
                $data[$field] = (bool) $args[$argument];
            }
        }
        foreach (self::TARGET_LISTS as $argument => $field) {
            if ($args[$argument] !== null) {
                $data[$field] = array_values(array_unique(array_filter(array_map('intval', $args[$argument]), static fn (int $v): bool => $v > 0)));
            }
        }
        foreach (self::TEXTS as $argument => $field) {
            if ($args[$argument] !== null) {
                $data[$field] = trim((string) $args[$argument]);
            }
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    #[PsMcpTool(
        name: 'block_toggle',
        title: 'Ever Block : activer ou désactiver un bloc',
        description: 'Active ou désactive un bloc HTML. Le cache du bloc et de son hook est vidé.',
        annotations: new PsMcpToolAnnotations(readOnlyHint: false, destructiveHint: false, idempotentHint: true, openWorldHint: false),
        meta: ['category' => 'content', 'qcd_permission' => 'AdminEverBlock:update', 'qcd_precheck' => 'checkBlockExists']
    )]
    #[PsMcpSchema(properties: ['id' => ['type' => 'integer', 'minimum' => 1], 'active' => ['type' => 'boolean']], required: ['id', 'active'])]
    public function blockToggle(int $id, bool $active): array
    {
        $block = $this->loadBlock($id);
        $changed = (bool) $block->active !== $active;
        if ($changed) {
            $this->blockRepository()->setActive($id, $this->shopId(), $active);
            Block::clearCache($id, $this->shopId(), \Language::getLanguages(false), [(int) $block->id_hook]);
        }

        return ['changed' => $changed, 'block' => ['id' => $id, 'name' => (string) $block->name, 'active' => $active]];
    }

    /**
     * @return array<string, mixed>
     */
    #[PsMcpTool(
        name: 'block_duplicate',
        title: 'Ever Block : dupliquer un bloc',
        description: 'Copie un bloc (contenus, ciblage, habillage), en fin de liste de son hook ou sur un autre hook. La copie est créée inactive sauf active=true, pour ne pas afficher deux fois le même contenu.',
        annotations: new PsMcpToolAnnotations(readOnlyHint: false, destructiveHint: false, idempotentHint: false, openWorldHint: false),
        meta: ['category' => 'content', 'qcd_permission' => 'AdminEverBlock:create', 'qcd_precheck' => 'checkBlockDuplicate']
    )]
    #[PsMcpSchema(
        properties: [
            'id' => ['type' => 'integer', 'minimum' => 1],
            'hook' => ['type' => 'string', 'description' => 'Hook de la copie (nom ou ID) ; défaut : celui du bloc.'],
            'name' => ['type' => 'string', 'description' => 'Nom de la copie ; défaut : nom du bloc suivi de « (copy) ».'],
            'active' => ['type' => 'boolean', 'default' => false],
        ],
        required: ['id']
    )]
    public function blockDuplicate(int $id, ?string $hook = null, ?string $name = null, bool $active = false): array
    {
        $data = $this->checkBlockDuplicate($id, $hook, $name, $active);
        $newId = $this->saveHandler()->handle(new SaveAdminItemCommand('blocks', null, $this->shopId(), $data, \Language::getLanguages(false)));
        if ($newId <= 0) {
            throw new PsMcpToolCallException('La copie n\'a pas pu être créée.');
        }

        return ['sourceId' => $id, 'block' => $this->describeBlock($this->loadBlock($newId))];
    }

    /**
     * @return array<string, mixed>
     */
    public function checkBlockDuplicate(int $id, ?string $hook = null, ?string $name = null, bool $active = false): array
    {
        $source = $this->loadBlock($id);
        $data = $this->blockToData($source);
        $data['name'] = $name !== null && trim($name) !== '' ? mb_substr(trim($name), 0, 255) : trim($source->name . ' (copy)');
        if ($hook !== null && trim($hook) !== '') {
            $data['id_hook'] = (int) $this->resolveHook($hook)['id_hook'];
        }
        $data['position'] = $this->blockRepository()->getNextPosition($this->shopId(), (int) $data['id_hook']);
        $data['active'] = $active;

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    #[PsMcpTool(
        name: 'block_delete',
        title: 'Ever Block : supprimer un bloc',
        description: 'Supprime définitivement un bloc HTML et ses contenus dans toutes les langues. Pour le masquer sans le perdre, utiliser block_toggle.',
        annotations: new PsMcpToolAnnotations(readOnlyHint: false, destructiveHint: true, idempotentHint: true, openWorldHint: false),
        meta: ['category' => 'content', 'qcd_permission' => 'AdminEverBlock:delete', 'qcd_precheck' => 'checkBlockExists']
    )]
    #[PsMcpSchema(properties: ['id' => ['type' => 'integer', 'minimum' => 1]], required: ['id'])]
    public function blockDelete(int $id): array
    {
        $block = $this->loadBlock($id);
        $deleted = (new DeleteAdminItemHandler(
            $this->blockRepository(),
            RepositoryProvider::get('everblock.repository.shortcode'),
            RepositoryProvider::get('everblock.repository.page'),
            RepositoryProvider::get('everblock.repository.hook')
        ))->handle(new DeleteAdminItemCommand('blocks', $id, $this->shopId()));

        return ['deleted' => $deleted, 'block' => ['id' => $id, 'name' => (string) $block->name]];
    }

    public function checkBlockExists(int $id, bool $active = false): void
    {
        $this->loadBlock($id);
    }

    /**
     * @return array<string, mixed>
     */
    #[PsMcpTool(
        name: 'cache_clear',
        title: 'Ever Block : vider le cache',
        description: 'Vide le cache d\'Ever Block (blocs, shortcodes, pages, contenus produit). Sans effet sur les données ; à lancer si un contenu modifié ne s\'affiche pas.',
        annotations: new PsMcpToolAnnotations(readOnlyHint: false, destructiveHint: false, idempotentHint: true, openWorldHint: false),
        meta: ['category' => 'content', 'qcd_permission' => 'AdminEverBlockConfiguration:update', 'qcd_approval' => false]
    )]
    public function cacheClear(): array
    {
        EverblockCache::clearAllModuleCache();

        return ['cleared' => true];
    }

    /* ------------------------------------------------------------------------------------
     * Outils internes
     * ---------------------------------------------------------------------------------- */

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

    private function loadBlock(int $id): Block
    {
        $block = $id > 0 ? $this->blockRepository()->find($id, $this->shopId()) : null;
        if (!$block instanceof Block) {
            throw new PsMcpToolCallException(sprintf('Bloc #%d introuvable dans cette boutique.', $id));
        }

        return $block;
    }

    /**
     * @return array{id_hook: int, name: string}
     */
    private function resolveHook(string $hook): array
    {
        $hook = trim($hook);
        $row = ctype_digit($hook)
            ? \Db::getInstance()->getRow('SELECT id_hook, name FROM ' . _DB_PREFIX_ . 'hook WHERE id_hook = ' . (int) $hook)
            : \Db::getInstance()->getRow('SELECT id_hook, name FROM ' . _DB_PREFIX_ . 'hook WHERE name = "' . pSQL($hook) . '"');
        if (!$row) {
            throw new PsMcpToolCallException(sprintf('Hook « %s » introuvable (voir hooks_list).', $hook));
        }
        if (preg_match('/^(action|filter)/i', (string) $row['name'])) {
            throw new PsMcpToolCallException(sprintf('« %s » n\'est pas un hook d\'affichage.', $row['name']));
        }

        return ['id_hook' => (int) $row['id_hook'], 'name' => (string) $row['name']];
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

    /**
     * Bloc au format attendu par SaveAdminItemHandler (celui du formulaire BO).
     *
     * @return array<string, mixed>
     */
    private function blockToData(Block $block): array
    {
        $data = [
            'name' => $block->name,
            'id_hook' => $block->id_hook,
            'device' => $block->device,
            'position' => $block->position,
            'delay' => $block->delay,
            'timeout' => $block->timeout,
            'active' => $block->active,
            'date_start' => $block->date_start,
            'date_end' => $block->date_end,
        ];
        foreach (self::FLAGS as $field) {
            $data[$field] = (bool) $block->{$field};
        }
        foreach (self::TARGET_LISTS as $field) {
            $decoded = json_decode((string) $block->{$field}, true);
            $data[$field] = is_array($decoded) ? array_values(array_map('intval', $decoded)) : [];
        }
        foreach (self::TEXTS as $field) {
            $data[$field] = $block->{$field};
        }
        foreach ($block->content as $langId => $html) {
            $data['content_' . (int) $langId] = (string) $html;
        }
        foreach ($block->custom_code as $langId => $code) {
            $data['custom_code_' . (int) $langId] = (string) $code;
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function describeBlock(Block $block): array
    {
        $byIso = static function (array $values): array {
            $out = [];
            foreach ($values as $langId => $value) {
                $iso = \Language::getIsoById((int) $langId);
                $out[$iso ?: (string) $langId] = (string) $value;
            }

            return $out;
        };
        $data = $this->blockToData($block);
        $hookName = (string) \Hook::getNameById((int) $block->id_hook);

        return [
            'id' => (int) $block->id,
            'name' => $block->name,
            'hook' => ['id' => (int) $block->id_hook, 'name' => $hookName],
            'position' => $block->position,
            'active' => $block->active,
            'status' => $this->status(['active' => $block->active, 'date_start' => $block->date_start, 'date_end' => $block->date_end]),
            'device' => $this->deviceName($block->device),
            'dateStart' => $this->date($block->date_start),
            'dateEnd' => $this->date($block->date_end),
            'content' => $byIso($block->content),
            'customCode' => array_filter($byIso($block->custom_code), static fn (string $v): bool => $v !== ''),
            'targeting' => [
                'onlyHome' => $block->only_home,
                'onlyCategory' => $block->only_category,
                'onlyCategoryProduct' => $block->only_category_product,
                'categories' => $data['categories'],
                'onlyManufacturer' => $block->only_manufacturer,
                'manufacturers' => $data['manufacturers'],
                'onlySupplier' => $block->only_supplier,
                'suppliers' => $data['suppliers'],
                'onlyCmsCategory' => $block->only_cms_category,
                'cmsCategories' => $data['cms_categories'],
                'groups' => $data['groups'],
            ],
            'display' => [
                'addContainer' => $block->add_container,
                'bootstrapClass' => $block->bootstrap_class,
                'cssClass' => $block->css_class,
                'background' => $block->background,
                'dataAttribute' => $block->data_attribute,
                'lazyload' => $block->lazyload,
                'obfuscateLink' => $block->obfuscate_link,
            ],
            'modal' => ['enabled' => $block->modal, 'delayDays' => $block->delay, 'timeoutMs' => $block->timeout],
        ];
    }

    /**
     * @param array<string, mixed> $row
     */
    private function status(array $row): string
    {
        if (!(bool) ($row['active'] ?? false)) {
            return 'inactive';
        }
        $now = date('Y-m-d H:i:s');
        $start = $this->date($row['date_start'] ?? null);
        $end = $this->date($row['date_end'] ?? null);
        if ($end !== null && $end < $now) {
            return 'expired';
        }
        if ($start !== null && $start > $now) {
            return 'scheduled';
        }

        return 'live';
    }

    private function date(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' || str_starts_with($value, '0000-00-00') ? null : $value;
    }

    private function deviceName(int $device): string
    {
        return (string) (array_search($device, self::DEVICES, true) ?: 'all');
    }

    private function excerpt(string $html): string
    {
        $text = trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));

        return mb_strlen($text) > 160 ? mb_substr($text, 0, 157) . '...' : $text;
    }

    /**
     * @param int[] $blockIds
     *
     * @return array<int, list<string>> codes ISO des langues dont le contenu n'est pas vide
     */
    private function filledLanguages(array $blockIds): array
    {
        if ($blockIds === []) {
            return [];
        }
        $out = [];
        foreach (\Db::getInstance()->executeS(
            'SELECT bl.id_everblock, l.iso_code FROM ' . _DB_PREFIX_ . 'everblock_lang bl
             INNER JOIN ' . _DB_PREFIX_ . 'lang l ON l.id_lang = bl.id_lang
             WHERE bl.id_everblock IN (' . implode(',', array_map('intval', $blockIds)) . ')
               AND TRIM(CONCAT(COALESCE(bl.content, ""), COALESCE(bl.custom_code, ""))) <> ""'
        ) ?: [] as $row) {
            $out[(int) $row['id_everblock']][] = (string) $row['iso_code'];
        }

        return $out;
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
}
