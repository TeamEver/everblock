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

namespace Everblock\Tools\Form\Handler;

use Context;
use Hook;
use Module;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Validate;

if (!defined('_PS_VERSION_') && php_sapi_name() !== 'cli') {
    exit;
}

class HookFormHandler
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
     * @param Hook|null $hook
     *
     * @return array<string, mixed>
     */
    public function handle(FormInterface $form, Request $request, ?Hook $hook = null): array
    {
        $form->handleRequest($request);

        $result = [
            'submitted' => $form->isSubmitted(),
            'success' => false,
            'errors' => [],
            'id' => null,
            'stay' => (bool) $request->request->get('stay'),
        ];

        if (!$form->isSubmitted()) {
            return $result;
        }

        if (!$form->isValid()) {
            foreach ($form->getErrors(true) as $error) {
                $result['errors'][] = $error->getMessage();
            }

            return $result;
        }

        $data = $form->getData();

        if (!$hook || !Validate::isLoadedObject($hook)) {
            $hook = new Hook();
        }

        $hook->name = (string) $data['name'];
        $hook->title = (string) $data['title'];
        $hook->description = (string) $data['description'];
        $hook->active = (bool) $data['active'];
        $hook->position = 1;

        try {
            if (!$hook->save()) {
                $result['errors'][] = $this->trans('An error occurred while saving the hook.');

                return $result;
            }
        } catch (\Exception $exception) {
            $result['errors'][] = $exception->getMessage();

            return $result;
        }

        if (Module::isInstalled('prettyblocks')) {
            $module = Module::getInstanceByName('prettyblocks');
            if ($module) {
                $module->registerHook($hook->name);
            }
        }

        $result['success'] = true;
        $result['id'] = (int) $hook->id;

        return $result;
    }

    private function trans(string $message): string
    {
        return $this->context->getTranslator()->trans($message, [], 'Modules.Everblock.Admineverblockcontroller');
    }
}
