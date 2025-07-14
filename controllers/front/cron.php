<?php
/**
 * 2019-2023 Team Ever
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
 *  @copyright 2019-2023 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

if (version_compare(_PS_VERSION_, '9.0.0', '>=')) {
    require_once _PS_ROOT_DIR_ . '/app/AdminKernel.php';
} else {
    require_once _PS_ROOT_DIR_ . '/app/AppKernel.php';
}

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class EverblockcronModuleFrontController extends ModuleFrontController
{
    private $allowedActions = [
        'getrandomcomment',
        'saveblocks',
        'restoreblocks',
        'removeinlinecsstags',
        'droplogs',
        'refreshtokens',
        'securewithapache',
        'fetchwordpressposts',
    ];

    public function initContent()
    {
        if (!Tools::getIsset('evertoken')
            || $this->module->encrypt($this->module->name . '/evercron') != Tools::getValue('evertoken')
            || !Module::isInstalled($this->module->name)
        ) {
            Tools::redirect('index.php');
        }
        if (!Tools::getValue('action')
            || empty(Tools::getValue('action'))
        ) {
            Tools::redirect('index.php');
        }
        if (!in_array(Tools::getValue('action'), $this->allowedActions)) {
            Tools::redirect('index.php');
        }
        try {
            if (defined('_PS_MODE_DEV_')
                && _PS_MODE_DEV_ == true
            ) {
                $env = 'dev';
                $debug = true;
            } else {
                $env = 'prod';
                $debug = false;
            }
            if (version_compare(_PS_VERSION_, '9.0.0', '>=')) {
                $kernel = new \AdminKernel($env, $debug);
            } else {
                $kernel = new \AppKernel($env, $debug);
            }
            $kernel->boot();

            $container = $kernel->getContainer();

            $application = new Application($kernel);
            $application->setAutoExit(false);

            $input = new ArrayInput([
                'command' => 'everblock:tools:execute',
                'action' => trim(Tools::getValue('action')),
                (int) $this->context->shop->id,
            ]);

            $output = new BufferedOutput();

            // Exécution de la commande
            $application->run($input, $output);
            // Récupérer la sortie de la commande
            $outputText = $output->fetch();
            // Pattern pour matcher les messages <success>, <error>, etc
            $pattern = '/<(success|error|comment|warning)>(.*?)<\/\1>/';

            // Recherche de tous les motifs correspondants dans la sortie
            preg_match_all($pattern, $outputText, $matches, PREG_SET_ORDER);

            // Vérification si des motifs ont été trouvés
            if (!empty($matches)) {
                echo '<div>';
                foreach ($matches as $match) {
                    // $match[1] est le type (success ou error)
                    // $match[2] est le message correspondant
                    $type = $match[1];
                    $message = htmlspecialchars($match[2], ENT_QUOTES); // Pour éviter les injections XSS

                    // Affichage formaté du message
                    echo '<div class=' . $type . '>' . $message . '</div>';
                }
                echo '</div>';
                if ((bool) $debug === true) {
                    echo '<pre>Sortie brute : <br>' . htmlspecialchars($outputText, ENT_QUOTES) . '</pre>';
                }
            } else {
                echo 'Aucun message trouvé.';
            }

        } catch (\Throwable $e) {
            PrestaShopLogger::addLog(
                $e->getMessage()
            );
            echo $e->getMessage();
        }
        die();
    }
}
