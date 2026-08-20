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
    public function initContent()
    {
        if (!$this->module instanceof Everblock) {
            Tools::redirect('index.php');
            return;
        }
        $module = $this->module;
        if (!Module::isInstalled($module->name)) {
            Tools::redirect('index.php');
        }
        // Dedicated random secret, compared in constant time. The former token was
        // encrypt('everblock/evercron'), derivable from the cookie key and impossible to rotate.
        if (!$module->isValidAdminConfigurationCronToken(Tools::getValue('evertoken'))) {
            Tools::redirect('index.php');
        }
        $action = trim((string) Tools::getValue('action'));
        if ($action === '') {
            Tools::redirect('index.php');
        }
        // Single source of truth, shared with the back office link generator. Recovery and
        // one-shot migration actions are deliberately no longer exposed over HTTP.
        if (!in_array($action, $module->getAdminConfigurationAllowedActions(), true)) {
            Tools::redirect('index.php');
        }
        try {
            $debug = defined('_PS_MODE_DEV_') ? (bool) constant('_PS_MODE_DEV_') : false;
            $env = $debug ? 'dev' : 'prod';
            $kernelClass = version_compare(_PS_VERSION_, '9.0.0', '>=') && class_exists('AdminKernel')
                ? 'AdminKernel'
                : 'AppKernel';
            /** @var AppKernel $kernel */
            $kernel = new $kernelClass($env, $debug);
            $kernel->boot();

            $container = $kernel->getContainer();

            $application = new Application($kernel);
            $application->setAutoExit(false);

            $input = new ArrayInput([
                'command' => 'everblock:tools:execute',
                'action' => $action,
                'idshop id' => (int) $this->context->shop->id,
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
                'Everblock cron "' . $action . '": ' . $e->getMessage(),
                3
            );
            // Do not echo the exception message: it can disclose absolute paths and internals.
            echo 'Everblock cron failed, see the PrestaShop logs for details.';
        }
        die();
    }
}
