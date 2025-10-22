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
 */

namespace Everblock\PrestaShopBundle\Form\Admin\EverBlock\Command\Handler;

use Configuration;
use EverBlockClass;
use Everblock\PrestaShopBundle\Form\Admin\EverBlock\Command\UpsertEverBlockCommand;
use Hook;
use Module;
use Tools;

class UpsertEverBlockHandler
{
    public function handle(UpsertEverBlockCommand $command): int
    {
        $block = $command->getId() ? new EverBlockClass((int) $command->getId()) : new EverBlockClass();

        $block->id_shop = (int) $command->getShopId();
        $block->name = pSQL($command->getName());
        $block->id_hook = (int) $command->getHookId();
        $block->content = $command->getContent();
        $block->custom_code = $command->getCustomCode();
        $block->active = (int) $command->isActive();
        $block->device = (int) $command->getDevice();
        $block->groups = $command->getGroupsJson();
        $block->only_home = (int) $command->isOnlyHome();
        $block->only_category = (int) $command->isOnlyCategory();
        $block->only_category_product = (int) $command->isOnlyCategoryProduct();
        $block->categories = $command->getCategoriesJson();
        $block->only_manufacturer = (int) $command->isOnlyManufacturer();
        $block->manufacturers = $command->getManufacturersJson();
        $block->only_supplier = (int) $command->isOnlySupplier();
        $block->suppliers = $command->getSuppliersJson();
        $block->only_cms_category = (int) $command->isOnlyCmsCategory();
        $block->cms_categories = $command->getCmsCategoriesJson();
        $block->obfuscate_link = (int) $command->isObfuscateLink();
        $block->add_container = (int) $command->isAddContainer();
        $block->lazyload = (int) $command->isLazyload();
        $block->background = pSQL((string) $command->getBackground());
        $block->css_class = pSQL((string) $command->getCssClass());
        $block->data_attribute = pSQL((string) $command->getDataAttribute());
        $block->bootstrap_class = (int) $command->getBootstrapClass();
        $block->position = (int) $command->getPosition();
        $block->modal = (int) $command->isModal();
        $block->delay = $command->getDelay() ?: 0;
        $block->timeout = $command->getTimeout() ?: 0;
        $block->date_start = $command->getDateStart() ?: '';
        $block->date_end = $command->getDateEnd() ?: '';

        if (!$block->save()) {
            throw new \RuntimeException('Unable to save EverBlock.');
        }

        $module = Module::getInstanceByName('everblock');
        $hookName = Hook::getNameById($command->getHookId());
        if ($module && $hookName) {
            $module->registerHook($hookName);
        }

        if ((bool) Configuration::get('EVERPSCSS_CACHE')) {
            Tools::clearAllCache();
        }

        return (int) $block->id;
    }
}
