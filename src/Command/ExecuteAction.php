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

namespace Everblock\Tools\Command;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Configuration;
use Currency;
use Db;
use DbQuery;
use Everblock\Tools\Service\ImportFile;
use EverblockCache;
use EverblockTools;
use Language;
use Module;
use PrestaShop\PrestaShop\Adapter\LegacyContext as ContextAdapter;
use Product;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Validate;

class ExecuteAction extends Command
{
    public const SUCCESS = 0;
    public const FAILURE = 1;
    public const INVALID = 2;
    public const ABORTED = 3;

    private $allowedActions = [
        'getrandomcomment' => [
            'label' => 'Random console comment',
            'description' => 'Displays a formatted humorous message in the console.',
            'parameters' => [],
        ],
        'saveblocks' => [
            'label' => 'Backup Everblock data',
            'description' => 'Exports module tables and backs up CSS/JS assets.',
            'parameters' => ['idshop (optional)'],
        ],
        'restoreblocks' => [
            'label' => 'Restore Everblock data',
            'description' => 'Restores module tables and assets from a backup.',
            'parameters' => [],
        ],
        'removeinlinecsstags' => [
            'label' => 'Strip inline CSS',
            'description' => 'Removes inline style attributes from product descriptions.',
            'parameters' => ['idshop (optional)'],
        ],
        'droplogs' => [
            'label' => 'Purge PrestaShop logs',
            'description' => 'Clears the native PrestaShop logs table.',
            'parameters' => [],
        ],
        'refreshtokens' => [
            'label' => 'Refresh Instagram token',
            'description' => 'Renews the Instagram token and clears the related cache.',
            'parameters' => [],
        ],
        'fetchinstagramimages' => [
            'label' => 'Download Instagram medias',
            'description' => 'Downloads configured Instagram media files and stores them locally.',
            'parameters' => [],
        ],
        'securewithapache' => [
            'label' => 'Protect module folders',
            'description' => 'Adds Apache rules to protect the module folders.',
            'parameters' => [],
        ],
        'saveproducts' => [
            'label' => 'Resave all products',
            'description' => 'Re-saves every product in the shop.',
            'parameters' => ['idshop (optional)'],
        ],
        'generateproducts' => [
            'label' => 'Generate demo products',
            'description' => 'Creates dummy products with images for the selected shop.',
            'parameters' => ['idshop (optional)'],
        ],
        'webpprettyblock' => [
            'label' => 'Convert Prettyblocks images to WebP',
            'description' => 'Converts every Prettyblock image to WebP.',
            'parameters' => [],
        ],
        'removehn' => [
            'label' => 'Replace Hn tags',
            'description' => 'Replaces Hn tags with paragraph elements carrying CSS classes.',
            'parameters' => ['idshop (optional)'],
        ],
        'duplicateblockslang' => [
            'label' => 'Duplicate blocks between languages',
            'description' => 'Copies block content and custom code from one language to another.',
            'parameters' => ['idshop (optional)', 'fromlang (required)', 'tolang (required)'],
        ],
        'fetchwordpressposts' => [
            'label' => 'Fetch WordPress posts',
            'description' => 'Fetches the configured WordPress posts.',
            'parameters' => [],
        ],
        'checkdatabase' => [
            'label' => 'Check module database',
            'description' => 'Installs missing tables and columns, removes obsolete files.',
            'parameters' => [],
        ],
        'dropunusedlangs' => [
            'label' => 'Drop unused languages',
            'description' => 'Removes orphan translations from core multilingual tables.',
            'parameters' => [],
        ],
        'clearcache' => [
            'label' => 'Clear PrestaShop cache',
            'description' => 'Flushes all native caches (Smarty, XML, filesystem).',
            'parameters' => [],
        ],
        'warmup' => [
            'label' => 'Warm front-office pages',
            'description' => 'Preloads the storefront for every active language.',
            'parameters' => ['--url (optional)'],
        ],
    ];

