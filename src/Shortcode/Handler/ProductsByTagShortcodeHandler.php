<?php

namespace Everblock\Tools\Shortcode\Handler;

use Everblock;
use EverblockTools;
use Everblock\Tools\Dto\Product\ProductTagFilters;
use Everblock\Tools\Infrastructure\Repository\ProductRepository;
use Everblock\Tools\Infrastructure\Repository\ProductTagRepository;
use Everblock\Tools\Shortcode\ShortcodeHandlerInterface;
use Everblock\Tools\Shortcode\ShortcodeRenderingContext;

final class ProductsByTagShortcodeHandler implements ShortcodeHandlerInterface
{
    public function __construct(
        private readonly ProductTagRepository $productTagRepository,
        private readonly ProductRepository $productRepository
    ) {
    }

    public function supports(string $content): bool
    {
        return str_contains($content, '[products_by_tag');
    }

    public function render(string $content, ShortcodeRenderingContext $context, Everblock $module): string
    {
        return (string) preg_replace_callback(
            '/\[products_by_tag\s+([^\]]+)\]/i',
            function (array $matches) use ($context, $module) {
                $attrs = $this->parseAttributes($matches[1] ?? '');

                $tagNames = $this->normalizeStringList($attrs['tag'] ?? []);
                $tagIds = $this->normalizeIntList($attrs['tag_id'] ?? []);

                if ($tagNames === [] && $tagIds === []) {
                    return '';
                }

                $match = strtolower((string) ($attrs['match'] ?? ProductTagFilters::MATCH_ANY));
                if (!in_array($match, [ProductTagFilters::MATCH_ANY, ProductTagFilters::MATCH_ALL], true)) {
                    $match = ProductTagFilters::MATCH_ANY;
                }

                $limit = isset($attrs['limit']) ? max(1, (int) $attrs['limit']) : 12;
                $offset = isset($attrs['offset']) ? max(0, (int) $attrs['offset']) : 0;
                $order = strtolower((string) ($attrs['order'] ?? 'position'));
                $orderDirection = strtolower((string) ($attrs['way'] ?? 'asc'));
                $cols = isset($attrs['cols']) ? (int) $attrs['cols'] : null;

                $allowedOrders = ['position', 'name', 'price', 'date_add', 'rand'];
                if (!in_array($order, $allowedOrders, true)) {
                    $order = 'position';
                }

                $allowedDirections = ['asc', 'desc'];
                if (!in_array($orderDirection, $allowedDirections, true)) {
                    $orderDirection = 'asc';
                }

                $visibilities = $this->resolveVisibilities($attrs['visibility'] ?? 'both|catalog');

                $cacheKey = md5(json_encode([
                    $tagNames,
                    $tagIds,
                    $match,
                    $limit,
                    $offset,
                    $order,
                    $orderDirection,
                    $visibilities,
                    $context->getShopId(),
                    $context->getLanguageId(),
                ]));

                static $cache = [];

                if (!isset($cache[$cacheKey])) {
                    $filters = new ProductTagFilters(
                        $context->getShopId(),
                        $context->getLanguageId(),
                        $tagNames,
                        $tagIds,
                        $match,
                        $offset,
                        $limit,
                        $order,
                        $orderDirection,
                        $visibilities,
                    );

                    $productIds = $this->productTagRepository->findProductIds($filters);

                    if (count($productIds) === 0) {
                        $cache[$cacheKey] = [];
                    } else {
                        $presentations = $this->productRepository->presentProducts($productIds, $context);
                        $cache[$cacheKey] = $presentations->toArray();
                    }
                }

                $products = $cache[$cacheKey];

                if ($products === []) {
                    return '';
                }

                $context->getSmarty()->assign([
                    'products' => $products,
                    'cols' => $cols,
                    'total' => count($products),
                    'params' => $attrs,
                ]);

                $templatePath = EverblockTools::getTemplatePath('hook/products_by_tag.tpl', $module);

                return $context->getSmarty()->fetch($templatePath);
            },
            $content
        );
    }

    /**
     * @return array<string, string|array<int, string>>
     */
    private function parseAttributes(string $attrStr): array
    {
        $attrs = [];
        preg_match_all('/(\w+)\s*=\s*"([^"]*)"/', $attrStr, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $key = strtolower($match[1]);
            $value = trim($match[2]);
            if (strpos($value, '|') !== false) {
                $value = array_filter(array_map('trim', explode('|', $value)));
            }
            $attrs[$key] = $value;
        }

        return $attrs;
    }

    /**
     * @param mixed $value
     * @return string[]
     */
    private function normalizeStringList(mixed $value): array
    {
        if (is_string($value)) {
            $value = [$value];
        }

        if (!is_array($value)) {
            return [];
        }

        $values = [];
        foreach ($value as $item) {
            if (!is_string($item)) {
                continue;
            }
            $item = trim($item);
            if ($item === '') {
                continue;
            }
            $values[] = $item;
        }

        return $values;
    }

    /**
     * @param mixed $value
     * @return int[]
     */
    private function normalizeIntList(mixed $value): array
    {
        if (is_string($value)) {
            $value = [$value];
        }

        if (!is_array($value)) {
            return [];
        }

        $values = [];
        foreach ($value as $item) {
            if ($item === '' || $item === null) {
                continue;
            }
            $values[] = (int) $item;
        }

        return array_values(array_filter($values, static fn (int $id): bool => $id > 0));
    }

    /**
     * @param mixed $value
     * @return string[]
     */
    private function resolveVisibilities(mixed $value): array
    {
        $raw = $value;
        if (is_string($raw)) {
            $raw = array_map('trim', preg_split('/[|,]/', $raw) ?: []);
        }

        if (!is_array($raw)) {
            return ['both', 'catalog'];
        }

        $allowed = ['both', 'catalog', 'search', 'none'];
        $visibilities = array_values(array_intersect($raw, $allowed));

        if ($visibilities === []) {
            return ['both', 'catalog'];
        }

        return $visibilities;
    }
}
