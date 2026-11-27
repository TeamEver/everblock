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
use Everblock\Tools\Entity\Modal;
use Everblock\Tools\Entity\Page;
use Everblock\Tools\Entity\ProductFlag;
use Everblock\Tools\Entity\ProductTab;
use Everblock\Tools\Entity\Shortcode;
use Everblock\Tools\Repository\RepositoryProvider;
use Everblock\Tools\Service\EverblockCache;
use Everblock\Tools\Service\EverblockTools;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpSchema;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpTool;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpToolAnnotations;
use PrestaShop\Module\PsMcpServer\Server\Exceptions\PsMcpToolCallException;

/**
 * Outils MCP d'Ever Block pour les contenus : pages du guide, shortcodes personnalisés,
 * onglets, flags et modale des fiches produit, recherche et remplacement dans les contenus.
 *
 * Même modèle que EverblockMcpTools : attributs de ps_mcp_server, clés qcd_* lues par QCD MCP
 * Server, écritures par les gestionnaires et entités du back-office (mêmes règles, mêmes caches).
 * Les fichiers (couverture de page, fichiers de la modale) restent gérés dans le back-office.
 */
final class EverblockContentMcpTools
{
    use EverblockMcpSupport;

    private const PAGE_FIELDS = [
        'name' => 'name',
        'title' => 'title',
        'metaDescription' => 'meta_description',
        'shortDescription' => 'short_description',
        'linkRewrite' => 'link_rewrite',
        'content' => 'content',
    ];

    /** Contenus couverts par la recherche et le remplacement : table de langue, parent, clé, champs. */
    private const SCOPES = [
        'blocks' => ['parent' => 'everblock', 'lang' => 'everblock_lang', 'key' => 'id_everblock', 'fields' => ['content', 'custom_code'], 'label' => 'p.name', 'product' => false],
        'shortcodes' => ['parent' => 'everblock_shortcode', 'lang' => 'everblock_shortcode_lang', 'key' => 'id_everblock_shortcode', 'fields' => ['title', 'content'], 'label' => 'p.shortcode', 'product' => false],
        'pages' => ['parent' => 'everblock_page', 'lang' => 'everblock_page_lang', 'key' => 'id_everblock_page', 'fields' => ['name', 'title', 'meta_description', 'short_description', 'content'], 'label' => 'l.name', 'product' => false],
        'tabs' => ['parent' => 'everblock_tabs', 'lang' => 'everblock_tabs_lang', 'key' => 'id_everblock_tabs', 'fields' => ['title', 'content'], 'label' => 'CONCAT("produit #", p.id_product, " onglet ", p.id_tab)', 'product' => true],
        'flags' => ['parent' => 'everblock_flags', 'lang' => 'everblock_flags_lang', 'key' => 'id_everblock_flags', 'fields' => ['title', 'content'], 'label' => 'CONCAT("produit #", p.id_product, " flag ", p.id_flag)', 'product' => true],
        'modals' => ['parent' => 'everblock_modal', 'lang' => 'everblock_modal_lang', 'key' => 'id_everblock_modal', 'fields' => ['content', 'button_label'], 'label' => 'CONCAT("produit #", p.id_product, " modale")', 'product' => true],
    ];
    private const MAX_MATCHES = 500;
    private const MAX_REPLACE_ROWS = 1000;

    /* ------------------------------------------------------------------------------------
     * Pages du guide
     * ---------------------------------------------------------------------------------- */

