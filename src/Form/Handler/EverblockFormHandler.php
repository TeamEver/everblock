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
use DateTimeImmutable;
use Everblock\Tools\Application\Command\EverBlock\EverBlockTranslationCommand;
use Everblock\Tools\Application\Command\EverBlock\UpsertEverBlockCommand;
use Everblock\Tools\Application\EverBlockApplicationService;
use Group;
use Language;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

if (!defined('_PS_VERSION_') && php_sapi_name() !== 'cli') {
    exit;
}

class EverblockFormHandler
{
    private Context $context;

    public function __construct(
        Context $context,
        private readonly EverBlockApplicationService $applicationService
    ) {
        $this->context = $context;
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(FormInterface $form, Request $request, ?int $everBlockId = null): array
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

        try {
            $command = $this->buildCommand($data, $everBlockId);
            $block = $this->applicationService->save($command);
        } catch (\Throwable $exception) {
            $result['errors'][] = $exception->getMessage();

            return $result;
        }

        $result['success'] = true;
        $result['id'] = (int) $block->getId();

        return $result;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function buildCommand(array $data, ?int $everBlockId): UpsertEverBlockCommand
    {
        $shopId = isset($this->context->shop) ? (int) $this->context->shop->id : 0;

        $blockId = $everBlockId;
        if (null === $blockId && isset($data['id_everblock']) && $data['id_everblock'] !== null && $data['id_everblock'] !== '') {
            $blockId = (int) $data['id_everblock'];
        }

        $groups = (array) ($data['groupBox'] ?? []);
        if (empty($groups)) {
            $groups = array_map(function (array $group) {
                return (int) $group['id_group'];
            }, Group::getGroups(isset($this->context->language) ? (int) $this->context->language->id : 0));
        }

        $positionValue = $data['position'] ?? '';
        $position = $positionValue === '' ? null : (int) $positionValue;

        return new UpsertEverBlockCommand(
            $blockId,
            (string) $data['name'],
            (int) $data['id_hook'],
            $shopId,
            (bool) $data['only_home'],
            (bool) $data['only_category'],
            (bool) $data['only_category_product'],
            (bool) $data['only_manufacturer'],
            (bool) $data['only_supplier'],
            (bool) $data['only_cms_category'],
            (bool) $data['obfuscate_link'],
            (bool) $data['add_container'],
            (bool) $data['lazyload'],
            array_map('intval', $groups),
            array_map('intval', (array) ($data['categories'] ?? [])),
            array_map('intval', (array) ($data['manufacturers'] ?? [])),
            array_map('intval', (array) ($data['suppliers'] ?? [])),
            array_map('intval', (array) ($data['cms_categories'] ?? [])),
            (string) ($data['background'] ?? ''),
            (string) ($data['css_class'] ?? ''),
            (string) ($data['data_attribute'] ?? ''),
            (string) ($data['bootstrap_class'] ?? ''),
            $position,
            (int) $data['device'],
            $this->normalizeInt($data['delay'] ?? null),
            $this->normalizeInt($data['timeout'] ?? null),
            (bool) $data['modal'],
            $this->parseDate($data['date_start'] ?? null),
            $this->parseDate($data['date_end'] ?? null),
            (bool) $data['active'],
            $this->buildTranslationCommands(
                (array) ($data['content'] ?? []),
                (array) ($data['custom_code'] ?? [])
            )
        );
    }

    /**
     * @param array<int, mixed> $content
     * @param array<int, mixed> $customCode
     *
     * @return array<int, EverBlockTranslationCommand>
     */
    private function buildTranslationCommands(array $content, array $customCode): array
    {
        $commands = [];
        foreach (Language::getLanguages(false) as $language) {
            $idLang = (int) $language['id_lang'];
            $commands[$idLang] = new EverBlockTranslationCommand(
                $idLang,
                (string) ($content[$idLang] ?? ''),
                (string) ($customCode[$idLang] ?? '')
            );
        }

        return $commands;
    }

    private function parseDate(mixed $value): ?DateTimeImmutable
    {
        if ($value instanceof \DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }

        if (is_string($value) && $value !== '') {
            try {
                return new DateTimeImmutable($value);
            } catch (\Exception) {
                return null;
            }
        }

        return null;
    }

    private function normalizeInt(mixed $value): ?int
    {
        if ($value === '' || $value === null) {
            return null;
        }

        return (int) $value;
    }

    private function trans(string $message): string
    {
        return $this->context->getTranslator()->trans($message, [], 'Modules.Everblock.Admin');
    }
}
