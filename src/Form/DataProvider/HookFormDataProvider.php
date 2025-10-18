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

namespace Everblock\Tools\Form\DataProvider;

use Context;
use Hook;
use RuntimeException;
use Validate;

if (!defined('_PS_VERSION_') && php_sapi_name() !== 'cli') {
    exit;
}

class HookFormDataProvider
{
    /**
     * @var Context
     */
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefaultData(): array
    {
        return [
            'id_hook' => null,
            'name' => '',
            'title' => '',
            'description' => '',
            'active' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(int $hookId): array
    {
        $hook = new Hook($hookId);
        if (!Validate::isLoadedObject($hook)) {
            throw new RuntimeException($this->trans('Unable to find the requested hook.'));
        }

        return [
            'id_hook' => (int) $hook->id,
            'name' => (string) $hook->name,
            'title' => (string) $hook->title,
            'description' => (string) $hook->description,
            'active' => (bool) $hook->active,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormOptions(): array
    {
        return [
            'translation_domain' => 'Modules.Everblock.Admineverblockcontroller',
        ];
    }

    private function trans(string $message): string
    {
        return $this->context->getTranslator()->trans($message, [], 'Modules.Everblock.Admineverblockcontroller');
    }
}
