<?php

namespace Everblock\Tools\Controller\Admin;

use DateTimeImmutable;
use Everblock\Tools\Entity\Everblock;
use Everblock\Tools\Service\EverblockManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/everblocks', name: 'everblock_admin_')]
class EverblockController extends AbstractController
{
    public function __construct(private EverblockManager $manager)
    {
    }

    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $shopId = (int) $request->query->get('shop', 1);
        $languageId = (int) $request->query->get('lang', 1);

        $blocks = $this->manager->listBlocks($shopId, $languageId);

        return $this->json($blocks);
    }

    #[Route(path: '/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id, Request $request): JsonResponse
    {
        $shopId = (int) $request->query->get('shop', 1);
        $languageId = (int) $request->query->get('lang', 1);

        $block = $this->manager->getBlock($id, $languageId, $shopId);

        if (!$block instanceof Everblock) {
            return $this->json(['message' => 'Everblock not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($block->toArray($languageId));
    }

    #[Route(path: '', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = $this->decodeRequest($request);
        $languageId = (int) ($payload['language_id'] ?? $payload['id_lang'] ?? 1);
        $block = new Everblock();
        $this->applyPayload($block, $payload);

        $saved = $this->manager->save($block);

        return $this->json($saved->toArray($languageId), Response::HTTP_CREATED);
    }

    #[Route(path: '/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $payload = $this->decodeRequest($request);
        $shopId = (int) ($payload['shop_id'] ?? $payload['id_shop'] ?? 1);
        $languageId = (int) ($payload['language_id'] ?? $payload['id_lang'] ?? 1);

        $block = $this->manager->getBlock($id, $languageId, $shopId);

        if (!$block instanceof Everblock) {
            return $this->json(['message' => 'Everblock not found'], Response::HTTP_NOT_FOUND);
        }

        $this->applyPayload($block, $payload);
        $saved = $this->manager->save($block);

        return $this->json($saved->toArray($languageId));
    }

    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, Request $request): JsonResponse
    {
        $shopId = (int) $request->query->get('shop', 1);
        $languageId = (int) $request->query->get('lang', 1);
        $block = $this->manager->getBlock($id, $languageId, $shopId);

        if (!$block instanceof Everblock) {
            return $this->json(['message' => 'Everblock not found'], Response::HTTP_NOT_FOUND);
        }

        $this->manager->delete($block);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    private function decodeRequest(Request $request): array
    {
        $content = $request->getContent();
        if ($content === '') {
            return [];
        }

        $data = json_decode($content, true);

        if (!is_array($data)) {
            throw new \InvalidArgumentException('Invalid JSON payload provided.');
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function applyPayload(Everblock $block, array $payload): void
    {
        $map = [
            'name' => 'setName',
            'id_hook' => 'setHookId',
            'hook_id' => 'setHookId',
            'only_home' => 'setOnlyHome',
            'only_category' => 'setOnlyCategory',
            'only_category_product' => 'setOnlyCategoryProduct',
            'only_manufacturer' => 'setOnlyManufacturer',
            'only_supplier' => 'setOnlySupplier',
            'only_cms_category' => 'setOnlyCmsCategory',
            'obfuscate_link' => 'setObfuscateLink',
            'add_container' => 'setAddContainer',
            'lazyload' => 'setLazyload',
            'device' => 'setDevice',
            'groups' => 'setGroups',
            'background' => 'setBackground',
            'css_class' => 'setCssClass',
            'data_attribute' => 'setDataAttribute',
            'bootstrap_class' => 'setBootstrapClass',
            'position' => 'setPosition',
            'id_shop' => 'setShopId',
            'shop_id' => 'setShopId',
            'categories' => 'setCategories',
            'manufacturers' => 'setManufacturers',
            'suppliers' => 'setSuppliers',
            'cms_categories' => 'setCmsCategories',
            'modal' => 'setModal',
            'delay' => 'setDelay',
            'timeout' => 'setTimeout',
            'active' => 'setActive',
        ];

        $booleanFields = [
            'only_home',
            'only_category',
            'only_category_product',
            'only_manufacturer',
            'only_supplier',
            'only_cms_category',
            'obfuscate_link',
            'add_container',
            'lazyload',
            'modal',
            'active',
        ];

        $integerFields = [
            'id_hook',
            'hook_id',
            'device',
            'position',
            'id_shop',
            'shop_id',
            'delay',
            'timeout',
        ];

        $arrayFields = [
            'categories',
            'manufacturers',
            'suppliers',
            'cms_categories',
            'groups',
        ];

        foreach ($map as $key => $method) {
            if (!array_key_exists($key, $payload) || !method_exists($block, $method)) {
                continue;
            }

            $value = $payload[$key];

            if (in_array($key, $booleanFields, true)) {
                $value = (bool) $value;
            }

            if (in_array($key, $integerFields, true)) {
                $value = (int) $value;
            }

            if (in_array($key, $arrayFields, true)) {
                $value = $this->normalizeArrayValue($value);
            }

            $block->{$method}($value);
        }

        if (isset($payload['date_start'])) {
            $block->setDateStart($this->parseDate($payload['date_start']));
        }

        if (isset($payload['date_end'])) {
            $block->setDateEnd($this->parseDate($payload['date_end']));
        }

        $translations = $payload['translations'] ?? [];
        if (is_array($translations)) {
            foreach ($translations as $translation) {
                if (!is_array($translation)) {
                    continue;
                }

                $languageId = (int) ($translation['languageId'] ?? $translation['id_lang'] ?? 0);
                if ($languageId <= 0) {
                    continue;
                }

                if (array_key_exists('content', $translation)) {
                    $block->setContent($languageId, $translation['content']);
                }

                if (array_key_exists('customCode', $translation)) {
                    $block->setCustomCode($languageId, $translation['customCode']);
                } elseif (array_key_exists('custom_code', $translation)) {
                    $block->setCustomCode($languageId, $translation['custom_code']);
                }
            }
        }
    }

    private function normalizeArrayValue(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            return array_values(array_filter(array_map('trim', explode(',', $value)), static fn (string $item): bool => $item !== ''));
        }

        return [];
    }

    private function parseDate(mixed $value): ?DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof DateTimeImmutable) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) $value);
        if ($date instanceof DateTimeImmutable) {
            return $date;
        }

        $shortDate = DateTimeImmutable::createFromFormat('Y-m-d', (string) $value);

        return $shortDate ?: null;
    }
}
