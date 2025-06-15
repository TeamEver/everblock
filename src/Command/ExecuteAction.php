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

use PrestaShop\PrestaShop\Adapter\LegacyContext as ContextAdapter;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Everblock\Tools\Service\ImportFile;
use Currency;
use Configuration;
use DbQuery;
use Db;
use Product;
use Language;
use Module;
use Validate;
use EverblockTools;
use EverblockCache;

class ExecuteAction extends Command
{
    public const SUCCESS = 0;
    public const FAILURE = 1;
    public const INVALID = 2;
    public const ABORTED = 3;

    private $allowedActions = [
        'getrandomcomment',
        'saveblocks',
        'restoreblocks',
        'removeinlinecsstags',
        'droplogs',
        'refreshtokens',
        'securewithapache',
        'saveproducts',
        'webpprettyblock',
        'removehn',
        'duplicateblockslang',
    ];

    public function __construct(KernelInterface $kernel)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('everblock:tools:execute');
        $this->setDescription('Execute action');
        $this->addArgument('action', InputArgument::REQUIRED, sprintf('Action to execute (Allowed actions: %s).', implode(' / ', $this->allowedActions)));
        $this->addArgument('idshop id', InputArgument::OPTIONAL, 'Shop ID');
        $this->addArgument('fromlang id', InputArgument::OPTIONAL, 'Source language ID');
        $this->addArgument('tolang id', InputArgument::OPTIONAL, 'Target language ID');
        $help = sprintf("Allowed actions: %s\n", implode(' / ', $this->allowedActions));
        $this->setHelp($help);
        $this->module = Module::getInstanceByName('everblock');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = $input->getArgument('action');
        $idShop = $input->getArgument('idshop id');
        $idLangFrom = $input->getArgument('fromlang id');
        $idLangTo = $input->getArgument('tolang id');
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
        if (!in_array($action, $this->allowedActions)) {
            $output->writeln('<warning>Unkown action</warning>');
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
        if ($action === 'saveblocks') {
            $backuped = EverblockTools::exportModuleTablesSQL();
            if ((bool) $backuped === true) {
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

            // Produits
            $output->writeln('<comment>Processing products…</comment>');
            $sql = new DbQuery();
            $sql->select('id_product');
            $sql->from('product_shop');
            $sql->where('id_shop = ' . (int) $shop->id);
            $results = Db::getInstance()->executeS($sql);

            foreach ($results as $result) {
                $product = new Product((int) $result['id_product']);
                foreach (Language::getLanguages(false) as $lang) {
                    $product->description[$lang['id_lang']] = preg_replace_callback($pattern, function ($matches) {
                        return sprintf('<p class="h%s"%s>%s</p>', $matches[1], $matches[2], $matches[3]);
                    }, $product->description[$lang['id_lang']]);

                    $product->description_short[$lang['id_lang']] = preg_replace_callback($pattern, function ($matches) {
                        return sprintf('<p class="h%s"%s>%s</p>', $matches[1], $matches[2], $matches[3]);
                    }, $product->description_short[$lang['id_lang']]);
                }
                try {
                    $product->save();
                    $output->writeln('<comment>Updated product ' . $product->id . '</comment>');
                } catch (Exception $e) {
                    $output->writeln('<warning>Product ' . $product->id . ' failed: ' . $e->getMessage() . '</warning>');
                }
            }

            // Catégories
            $output->writeln('<comment>Processing categories…</comment>');
            $categories = \Category::getCategories($shop->id, false, false);
            foreach ($categories as $cat) {
                $category = new \Category($cat['id_category']);
                foreach (Language::getLanguages(false) as $lang) {
                    $category->description[$lang['id_lang']] = preg_replace_callback($pattern, function ($matches) {
                        return sprintf('<p class="h%s"%s>%s</p>', $matches[1], $matches[2], $matches[3]);
                    }, $category->description[$lang['id_lang']]);
                }
                try {
                    $category->save();
                    $output->writeln('<comment>Updated category ' . $category->id . '</comment>');
                } catch (Exception $e) {
                    $output->writeln('<warning>Category ' . $category->id . ' failed: ' . $e->getMessage() . '</warning>');
                }
            }

            // Marques (Fabricants)
            $output->writeln('<comment>Processing manufacturers…</comment>');
            $manufacturers = \Manufacturer::getManufacturers(false, $context->language->id, true, false, false);
            foreach ($manufacturers as $manu) {
                $manufacturer = new \Manufacturer($manu['id_manufacturer']);
                foreach (Language::getLanguages(false) as $lang) {
                    $manufacturer->description[$lang['id_lang']] = preg_replace_callback($pattern, function ($matches) {
                        return sprintf('<p class="h%s"%s>%s</p>', $matches[1], $matches[2], $matches[3]);
                    }, $manufacturer->description[$lang['id_lang']]);
                }
                try {
                    $manufacturer->save();
                    $output->writeln('<comment>Updated manufacturer ' . $manufacturer->id . '</comment>');
                } catch (Exception $e) {
                    $output->writeln('<warning>Manufacturer ' . $manufacturer->id . ' failed: ' . $e->getMessage() . '</warning>');
                }
            }

            $output->writeln('<success>All Hn tags replaced by <p class="hN"> in product, category, and manufacturer descriptions.</success>');
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