    public function __construct(KernelInterface $kernel)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('everblock:tools:execute');
        $this->setDescription('Execute action (use --list to display the available actions)');
        $this->addArgument('action', InputArgument::OPTIONAL, sprintf('Action to execute (Allowed actions: %s).', implode(' / ', array_keys($this->allowedActions))));
        $this->addOption('list', null, InputOption::VALUE_NONE, 'List available actions and exit.');
        $this->addArgument('idshop id', InputArgument::OPTIONAL, 'Shop ID');
        $this->addArgument('fromlang id', InputArgument::OPTIONAL, 'Source language ID');
        $this->addArgument('tolang id', InputArgument::OPTIONAL, 'Target language ID');
        $this->addOption('url', null, InputOption::VALUE_REQUIRED, 'Override the base URL used by actions such as warmup.');
        $help = "Use the --list option to display the available actions.\n";
        foreach ($this->allowedActions as $name => $action) {
            $parameters = empty($action['parameters']) ? 'no parameter' : implode(', ', $action['parameters']);
            $help .= sprintf('- %s: %s (%s)\n', $name, $action['description'], $parameters);
        }
        $this->setHelp($help);
        $this->module = Module::getInstanceByName('everblock');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('list')) {
            $table = new Table($output);
            $table->setHeaders(['Action', 'Description', 'Parameters']);
            foreach ($this->allowedActions as $name => $action) {
                $parameters = empty($action['parameters']) ? '—' : implode(', ', $action['parameters']);
                $table->addRow([
                    $name,
                    sprintf('%s — %s', $action['label'], $action['description']),
                    $parameters,
                ]);
            }
            $table->render();

            return self::SUCCESS;
        }

        $action = $input->getArgument('action');
        $idShop = $input->getArgument('idshop id');
        $idLangFrom = $input->getArgument('fromlang id');
        $idLangTo = $input->getArgument('tolang id');
        $baseUrlOverride = $input->getOption('url');

        if (!$action) {
            $output->writeln('<warning>No action provided. Use the --list option to display available actions.</warning>');

            return self::ABORTED;
        }
        $context = (new ContextAdapter())->getContext();
        $context->employee = new \Employee(1);
        $context->currency = new Currency(
            (int) Configuration::get('PS_CURRENCY_DEFAULT')
        );
        if ($idShop && $idShop >= 1) {
            $shop = new \Shop(
                (int) $idShop
            );
            if (!\Validate::isLoadedObject($shop)) {
                $output->writeln('<warning>Shop not found</warning>');
                return self::ABORTED;
            }
        } else {
            $shop = $context->shop;
            if (!\Validate::isLoadedObject($shop)) {
                $shop = new \Shop((int) \Configuration::get('PS_SHOP_DEFAULT'));
            }
        }
        if (!array_key_exists($action, $this->allowedActions)) {
            $output->writeln(sprintf('<warning>Unknown action "%s". Use the --list option to display available actions.</warning>', $action));
            return self::ABORTED;
        }
        if ($action === 'getrandomcomment') {
            $output->writeln(
                $this->getRandomFunnyComment($output)
            );
            return self::SUCCESS;
        }
        if ($action === 'refreshtokens') {
            // Instagram
            $newToken = EverblockTools::refreshInstagramToken();
            if ($newToken) {
                EverblockCache::cacheDropByPattern('fetchInstagramImages');
                $output->writeln(
                    '<success>' . $newToken . '</success>'
                );
            } else {
                $output->writeln('<warning>Instagram token reset failed</warning>');
            }
            return self::SUCCESS;
        }
        if ($action === 'securewithapache') {
            // Instagram
            $secured = EverblockTools::secureModuleFoldersWithApache();
            if (is_array($secured)
                && isset($secured['postErrors'])
                && count($secured['postErrors']) > 0
            ) {
                foreach ($secured['postErrors'] as $postErrors) {
                    $output->writeln('<warning>' . $postErrors . '</warning>');
                }
            }
            if (is_array($secured)
                && isset($secured['querySuccess'])
                && count($secured['querySuccess']) > 0
            ) {
                foreach ($secured['querySuccess'] as $querySuccess) {
                    $output->writeln(
                        '<success>' . $querySuccess . '</success>'
                    );
                }
            }
            return self::SUCCESS;
        }
        if ($action === 'fetchinstagramimages') {
            $output->writeln('<comment>Fetching Instagram medias…</comment>');
            $images = EverblockTools::fetchInstagramImages();
            $count = is_array($images) ? count($images) : 0;
            $output->writeln(sprintf('<success>%d media files processed</success>', $count));

            return self::SUCCESS;
        }
        if ($action === 'saveblocks') {
            $backuped = EverblockTools::exportModuleTablesSQL();
            $configBackuped = EverblockTools::exportConfigurationSQL();
            if ((bool) $backuped === true && (bool) $configBackuped === true) {
                try {
                    $modulePath = _PS_MODULE_DIR_ . 'everblock/';
                    $this->copyDirectory($modulePath . 'views/css', $modulePath . 'views/backup/css');
                    $this->copyDirectory($modulePath . 'views/js', $modulePath . 'views/backup/js');
                    $output->writeln('<success>Backup done</success>');
                } catch (\Exception $e) {
                    $output->writeln('<warning>Backup failed: ' . $e->getMessage() . '</warning>');
                    return self::FAILURE;
                }
                return self::SUCCESS;
            } else {
                $output->writeln('<warning>Backup failed</warning>');
                return self::FAILURE;
            }
        }
        if ($action === 'restoreblocks') {
            $restored = EverblockTools::restoreModuleTablesFromBackup();
            if ((bool) $restored === true) {
                try {
                    $modulePath = _PS_MODULE_DIR_ . 'everblock/';
                    $this->restoreDirectory($modulePath . 'views/backup/css', $modulePath . 'views/css');
                    $this->restoreDirectory($modulePath . 'views/backup/js', $modulePath . 'views/js');
                    $output->writeln('<success>Blocks restoration done</success>');
                } catch (\Exception $e) {
                    $output->writeln('<warning>Blocks restoration failed: ' . $e->getMessage() . '</warning>');
                    return self::FAILURE;
                }
                return self::SUCCESS;
            } else {
                $output->writeln('<warning>Blocks restoration failed</warning>');
                return self::FAILURE;
            }
        }
        if ($action === 'droplogs') {
            $purged = EverblockTools::purgeNativePrestashopLogsTable();
            if ((bool) $purged === true) {
                $output->writeln(
                    '<success>Logs table purged</success>'
                );
                return self::SUCCESS;
            } else {
                $output->writeln(
                    '<warning>Logs table NOT purged</warning>'
                );
                return self::FAILURE;
            }
        }
        if ($action === 'removeinlinecsstags') {
            $output->writeln('<comment>Start removing inline CSS tags</comment>');
            $sql = new DbQuery();
            $sql->select('id_product');
            $sql->from('product_shop');
            $sql->where('id_shop = ' . (int) $shop->id);
            $results = Db::getInstance()->executeS($sql);
            $pattern = '/style=("|\')(?:\\\\.|[^\\\\])*?\1/i';
            foreach ($results as $result) {
                $product = new Product(
                    (int) $result['id_product']
                );
                foreach (Language::getLanguages(false) as $lang) {
                    $product->description[(int) $lang['id_lang']] = preg_replace(
                        $pattern,
                        '',
                        $product->description[(int) $lang['id_lang']]
                    );
                    $product->description_short[(int) $lang['id_lang']] = preg_replace(
                        $pattern,
                        '',
                        $product->description_short[(int) $lang['id_lang']]
                    );
                }
                try {
                    $product->save();
                    $output->writeln('<comment>Product ' . $product->id . ' has been saved</comment>');
                } catch (Exception $e) {
                    $output->writeln('<warning>' . $e->getMessage() . '</warning>');
                    continue;
                }
            }
            $output->writeln('<comment>Inline styles have been removed from all products</comment>');
            return self::SUCCESS;
        }
        if ($action == 'webpprettyblock') {
            $output->writeln('<comment>Start converting Prettyblock images to webp</comment>');
            EverblockTools::convertAllPrettyblocksImagesToWebP();
            $output->writeln('<comment>Prettyblock images have been improved into webp</comment>');
            return self::SUCCESS;
        }
        if ($action === 'saveproducts') {
            $output->writeln('<comment>Start saving all products in the shop</comment>');

            $sql = new DbQuery();
            $sql->select('id_product');
            $sql->from('product_shop');
            $sql->where('id_shop = ' . (int) $shop->id);

            $results = Db::getInstance()->executeS($sql);

            foreach ($results as $result) {
                $product = new Product((int) $result['id_product']);

                if (!Validate::isLoadedObject($product)) {
                    $output->writeln('<warning>Product with ID ' . (int) $result['id_product'] . ' not found</warning>');
                    continue;
                }

                try {
                    $product->save();
                    $output->writeln('<success>Product ' . $product->id . ' has been saved successfully</success>');
                } catch (Exception $e) {
                    $output->writeln('<warning>Failed to save product ' . $product->id . ': ' . $e->getMessage() . '</warning>');
                }
            }

            $output->writeln('<comment>All products have been processed</comment>');
            return self::SUCCESS;
        }
        if ($action === 'generateproducts') {
            $output->writeln('<comment>Generating demo products for shop ' . (int) $shop->id . '</comment>');
            $generated = EverblockTools::generateProducts((int) $shop->id);
            if ($generated) {
                $output->writeln('<success>Demo products created successfully</success>');

                return self::SUCCESS;
            }

            $output->writeln('<warning>Failed to generate demo products</warning>');

            return self::FAILURE;
        }
        if ($action === 'duplicateblockslang') {
            if (!$idLangFrom || !$idLangTo) {
                $output->writeln('<warning>Missing language IDs</warning>');
                return self::ABORTED;
            }
            $langFrom = new Language((int) $idLangFrom);
            $langTo = new Language((int) $idLangTo);
            if (!Validate::isLoadedObject($langFrom) || !Validate::isLoadedObject($langTo)) {
                $output->writeln('<warning>Invalid language ID(s)</warning>');
                return self::ABORTED;
            }
            $sql = new DbQuery();
            $sql->select('id_everblock');
            $sql->from('everblock');
            $sql->where('id_shop = ' . (int) $shop->id);
            $blocks = Db::getInstance()->executeS($sql);
            foreach ($blocks as $blk) {
                $block = new EverBlockClass((int) $blk['id_everblock']);
                if (isset($block->content[$idLangFrom])) {
                    $block->content[(int) $idLangTo] = $block->content[$idLangFrom];
                }
                if (isset($block->custom_code[$idLangFrom])) {
                    $block->custom_code[(int) $idLangTo] = $block->custom_code[$idLangFrom];
                }
                try {
                    $block->save();
                    $output->writeln('<comment>Duplicated block ' . $block->id . ' from lang ' . (int) $idLangFrom . ' to ' . (int) $idLangTo . '</comment>');
                } catch (Exception $e) {
                    $output->writeln('<warning>' . $e->getMessage() . '</warning>');
                }
            }
            $output->writeln('<success>All blocks duplicated from lang ' . (int) $idLangFrom . ' to ' . (int) $idLangTo . '</success>');
            return self::SUCCESS;
        }
        if ($action === 'removehn') {
            $output->writeln('<comment>Start replacing all Hn tags in product, category and manufacturer descriptions</comment>');
            $pattern = '/<h([1-6])\b([^>]*)>(.*?)<\/h\1>/is';
            $callback = function ($matches) {
                return sprintf('<p class="h%s"%s>%s</p>', $matches[1], $matches[2], $matches[3]);
            };

            // Produits
            $output->writeln('<comment>Processing products…</comment>');
            $sql = new DbQuery();
            $sql->select('id_product');
            $sql->from('product_shop');
            $sql->where('id_shop = ' . (int) $shop->id);
            $results = Db::getInstance()->executeS($sql);

            foreach ($results as $result) {
                $idProduct = (int) $result['id_product'];
                foreach (Language::getLanguages(false) as $lang) {
                    $idLang = (int) $lang['id_lang'];
                    $row = Db::getInstance()->getRow(
                        'SELECT description, description_short FROM ' . _DB_PREFIX_ . 'product_lang WHERE id_product = ' . $idProduct . ' AND id_lang = ' . $idLang . ' AND id_shop = ' . (int) $shop->id
                    );
                    if (!$row) {
                        continue;
                    }
                    $newDesc = preg_replace_callback($pattern, $callback, $row['description']);
                    $newShort = preg_replace_callback($pattern, $callback, $row['description_short']);
                    Db::getInstance()->update(
                        'product_lang',
                        [
                            'description' => pSQL($newDesc, true),
                            'description_short' => pSQL($newShort, true),
                        ],
                        'id_product = ' . $idProduct . ' AND id_lang = ' . $idLang . ' AND id_shop = ' . (int) $shop->id
                    );
                }
                $output->writeln('<comment>Updated product ' . $idProduct . '</comment>');
            }

            // Catégories
            $output->writeln('<comment>Processing categories…</comment>');
            $categories = Db::getInstance()->executeS('SELECT id_category FROM ' . _DB_PREFIX_ . 'category_shop WHERE id_shop = ' . (int) $shop->id);
            foreach ($categories as $cat) {
                $idCategory = (int) $cat['id_category'];
                foreach (Language::getLanguages(false) as $lang) {
                    $idLang = (int) $lang['id_lang'];
                    $row = Db::getInstance()->getRow(
                        'SELECT description FROM ' . _DB_PREFIX_ . 'category_lang WHERE id_category = ' . $idCategory . ' AND id_lang = ' . $idLang . ' AND id_shop = ' . (int) $shop->id
                    );
                    if (!$row) {
                        continue;
                    }
                    $newDesc = preg_replace_callback($pattern, $callback, $row['description']);
                    Db::getInstance()->update(
                        'category_lang',
                        ['description' => pSQL($newDesc, true)],
                        'id_category = ' . $idCategory . ' AND id_lang = ' . $idLang . ' AND id_shop = ' . (int) $shop->id
                    );
                }
                $output->writeln('<comment>Updated category ' . $idCategory . '</comment>');
            }

            // Marques (Fabricants)
            $output->writeln('<comment>Processing manufacturers…</comment>');
            $manufacturers = Db::getInstance()->executeS('SELECT id_manufacturer FROM ' . _DB_PREFIX_ . 'manufacturer');
            foreach ($manufacturers as $manu) {
                $idManufacturer = (int) $manu['id_manufacturer'];
                foreach (Language::getLanguages(false) as $lang) {
                    $idLang = (int) $lang['id_lang'];
                    $row = Db::getInstance()->getRow(
                        'SELECT description FROM ' . _DB_PREFIX_ . 'manufacturer_lang WHERE id_manufacturer = ' . $idManufacturer . ' AND id_lang = ' . $idLang
                    );
                    if (!$row) {
                        continue;
                    }
                    $newDesc = preg_replace_callback($pattern, $callback, $row['description']);
                    Db::getInstance()->update(
                        'manufacturer_lang',
                        ['description' => pSQL($newDesc, true)],
                        'id_manufacturer = ' . $idManufacturer . ' AND id_lang = ' . $idLang
                    );
                }
                $output->writeln('<comment>Updated manufacturer ' . $idManufacturer . '</comment>');
            }

            $output->writeln('<success>All Hn tags replaced by <p class="hN"> in product, category, and manufacturer descriptions.</success>');
            return self::SUCCESS;
        }
        if ($action === 'fetchwordpressposts') {
            EverblockTools::fetchWordpressPosts();
            $output->writeln('<comment>WordPress posts fetched</comment>');
            return self::SUCCESS;
        }
        if ($action === 'checkdatabase') {
            EverblockTools::checkAndFixDatabase();
            $output->writeln('<success>Database schema verified successfully</success>');

            return self::SUCCESS;
        }
        if ($action === 'dropunusedlangs') {
            $output->writeln('<comment>Removing orphan translations…</comment>');
            $result = EverblockTools::dropUnusedLangs();
            $hasErrors = false;
            if (!empty($result['querySuccess'])) {
                foreach ($result['querySuccess'] as $message) {
                    $output->writeln('<success>' . $message . '</success>');
                }
            }
            if (!empty($result['postErrors'])) {
                $hasErrors = true;
                foreach ($result['postErrors'] as $error) {
                    $output->writeln('<warning>' . $error . '</warning>');
                }
            }

            return $hasErrors ? self::FAILURE : self::SUCCESS;
        }
        if ($action === 'clearcache') {
            \Tools::clearAllCache();
            $output->writeln('<success>All PrestaShop caches cleared</success>');

            return self::SUCCESS;
        }
        if ($action === 'warmup') {
            $baseUrl = $baseUrlOverride ?: \Tools::getShopDomainSsl(true) . __PS_BASE_URI__;
            $output->writeln('<comment>Warming up front-office pages from ' . $baseUrl . '</comment>');
            EverblockTools::warmup($baseUrl);
            $output->writeln('<success>Warmup requests dispatched</success>');

            return self::SUCCESS;
        }
        return self::ABORTED;
    }

    /**
     * Copy a directory and its contents recursively
     *
     * @param string $src Source directory
     * @param string $dst Destination directory
     * @return void
     */
    protected function copyDirectory($src, $dst)
    {
        $dir = opendir($src);
        if (!is_dir($dst)) {
            mkdir($dst, 0755, true);
        }
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->copyDirectory($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    /**
     * Restore a directory and its contents recursively
     *
     * @param string $src Source directory (backup)
     * @param string $dst Destination directory (original)
     * @return void
     */
    protected function restoreDirectory($src, $dst)
    {
        $dir = opendir($src);
        if (!is_dir($dst)) {
            mkdir($dst, 0755, true);
        }
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->restoreDirectory($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    /**
     * Get funny random comment
     * Can be useful for setting comment style example
     * @see https://symfony.com/doc/current/console/coloring.html
    */
    protected function getRandomFunnyComment($output)
    {
        $outputStyle = new OutputFormatterStyle('green', 'white', ['bold', 'blink']);
        $output->getFormatter()->setStyle('styled', $outputStyle);
        $funnyComments = [];
        $funnyComments[] = "<styled>
            IMPORT ENDED, HAVE A BEER
                         .sssssssss.
                   .sssssssssssssssssss
                 sssssssssssssssssssssssss
                ssssssssssssssssssssssssssss
                 @@sssssssssssssssssssssss@ss
                 |s@@@@sssssssssssssss@@@@s|s
          _______|sssss@@@@@sssss@@@@@sssss|s
        /         sssssssss@sssss@sssssssss|s
       /  .------+.ssssssss@sssss@ssssssss.|
      /  /       |...sssssss@sss@sssssss...|
     |  |        |.......sss@sss@ssss......|
     |  |        |..........s@ss@sss.......|
     |  |        |...........@ss@..........|
      \  \       |............ss@..........|
       \  '------+...........ss@...........|
        \________ .........................|
                 |.........................|
                /...........................\
               |.............................|
                  |.......................|
                      |...............|
                </styled>";
        $funnyComments[] = "<styled>
            IMPORT ENDED, MEOW
              ^~^  ,
             ('Y') )
             /   \/
            (\|||/)
            </styled>";
        $funnyComments[] = "<styled>
            IMPORT ENDED, D'OH
            ...___.._____
            ....‘/,-Y”.............“~-.
            ..l.Y.......................^.
            ./\............................_\_
            i.................... ___/“....“\
            |.................../“....“\ .....o !
            l..................].......o !__../
            .\..._..._.........\..___./...... “~\
            ..X...\/...\.....................___./
            .(. \.___......_.....--~~“.......~`-.
            ....`.Z,--........./.................\
            .......\__....(......../..........______)
            ...........\.........l......../-----~~”/
            ............Y.......\................/
            ............|........“x______.^
            ............|.............\
            ............j...............Y
            </styled>";
        $funnyComments[] = '<styled>
            |￣￣￣￣￣￣￣￣￣ |
            |      IMPORT      |
            |      ENDED!      |
            |__________________|
            (\__/) ||
            (•ㅅ•) ||
            / 　 づ"
            </styled>';
        $funnyComments[] = "<styled>
            Import (•_•)
            has been ( •_•)>⌐■-■
            ended (⌐■_■)
            </styled>";
        $funnyComments[] = "<styled>
            ......_________________________
            ....../ `---___________--------    | ============= IMPORT-ENDED-BULLET !
            ...../_==o;;;;;;;;______________|
            .....), ---.(_(__) /
            .......// (..) ), /--
            ... //___//---
            .. //___//
            .//___//
            //___//
            </styled>";
        $funnyComments[] = "<styled>
               IMPORT ENDED
           ._________________.
           |.---------------.|
           ||               ||
           ||   -._ .-.     ||
           ||   -._| | |    ||
           ||   -._|'|'|    ||
           ||   -._|.-.|    ||
           ||_______________||
           /.-.-.-.-.-.-.-.-.\
          /.-.-.-.-.-.-.-.-.-.\
         /.-.-.-.-.-.-.-.-.-.-.\
        /______/__________\___o_\ 
        \_______________________/
         </styled>";
        $k = array_rand($funnyComments);
        return $funnyComments[$k];
    }
}