    /**
     * @return array<string, mixed>
     */
    #[PsMcpTool(
        name: 'pages_list',
        title: 'Ever Block : pages du guide',
        description: 'Pages de contenu du module (liste /guide) : nom, titre SEO, URL, statut, position, groupes autorisés, langues renseignées. search filtre sur le nom, le titre et le contenu.',
        annotations: new PsMcpToolAnnotations(readOnlyHint: true, destructiveHint: false, idempotentHint: true, openWorldHint: false),
        meta: ['category' => 'content', 'qcd_permission' => 'AdminEverBlockPage:read']
    )]
    public function pagesList(string $search = '', string $status = 'all', string $lang = ''): array
    {
        $shopId = $this->shopId();
        $langId = $this->resolveLang($lang);
        $where = ['p.id_shop = ' . $shopId];
        if ($status === 'active' || $status === 'inactive') {
            $where[] = 'p.active = ' . ($status === 'active' ? 1 : 0);
        }
        if (trim($search) !== '') {
            $like = pSQL('%' . trim($search) . '%', true);
            $where[] = 'EXISTS (SELECT 1 FROM ' . _DB_PREFIX_ . 'everblock_page_lang s WHERE s.id_everblock_page = p.id_everblock_page
                AND (s.name LIKE "' . $like . '" OR s.title LIKE "' . $like . '" OR s.content LIKE "' . $like . '"))';
        }
        $rows = \Db::getInstance()->executeS(
            'SELECT p.id_everblock_page, p.active, p.position, p.groups, p.date_upd, l.name, l.title, l.link_rewrite
             FROM ' . _DB_PREFIX_ . 'everblock_page p
             LEFT JOIN ' . _DB_PREFIX_ . 'everblock_page_lang l ON l.id_everblock_page = p.id_everblock_page AND l.id_lang = ' . $langId . '
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY p.position ASC, p.id_everblock_page DESC'
        ) ?: [];

        return ['items' => array_map(fn (array $r): array => [
            'id' => (int) $r['id_everblock_page'],
            'name' => (string) $r['name'],
            'title' => (string) $r['title'],
            'url' => $this->pageUrl((int) $r['id_everblock_page'], (string) $r['link_rewrite'], $langId),
            'active' => (bool) $r['active'],
            'position' => (int) $r['position'],
            'groups' => $this->decodeIds($r['groups']),
            'dateUpd' => $this->date($r['date_upd']),
        ], $rows)];
    }

    /**
     * @return array<string, mixed>
     */
    #[PsMcpTool(
        name: 'page_get',
        title: 'Ever Block : détail d\'une page',
        description: 'Tous les champs d\'une page du guide, par langue (clés ISO) : nom, titre SEO, méta description, résumé, URL simplifiée, contenu ; statut, position, groupes autorisés, image de couverture.',
        annotations: new PsMcpToolAnnotations(readOnlyHint: true, destructiveHint: false, idempotentHint: true, openWorldHint: false),
        meta: ['category' => 'content', 'qcd_permission' => 'AdminEverBlockPage:read']
    )]
    #[PsMcpSchema(properties: ['id' => ['type' => 'integer', 'minimum' => 1]], required: ['id'])]
    public function pageGet(int $id): array
    {
        return $this->describePage($this->loadPage($id));
    }

    /**
     * @return array<string, mixed>
     */
    #[PsMcpTool(
        name: 'page_save',
        title: 'Ever Block : créer ou modifier une page',
        description: 'Crée une page du guide (sans id ; name et content requis, inactive sauf active=true) ou modifie une page (id) : seuls les champs et les langues fournis changent. Champs par langue, clés ISO : {"fr": "...", "en": "..."}. URL simplifiée déduite du nom si absente. groups : groupes clients autorisés, liste vide pour tous. L\'image de couverture se gère dans le back-office.',
        annotations: new PsMcpToolAnnotations(readOnlyHint: false, destructiveHint: false, idempotentHint: false, openWorldHint: false),
        meta: ['category' => 'content', 'qcd_permission' => 'AdminEverBlockPage:update', 'qcd_precheck' => 'checkPageSave']
    )]
    #[PsMcpSchema(
        properties: [
            'id' => ['type' => 'integer', 'minimum' => 1],
            'name' => self::LOCALIZED_SCHEMA,
            'title' => self::LOCALIZED_SCHEMA,
            'metaDescription' => self::LOCALIZED_SCHEMA,
            'shortDescription' => self::LOCALIZED_SCHEMA,
            'linkRewrite' => self::LOCALIZED_SCHEMA,
            'content' => self::LOCALIZED_SCHEMA,
            'active' => ['type' => 'boolean'],
            'position' => ['type' => 'integer', 'minimum' => 0],
            'groups' => ['type' => 'array', 'items' => ['type' => 'integer']],
        ]
    )]
    public function pageSave(
        ?int $id = null,
        ?array $name = null,
        ?array $title = null,
        ?array $metaDescription = null,
        ?array $shortDescription = null,
        ?array $linkRewrite = null,
        ?array $content = null,
        ?bool $active = null,
        ?int $position = null,
        ?array $groups = null,
    ): array {
        $data = $this->checkPageSave(...get_defined_vars());
        $savedId = $this->saveHandler()->handle(new SaveAdminItemCommand('pages', $id, $this->shopId(), $data, \Language::getLanguages(false)));
        if ($savedId <= 0) {
            throw new PsMcpToolCallException('La page n\'a pas pu être enregistrée.');
        }

        return ['saved' => true, 'created' => $id === null, 'page' => $this->describePage($this->loadPage($savedId))];
    }

    /**
     * @return array<string, mixed> données au format du formulaire BO
     */
    public function checkPageSave(
        ?int $id = null,
        ?array $name = null,
        ?array $title = null,
        ?array $metaDescription = null,
        ?array $shortDescription = null,
        ?array $linkRewrite = null,
        ?array $content = null,
        ?bool $active = null,
        ?int $position = null,
        ?array $groups = null,
    ): array {
        if ($id === null) {
            if ($name === null || $name === [] || $content === null || $content === []) {
                throw new PsMcpToolCallException('Création : name et content sont obligatoires.');
            }
            $data = $this->pageToData(new Page());
            $data['active'] = false;
            $data['position'] = (int) \Db::getInstance()->getValue(
                'SELECT COALESCE(MAX(position), 0) + 1 FROM ' . _DB_PREFIX_ . 'everblock_page WHERE id_shop = ' . $this->shopId()
            );
        } else {
            $data = $this->pageToData($this->loadPage($id));
        }

        $args = get_defined_vars();
        foreach (self::PAGE_FIELDS as $argument => $field) {
            $html = in_array($field, ['short_description', 'content'], true);
            foreach ($this->localizedInput($args[$argument], $html) as $langId => $value) {
                if (!$html && $value !== strip_tags($value)) {
                    throw new PsMcpToolCallException(sprintf('%s : texte sans HTML attendu.', $argument));
                }
                $data[$field . '_' . $langId] = $value;
            }
        }
        if ($active !== null) {
            $data['active'] = $active;
        }
        if ($position !== null) {
            $data['position'] = $position;
        }
        if ($groups !== null) {
            $data['group_ids'] = array_values(array_unique(array_filter(array_map('intval', $groups), static fn (int $v): bool => $v > 0)));
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    #[PsMcpTool(
        name: 'page_delete',
        title: 'Ever Block : supprimer une page',
        description: 'Supprime définitivement une page du guide dans toutes les langues. Pour la masquer, page_save avec active=false.',
        annotations: new PsMcpToolAnnotations(readOnlyHint: false, destructiveHint: true, idempotentHint: true, openWorldHint: false),
        meta: ['category' => 'content', 'qcd_permission' => 'AdminEverBlockPage:delete', 'qcd_precheck' => 'checkPageExists']
    )]
    #[PsMcpSchema(properties: ['id' => ['type' => 'integer', 'minimum' => 1]], required: ['id'])]
    public function pageDelete(int $id): array
    {
        $page = $this->loadPage($id);
        $deleted = $this->deleteHandler()->handle(new DeleteAdminItemCommand('pages', $id, $this->shopId()));

        return ['deleted' => $deleted, 'page' => ['id' => $id, 'name' => $this->byIso((array) $page->name)]];
    }

    public function checkPageExists(int $id): void
    {
        $this->loadPage($id);
    }

    /* ------------------------------------------------------------------------------------
     * Shortcodes personnalisés
     * ---------------------------------------------------------------------------------- */

    /**
     * @return array<string, mixed>
     */
    #[PsMcpTool(
        name: 'shortcode_get',
        title: 'Ever Block : shortcode personnalisé',
        description: 'Détail d\'un shortcode personnalisé (par ID ou par code, ex. [bandeau_noel]) : titre et contenu de remplacement par langue. La liste est dans shortcodes_doc.',
        annotations: new PsMcpToolAnnotations(readOnlyHint: true, destructiveHint: false, idempotentHint: true, openWorldHint: false),
        meta: ['category' => 'content', 'qcd_permission' => 'AdminEverBlockShortcode:read']
    )]
    public function shortcodeGet(string $shortcode): array
    {
        return $this->describeShortcode($this->loadShortcode($shortcode));
    }

    /**
     * @return array<string, mixed>
     */
    #[PsMcpTool(
        name: 'shortcode_save',
        title: 'Ever Block : créer ou modifier un shortcode',
        description: 'Crée un shortcode personnalisé (sans id) ou le modifie (id) : le code, par exemple [bandeau_noel], est remplacé partout par le contenu de la langue du visiteur. Seuls les champs et les langues fournis changent. Un code déjà utilisé, ou celui d\'un shortcode natif, est refusé.',
        annotations: new PsMcpToolAnnotations(readOnlyHint: false, destructiveHint: false, idempotentHint: false, openWorldHint: false),
        meta: ['category' => 'content', 'qcd_permission' => 'AdminEverBlockShortcode:update', 'qcd_precheck' => 'checkShortcodeSave']
    )]
    #[PsMcpSchema(
        properties: [
            'id' => ['type' => 'integer', 'minimum' => 1],
            'shortcode' => ['type' => 'string', 'description' => 'Code entre crochets, lettres, chiffres, - et _ : [bandeau_noel].'],
            'title' => self::LOCALIZED_SCHEMA,
            'content' => self::LOCALIZED_SCHEMA,
        ]
    )]
    public function shortcodeSave(?int $id = null, ?string $shortcode = null, ?array $title = null, ?array $content = null): array
    {
        $data = $this->checkShortcodeSave($id, $shortcode, $title, $content);
        $savedId = $this->saveHandler()->handle(new SaveAdminItemCommand('shortcodes', $id, $this->shopId(), $data, \Language::getLanguages(false)));
        if ($savedId <= 0) {
            throw new PsMcpToolCallException('Le shortcode n\'a pas pu être enregistré.');
        }

        return ['saved' => true, 'created' => $id === null, 'shortcode' => $this->describeShortcode($this->loadShortcode((string) $savedId))];
    }

    /**
     * @return array<string, mixed>
     */
    public function checkShortcodeSave(?int $id = null, ?string $shortcode = null, ?array $title = null, ?array $content = null): array
    {
        if ($id === null && ($shortcode === null || $content === null || $content === [])) {
            throw new PsMcpToolCallException('Création : shortcode et content sont obligatoires.');
        }
        $existing = $id !== null ? $this->loadShortcode((string) $id) : new Shortcode();
        $data = ['shortcode' => $existing->shortcode];
        foreach ((array) $existing->title as $langId => $value) {
            $data['title_' . (int) $langId] = (string) $value;
        }
        foreach ((array) $existing->content as $langId => $value) {
            $data['content_' . (int) $langId] = (string) $value;
        }

        if ($shortcode !== null) {
            $code = trim($shortcode);
            if ($code !== '' && $code[0] !== '[') {
                $code = '[' . $code . ']';
            }
            if (!preg_match('/^\[[A-Za-z0-9_-]{2,60}\]$/', $code)) {
                throw new PsMcpToolCallException('Code invalide : lettres, chiffres, - et _ entre crochets, par exemple [bandeau_noel].');
            }
            $name = mb_strtolower(substr($code, 1, -1));
            $taken = (int) \Db::getInstance()->getValue(
                'SELECT id_everblock_shortcode FROM ' . _DB_PREFIX_ . 'everblock_shortcode
                 WHERE id_shop = ' . $this->shopId() . ' AND shortcode = "' . pSQL($code) . '"'
                . ($id !== null ? ' AND id_everblock_shortcode <> ' . (int) $id : '')
            );
            if ($taken > 0) {
                throw new PsMcpToolCallException(sprintf('%s existe déjà (shortcode #%d).', $code, $taken));
            }
            $custom = array_map(static fn (array $r): string => mb_strtolower(trim($r['shortcode'], '[]')), $this->customShortcodes());
            if (isset($this->knownShortcodes()[$name]) && !in_array($name, $custom, true)) {
                throw new PsMcpToolCallException(sprintf('%s est un shortcode natif d\'Ever Block : choisir un autre code.', $code));
            }
            $data['shortcode'] = $code;
        }
        foreach ($this->localizedInput($title, false) as $langId => $value) {
            $data['title_' . $langId] = strip_tags($value);
        }
        foreach ($this->localizedInput($content) as $langId => $value) {
            $data['content_' . $langId] = $value;
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    #[PsMcpTool(
        name: 'shortcode_delete',
        title: 'Ever Block : supprimer un shortcode',
        description: 'Supprime un shortcode personnalisé (par ID ou code). Les contenus qui l\'utilisent afficheront le code brut : chercher ses usages avec content_search avant.',
        annotations: new PsMcpToolAnnotations(readOnlyHint: false, destructiveHint: true, idempotentHint: true, openWorldHint: false),
        meta: ['category' => 'content', 'qcd_permission' => 'AdminEverBlockShortcode:delete', 'qcd_precheck' => 'checkShortcodeExists']
    )]
    public function shortcodeDelete(string $shortcode): array
    {
        $item = $this->loadShortcode($shortcode);
        $deleted = $this->deleteHandler()->handle(new DeleteAdminItemCommand('shortcodes', (int) $item->id, $this->shopId()));

        return ['deleted' => $deleted, 'shortcode' => $item->shortcode];
    }

    public function checkShortcodeExists(string $shortcode): void
    {
        $this->loadShortcode($shortcode);
    }

    /**
     * @return array<string, mixed>
     */
    #[PsMcpTool(
        name: 'shortcode_render',
        title: 'Ever Block : tester un rendu de shortcodes',
        description: 'Rend un contenu HTML avec ses shortcodes résolus, dans une langue, sans rien enregistrer : pour vérifier un shortcode avant de l\'insérer dans un bloc ou une page. Les shortcodes liés au visiteur ou à la page courante peuvent rendre vide hors contexte front. HTML tronqué à 100 000 caractères.',
        annotations: new PsMcpToolAnnotations(readOnlyHint: true, destructiveHint: false, idempotentHint: true, openWorldHint: false),
        meta: ['category' => 'content', 'qcd_permission' => 'AdminEverBlock:read']
    )]
    #[PsMcpSchema(properties: ['content' => ['type' => 'string', 'minLength' => 1], 'lang' => ['type' => 'string']], required: ['content'])]
    public function shortcodeRender(string $content, string $lang = ''): array
    {
        $context = \Context::getContext();
        $previous = $context->language;
        $context->language = new \Language($this->resolveLang($lang, $lang !== ''));
        try {
            $html = EverblockTools::renderShortcodes($content, $context, $this->module());
        } catch (\Throwable $exception) {
            throw new PsMcpToolCallException('Rendu impossible : ' . $exception->getMessage());
        } finally {
            $context->language = $previous;
        }

        return ['truncated' => mb_strlen($html) > 100000, 'html' => mb_substr($html, 0, 100000)];
    }

    /* ------------------------------------------------------------------------------------
     * Contenus de la fiche produit
     * ---------------------------------------------------------------------------------- */

    /**
     * @return array<string, mixed>
     */
    #[PsMcpTool(
        name: 'product_contents_get',
        title: 'Ever Block : onglets, flags et modale d\'un produit',
        description: 'Contenus Ever Block d\'une fiche produit (ID, référence ou référence de déclinaison) : chaque emplacement d\'onglet et de flag (titre et contenu par langue, vide si non utilisé) et la modale (contenu, libellé du bouton, fichiers).',
        annotations: new PsMcpToolAnnotations(readOnlyHint: true, destructiveHint: false, idempotentHint: true, openWorldHint: false),
        meta: ['category' => 'catalog', 'qcd_permission' => 'AdminProducts:read']
    )]
    public function productContentsGet(string $product): array
    {
        $info = $this->resolveProduct($product);
        $shopId = $this->shopId();
        $tabs = [];
        foreach (range(1, $this->slotCount('EVERPS_TAB_NB')) as $slot) {
            $tab = ProductTab::getByIdProductIdTab($info['id'], $shopId, $slot);
            $tabs[] = ['slot' => $slot, 'title' => $this->byIso((array) $tab->title), 'content' => $this->byIso((array) $tab->content)];
        }
        $flags = [];
        foreach (range(1, $this->slotCount('EVERPS_FLAG_NB')) as $slot) {
            $flag = ProductFlag::getByIdProductIdFlag($info['id'], $shopId, $slot);
            $flags[] = ['slot' => $slot, 'title' => $this->byIso((array) $flag->title), 'content' => $this->byIso((array) $flag->content)];
        }
        $modal = Modal::getByProductId($info['id'], $shopId);

        return [
            'product' => $info,
            'tabs' => $tabs,
            'flags' => $flags,
            'modal' => [
                'content' => $this->byIso((array) $modal->content),
                'buttonLabel' => $this->byIso((array) $modal->button_label),
                'file' => (string) $modal->file,
                'buttonFile' => (string) $modal->button_file,
            ],
            'notes' => 'Un flag ne s\'affiche que si son titre et son contenu sont renseignés. Fichiers de la modale : back-office.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    #[PsMcpTool(
        name: 'product_tab_save',
        title: 'Ever Block : onglet d\'une fiche produit',
        description: 'Écrit l\'onglet n° slot d\'un produit (slot de 1 au nombre d\'onglets réglé dans Ever Block). Seules les langues fournies changent ; une chaîne vide efface la langue. Un onglet sans titre ni contenu ne s\'affiche pas.',
        annotations: new PsMcpToolAnnotations(readOnlyHint: false, destructiveHint: false, idempotentHint: true, openWorldHint: false),
        meta: ['category' => 'catalog', 'qcd_permission' => 'AdminProducts:update', 'qcd_precheck' => 'checkProductTabSave']
    )]
    #[PsMcpSchema(
        properties: ['product' => ['type' => 'string'], 'slot' => ['type' => 'integer', 'minimum' => 1], 'title' => self::LOCALIZED_SCHEMA, 'content' => self::LOCALIZED_SCHEMA],
        required: ['product', 'slot']
    )]
    public function productTabSave(string $product, int $slot, ?array $title = null, ?array $content = null): array
    {
        [$info, $titles, $contents] = $this->checkProductTabSave($product, $slot, $title, $content);
        $tab = ProductTab::getByIdProductIdTab($info['id'], $this->shopId(), $slot);
        $tab->title = array_replace((array) $tab->title, $titles);
        $tab->content = array_replace((array) $tab->content, $contents);
        $tab->id_tab = $slot;
        $tab->id_product = $info['id'];
        $tab->id_shop = $this->shopId();
        $tab->save();
        $this->purgeProductPages([$info['id']]);

        return ['saved' => true, 'product' => $info, 'slot' => $slot, 'title' => $this->byIso((array) $tab->title), 'content' => $this->byIso((array) $tab->content)];
    }

    /**
     * @return array{0: array{id: int, reference: string, name: string}, 1: array<int, string>, 2: array<int, string>}
     */
    public function checkProductTabSave(string $product, int $slot, ?array $title = null, ?array $content = null): array
    {
        return $this->checkSlotWrite($product, $slot, 'EVERPS_TAB_NB', 'onglets', $title, $content);
    }

    /**
     * @return array<string, mixed>
     */
    #[PsMcpTool(
        name: 'product_flag_save',
        title: 'Ever Block : flag d\'une fiche produit',
        description: 'Écrit le flag n° slot d\'un produit (slot de 1 au nombre de flags réglé dans Ever Block). Le flag affiche son contenu sans HTML et ne s\'affiche que si titre et contenu sont renseignés. Seules les langues fournies changent ; une chaîne vide efface la langue.',
        annotations: new PsMcpToolAnnotations(readOnlyHint: false, destructiveHint: false, idempotentHint: true, openWorldHint: false),
        meta: ['category' => 'catalog', 'qcd_permission' => 'AdminProducts:update', 'qcd_precheck' => 'checkProductFlagSave']
    )]
    #[PsMcpSchema(
        properties: ['product' => ['type' => 'string'], 'slot' => ['type' => 'integer', 'minimum' => 1], 'title' => self::LOCALIZED_SCHEMA, 'content' => self::LOCALIZED_SCHEMA],
        required: ['product', 'slot']
    )]
    public function productFlagSave(string $product, int $slot, ?array $title = null, ?array $content = null): array
    {
        [$info, $titles, $contents] = $this->checkProductFlagSave($product, $slot, $title, $content);
        $flag = ProductFlag::getByIdProductIdFlag($info['id'], $this->shopId(), $slot);
        $flag->title = array_replace((array) $flag->title, $titles);
        $flag->content = array_replace((array) $flag->content, $contents);
        $flag->id_flag = $slot;
        $flag->id_product = $info['id'];
        $flag->id_shop = $this->shopId();
        $flag->save();
        // Même invalidation que le back-office : caches des flags et greffe du hook des flags.
        \Hook::exec('actionObjectEverBlockFlagsUpdateAfter', ['object' => $flag], (int) \Module::getModuleIdByName('everblock'));
        $this->purgeProductPages([$info['id']]);

        return ['saved' => true, 'product' => $info, 'slot' => $slot, 'title' => $this->byIso((array) $flag->title), 'content' => $this->byIso((array) $flag->content)];
    }

    /**
     * @return array{0: array{id: int, reference: string, name: string}, 1: array<int, string>, 2: array<int, string>}
     */
    public function checkProductFlagSave(string $product, int $slot, ?array $title = null, ?array $content = null): array
    {
        return $this->checkSlotWrite($product, $slot, 'EVERPS_FLAG_NB', 'flags', $title, $content);
    }

    /**
     * @return array<string, mixed>
     */
    #[PsMcpTool(
        name: 'product_modal_save',
        title: 'Ever Block : modale d\'une fiche produit',
        description: 'Écrit le contenu et le libellé du bouton de la modale d\'un produit, par langue. Seules les langues fournies changent ; une chaîne vide efface la langue. Les fichiers joints restent gérés dans le back-office.',
        annotations: new PsMcpToolAnnotations(readOnlyHint: false, destructiveHint: false, idempotentHint: true, openWorldHint: false),
        meta: ['category' => 'catalog', 'qcd_permission' => 'AdminProducts:update', 'qcd_precheck' => 'checkProductModalSave']
    )]
    #[PsMcpSchema(
        properties: ['product' => ['type' => 'string'], 'content' => self::LOCALIZED_SCHEMA, 'buttonLabel' => self::LOCALIZED_SCHEMA],
        required: ['product']
    )]
    public function productModalSave(string $product, ?array $content = null, ?array $buttonLabel = null): array
    {
        [$info, $contents, $labels] = $this->checkProductModalSave($product, $content, $buttonLabel);
        $modal = Modal::getByProductId($info['id'], $this->shopId());
        $modal->content = array_replace((array) $modal->content, $contents);
        $modal->button_label = array_replace((array) $modal->button_label, $labels);
        $modal->id_product = $info['id'];
        $modal->id_shop = $this->shopId();
        $modal->save();
        $this->purgeProductPages([$info['id']]);

        return ['saved' => true, 'product' => $info, 'content' => $this->byIso((array) $modal->content), 'buttonLabel' => $this->byIso((array) $modal->button_label)];
    }

    /**
     * @return array{0: array{id: int, reference: string, name: string}, 1: array<int, string>, 2: array<int, string>}
     */
    public function checkProductModalSave(string $product, ?array $content = null, ?array $buttonLabel = null): array
    {
        if (($content ?? []) === [] && ($buttonLabel ?? []) === []) {
            throw new PsMcpToolCallException('Rien à écrire : content ou buttonLabel.');
        }

        return [$this->resolveProduct($product), $this->localizedInput($content), array_map('strip_tags', $this->localizedInput($buttonLabel, false))];
    }

    /* ------------------------------------------------------------------------------------
     * Recherche et remplacement dans les contenus
     * ---------------------------------------------------------------------------------- */

    /**
     * @param list<string>|null $scopes
     *
     * @return array<string, mixed>
     */
    #[PsMcpTool(
        name: 'content_search',
        title: 'Ever Block : rechercher dans les contenus',
        description: 'Cherche un texte (URL, nom, code, shortcode...) dans les contenus Ever Block de la boutique : blocs, shortcodes, pages, onglets, flags et modales produit. Renvoie chaque occurrence avec son contenu, sa langue, son champ et un extrait. 500 résultats au plus.',
        annotations: new PsMcpToolAnnotations(readOnlyHint: true, destructiveHint: false, idempotentHint: true, openWorldHint: false),
        meta: ['category' => 'content', 'qcd_permission' => 'AdminEverBlock:read']
    )]
    #[PsMcpSchema(
        properties: [
            'text' => ['type' => 'string', 'minLength' => 2],
            'scopes' => self::SCOPES_SCHEMA,
            'caseSensitive' => ['type' => 'boolean', 'default' => false],
        ],
        required: ['text']
    )]
    public function contentSearch(string $text, ?array $scopes = null, bool $caseSensitive = false): array
    {
        $matches = $this->findMatches($text, $this->checkScopes($scopes), $caseSensitive, self::MAX_MATCHES);

        return [
            'total' => count($matches),
            'truncated' => count($matches) >= self::MAX_MATCHES,
            'items' => array_map(fn (array $m): array => [
                'scope' => $m['scope'],
                'id' => $m['id'],
                'label' => $m['label'],
                'lang' => $m['iso'],
                'field' => $m['field'],
                'occurrences' => $m['count'],
                'excerpt' => $this->around($m['value'], $text, $caseSensitive),
            ], $matches),
        ];
    }

    /**
     * @param list<string>|null $scopes
     *
     * @return array<string, mixed>
     */
    #[PsMcpTool(
        name: 'content_replace',
        title: 'Ever Block : remplacer dans les contenus',
        description: 'Remplace un texte dans les contenus Ever Block (blocs, shortcodes, pages hors URL simplifiée, onglets, flags, modales), et nulle part ailleurs dans la base. dryRun=true (défaut) : calcule et liste les remplacements sans rien écrire ; dryRun=false : écrit, vide le cache du module et purge les fiches produit touchées. 1 000 champs au plus par appel.',
        annotations: new PsMcpToolAnnotations(readOnlyHint: false, destructiveHint: true, idempotentHint: false, openWorldHint: false),
        meta: ['category' => 'content', 'qcd_permission' => 'AdminEverBlockConfiguration:update', 'qcd_precheck' => 'checkContentReplace']
    )]
    #[PsMcpSchema(
        properties: [
            'search' => ['type' => 'string', 'minLength' => 2],
            'replace' => ['type' => 'string'],
            'scopes' => self::SCOPES_SCHEMA,
            'caseSensitive' => ['type' => 'boolean', 'default' => true],
            'dryRun' => ['type' => 'boolean', 'default' => true],
        ],
        required: ['search', 'replace']
    )]
    public function contentReplace(string $search, string $replace, ?array $scopes = null, bool $caseSensitive = true, bool $dryRun = true): array
    {
        $plan = $this->checkContentReplace($search, $replace, $scopes, $caseSensitive, $dryRun);
        $items = array_map(fn (array $m): array => [
            'scope' => $m['scope'],
            'id' => $m['id'],
            'label' => $m['label'],
            'lang' => $m['iso'],
            'field' => $m['field'],
            'occurrences' => $m['count'],
            'before' => $this->around($m['value'], $search, $caseSensitive),
            'after' => $this->around($m['new'], $replace === '' ? $search : $replace, $caseSensitive),
        ], $plan);
        $total = array_sum(array_column($plan, 'count'));

        if ($dryRun) {
            return ['dryRun' => true, 'fields' => count($plan), 'occurrences' => $total, 'items' => $items];
        }

        $db = \Db::getInstance();
        $products = [];
        $db->execute('START TRANSACTION');
        try {
            foreach ($plan as $m) {
                $scope = self::SCOPES[$m['scope']];
                $db->update(
                    $scope['lang'],
                    [$m['field'] => pSQL($m['new'], true)],
                    '`' . $scope['key'] . '` = ' . (int) $m['id'] . ' AND `id_lang` = ' . (int) $m['id_lang']
                );
                if ($scope['product']) {
                    $products[] = (int) $m['id_product'];
                }
            }
            $db->execute('COMMIT');
        } catch (\Throwable $exception) {
            $db->execute('ROLLBACK');
            throw new PsMcpToolCallException('Remplacement annulé : ' . $exception->getMessage());
        }
        EverblockCache::clearAllModuleCache();
        $this->purgeProductPages($products);

        return ['dryRun' => false, 'fields' => count($plan), 'occurrences' => $total, 'items' => $items];
    }

    /**
     * @param list<string>|null $scopes
     *
     * @return list<array<string, mixed>>
     */
    public function checkContentReplace(string $search, string $replace, ?array $scopes = null, bool $caseSensitive = true, bool $dryRun = true): array
    {
        if (mb_strlen($search) < 2) {
            throw new PsMcpToolCallException('Texte cherché trop court (2 caractères au moins).');
        }
        if ($search === $replace) {
            throw new PsMcpToolCallException('Le texte de remplacement est identique au texte cherché.');
        }
        $plan = $this->findMatches($search, $this->checkScopes($scopes), $caseSensitive, self::MAX_REPLACE_ROWS + 1);
        if (count($plan) > self::MAX_REPLACE_ROWS) {
            throw new PsMcpToolCallException('Plus de 1 000 champs concernés : restreindre scopes ou préciser le texte.');
        }
        foreach ($plan as &$m) {
            $m['new'] = $caseSensitive
                ? str_replace($search, $replace, $m['value'])
                : (string) preg_replace('/' . preg_quote($search, '/') . '/iu', str_replace(['\\', '$'], ['\\\\', '\\$'], $replace), $m['value']);
        }
        unset($m);

        return $plan;
    }

    /* ------------------------------------------------------------------------------------
     * Outils internes
     * ---------------------------------------------------------------------------------- */

    private const LOCALIZED_SCHEMA = ['type' => 'object', 'additionalProperties' => ['type' => 'string'], 'description' => 'Par langue, clés ISO : {"fr": "...", "en": "..."}.'];
    private const SCOPES_SCHEMA = ['type' => 'array', 'items' => ['type' => 'string', 'enum' => ['blocks', 'shortcodes', 'pages', 'tabs', 'flags', 'modals']], 'description' => 'Contenus couverts ; défaut : tous.'];

    private function loadPage(int $id): Page
    {
        $page = $id > 0 ? RepositoryProvider::get('everblock.repository.page')->find($id, $this->shopId()) : null;
        if (!$page instanceof Page) {
            throw new PsMcpToolCallException(sprintf('Page #%d introuvable dans cette boutique.', $id));
        }

        return $page;
    }

    /**
     * @return array<string, mixed>
     */
    private function pageToData(Page $page): array
    {
        $data = [
            'active' => $page->active,
            'position' => $page->position,
            'group_ids' => $this->decodeIds($page->groups),
        ];
        foreach (self::PAGE_FIELDS as $field) {
            foreach ((array) $page->{$field} as $langId => $value) {
                $data[$field . '_' . (int) $langId] = (string) $value;
            }
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function describePage(Page $page): array
    {
        $urls = [];
        foreach ((array) $page->link_rewrite as $langId => $rewrite) {
            $iso = \Language::getIsoById((int) $langId) ?: (string) $langId;
            $urls[$iso] = $this->pageUrl((int) $page->id, (string) $rewrite, (int) $langId);
        }
        $out = ['id' => (int) $page->id, 'active' => $page->active, 'position' => $page->position, 'groups' => $this->decodeIds($page->groups)];
        foreach (self::PAGE_FIELDS as $argument => $field) {
            $out[$argument] = $this->byIso((array) $page->{$field});
        }

        return $out + ['url' => $urls, 'coverImage' => (string) $page->cover_image, 'dateAdd' => $this->date($page->date_add), 'dateUpd' => $this->date($page->date_upd)];
    }

    private function pageUrl(int $id, string $rewrite, int $langId): string
    {
        try {
            return (string) \Context::getContext()->link->getModuleLink('everblock', 'page', ['id_everblock_page' => $id, 'rewrite' => $rewrite], null, $langId, $this->shopId());
        } catch (\Throwable) {
            return '';
        }
    }

    private function loadShortcode(string $shortcode): Shortcode
    {
        $value = trim($shortcode);
        $repository = RepositoryProvider::get('everblock.repository.shortcode');
        $id = ctype_digit($value) ? (int) $value : (int) \Db::getInstance()->getValue(
            'SELECT id_everblock_shortcode FROM ' . _DB_PREFIX_ . 'everblock_shortcode
             WHERE id_shop = ' . $this->shopId() . ' AND shortcode = "' . pSQL(str_starts_with($value, '[') ? $value : '[' . $value . ']') . '"'
        );
        $item = $id > 0 ? $repository->find($id, $this->shopId()) : null;
        if (!$item instanceof Shortcode) {
            throw new PsMcpToolCallException(sprintf('Shortcode « %s » introuvable.', $shortcode));
        }

        return $item;
    }

    /**
     * @return array<string, mixed>
     */
    private function describeShortcode(Shortcode $item): array
    {
        return ['id' => (int) $item->id, 'shortcode' => $item->shortcode, 'title' => $this->byIso((array) $item->title), 'content' => $this->byIso((array) $item->content)];
    }

    private function slotCount(string $key): int
    {
        return max(1, (int) \Configuration::get($key));
    }

    /**
     * @return array{0: array{id: int, reference: string, name: string}, 1: array<int, string>, 2: array<int, string>}
     */
    private function checkSlotWrite(string $product, int $slot, string $key, string $label, ?array $title, ?array $content): array
    {
        $max = $this->slotCount($key);
        if ($slot < 1 || $slot > $max) {
            throw new PsMcpToolCallException(sprintf('Emplacement %d hors limites : %d %s réglés dans Ever Block (1 à %d).', $slot, $max, $label, $max));
        }
        if (($title ?? []) === [] && ($content ?? []) === []) {
            throw new PsMcpToolCallException('Rien à écrire : title ou content.');
        }

        return [$this->resolveProduct($product), array_map('strip_tags', $this->localizedInput($title, false)), $this->localizedInput($content)];
    }

    /**
     * @param list<string>|null $scopes
     *
     * @return list<string>
     */
    private function checkScopes(?array $scopes): array
    {
        if ($scopes === null || $scopes === []) {
            return array_keys(self::SCOPES);
        }
        $unknown = array_diff($scopes, array_keys(self::SCOPES));
        if ($unknown !== []) {
            throw new PsMcpToolCallException('scopes inconnus : ' . implode(', ', $unknown) . ' (blocks, shortcodes, pages, tabs, flags, modals).');
        }

        return array_values(array_unique($scopes));
    }

    /**
     * @param list<string> $scopes
     *
     * @return list<array<string, mixed>>
     */
    private function findMatches(string $text, array $scopes, bool $caseSensitive, int $limit): array
    {
        $db = \Db::getInstance();
        $like = pSQL('%' . addcslashes($text, '%_') . '%', true);
        $shopId = $this->shopId();
        $isos = [];
        foreach (\Language::getLanguages(false) as $language) {
            $isos[(int) $language['id_lang']] = (string) $language['iso_code'];
        }

        $out = [];
        foreach ($scopes as $scopeName) {
            $scope = self::SCOPES[$scopeName];
            $conditions = [];
            foreach ($scope['fields'] as $field) {
                $conditions[] = ($caseSensitive ? 'BINARY l.`' . $field . '`' : 'l.`' . $field . '`') . ' LIKE "' . $like . '"';
            }
            $rows = $db->executeS(
                'SELECT l.*, ' . $scope['label'] . ' AS mcp_label' . ($scope['product'] ? ', p.id_product AS mcp_product' : '') . '
                 FROM ' . _DB_PREFIX_ . $scope['lang'] . ' l
                 INNER JOIN ' . _DB_PREFIX_ . $scope['parent'] . ' p ON p.`' . $scope['key'] . '` = l.`' . $scope['key'] . '` AND p.id_shop = ' . $shopId . '
                 WHERE ' . implode(' OR ', $conditions) . '
                 LIMIT ' . (int) $limit
            ) ?: [];
            foreach ($rows as $row) {
                foreach ($scope['fields'] as $field) {
                    $value = (string) ($row[$field] ?? '');
                    $count = $caseSensitive ? substr_count($value, $text) : (int) preg_match_all('/' . preg_quote($text, '/') . '/iu', $value);
                    if ($count === 0) {
                        continue;
                    }
                    $out[] = [
                        'scope' => $scopeName,
                        'id' => (int) $row[$scope['key']],
                        'id_lang' => (int) $row['id_lang'],
                        'iso' => $isos[(int) $row['id_lang']] ?? (string) $row['id_lang'],
                        'label' => (string) $row['mcp_label'],
                        'id_product' => (int) ($row['mcp_product'] ?? 0),
                        'field' => $field,
                        'count' => $count,
                        'value' => $value,
                    ];
                    if (count($out) >= $limit) {
                        return $out;
                    }
                }
            }
        }

        return $out;
    }

    private function around(string $value, string $needle, bool $caseSensitive): string
    {
        $pos = $caseSensitive ? mb_strpos($value, $needle) : mb_stripos($value, $needle);
        if ($pos === false) {
            return mb_substr($value, 0, 160);
        }
        $start = max(0, $pos - 70);

        return ($start > 0 ? '…' : '') . mb_substr($value, $start, mb_strlen($needle) + 140) . ($start + mb_strlen($needle) + 140 < mb_strlen($value) ? '…' : '');
    }

    /**
     * @return int[]
     */
    private function decodeIds(mixed $json): array
    {
        $decoded = json_decode((string) $json, true);

        return is_array($decoded) ? array_values(array_filter(array_map('intval', $decoded), static fn (int $v): bool => $v > 0)) : [];
    }
}
