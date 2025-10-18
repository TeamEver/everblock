<?php

namespace Everblock\Tools\Shortcode\Handler;

use Combination;
use Db;
use Everblock;
use EverblockTools;
use Everblock\Tools\Dto\Product\LowStockFilters;
use Everblock\Tools\Dto\Product\ProductIdCollection;
use Everblock\Tools\Infrastructure\Repository\ProductRepository;
use Everblock\Tools\Infrastructure\Repository\StockRepository;
use Everblock\Tools\Shortcode\ShortcodeHandlerInterface;
use Everblock\Tools\Shortcode\ShortcodeRenderingContext;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Product\ProductPresenterFactory;
use ProductAssembler;
use Validate;

final class LowStockShortcodeHandler implements ShortcodeHandlerInterface
{
    public function __construct(
        private readonly StockRepository $stockRepository,
        private readonly ProductRepository $productRepository
    ) {
    }

    public function supports(string $content): bool
    {
        return str_contains($content, '[low_stock');
    }

    public function render(string $content, ShortcodeRenderingContext $context, Everblock $module): string
    {
        return (string) preg_replace_callback(
            '/\[low_stock(?:\s+([^\]]+))?\]/i',
            function (array $matches) use ($context, $module) {
                $attrs = $this->parseAttributes($matches[1] ?? '');

                $limit = isset($attrs['limit']) ? max(1, (int) $attrs['limit']) : 10;
                $offset = isset($attrs['offset']) ? max(0, (int) $attrs['offset']) : 0;
                $threshold = isset($attrs['threshold']) ? (int) $attrs['threshold'] : 5;

                $match = strtolower((string) ($attrs['match'] ?? 'lte'));
                $operatorMap = [
                    'lt' => '<',
                    'lte' => '<=',
                    'eq' => '=',
                    'gt' => '>',
                    'gte' => '>=',
                ];
                $comparisonOperator = $operatorMap[$match] ?? '<=';

                $order = strtolower((string) ($attrs['order'] ?? 'qty'));
                $allowedOrders = ['qty', 'date_add', 'name', 'price', 'sales', 'rand'];
                if (!in_array($order, $allowedOrders, true)) {
                    $order = 'qty';
                }

                $direction = strtolower((string) ($attrs['way'] ?? 'asc'));
                $allowedDirections = ['asc', 'desc'];
                if (!in_array($direction, $allowedDirections, true)) {
                    $direction = 'asc';
                }
                if ($order === 'rand') {
                    $direction = 'asc';
                }

                $days = isset($attrs['days']) ? max(0, (int) $attrs['days']) : 0;
                $categoryIds = $this->normalizeIntList($attrs['id_category'] ?? []);
                $manufacturerIds = $this->normalizeIntList($attrs['id_manufacturer'] ?? []);
                $visibilities = $this->resolveVisibilities($attrs['visibility'] ?? 'both,catalog');
                $availableOnly = isset($attrs['available_only']) ? (bool) (int) $attrs['available_only'] : true;
                $granularity = strtolower((string) ($attrs['by'] ?? LowStockFilters::GRANULARITY_PRODUCT));
                if (!in_array($granularity, [LowStockFilters::GRANULARITY_PRODUCT, LowStockFilters::GRANULARITY_COMBINATION], true)) {
                    $granularity = LowStockFilters::GRANULARITY_PRODUCT;
                }

                $cols = isset($attrs['cols']) ? (int) $attrs['cols'] : 4;

                $cacheKey = md5(json_encode([
                    $attrs,
                    $context->getShopId(),
                    $context->getShopGroupId(),
                    $context->getLanguageId(),
                ]));

                static $cache = [];

                if (!isset($cache[$cacheKey])) {
                    $filters = new LowStockFilters(
                        $context->getShopId(),
                        $context->getShopGroupId(),
                        $context->getLanguageId(),
                        $threshold,
                        $comparisonOperator,
                        $limit,
                        $offset,
                        $order,
                        $direction,
                        $days,
                        $categoryIds,
                        $manufacturerIds,
                        $visibilities,
                        $availableOnly,
                        $granularity,
                    );

                    $lowStockProducts = $this->stockRepository->findLowStockProducts($filters);

                    if (count($lowStockProducts) === 0) {
                        $cache[$cacheKey] = ['products' => [], 'variants' => []];
                    } elseif ($granularity === LowStockFilters::GRANULARITY_PRODUCT) {
                        $productIds = new ProductIdCollection(array_map(
                            static fn ($product) => $product->getProductId(),
                            $lowStockProducts->toArray()
                        ));

                        $presentations = $this->productRepository->presentProducts($productIds, $context);
                        $cache[$cacheKey] = ['products' => $presentations->toArray(), 'variants' => []];
                    } else {
                        $cache[$cacheKey] = $this->buildCombinationData($lowStockProducts->toArray(), $context);
                    }
                }

                $data = $cache[$cacheKey];

                if ($data['products'] === []) {
                    return '';
                }

                $context->getSmarty()->assign([
                    'products' => $data['products'],
                    'variants' => $data['variants'],
                    'cols' => $cols,
                    'params' => $attrs,
                ]);

                $templatePath = EverblockTools::getTemplatePath('hook/low_stock.tpl', $module);

                return $context->getSmarty()->fetch($templatePath);
            },
            $content
        );
    }

    /**
     * @param string $attrStr
     * @return array<string, string|array<int, string>>
     */
    private function parseAttributes(string $attrStr): array
    {
        $attrs = [];
        preg_match_all('/(\w+)\s*=\s*"([^"]*)"/', $attrStr, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $key = strtolower($match[1]);
            $value = trim($match[2]);
            if (strpos($value, '|') !== false || strpos($value, ',') !== false) {
                $value = array_filter(array_map('trim', preg_split('/[|,]/', $value) ?: []));
            }
            $attrs[$key] = $value;
        }

        return $attrs;
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

    /**
     * @param array<int, \Everblock\Tools\Dto\Product\LowStockProduct> $products
     * @return array{products: array<int, array<string, mixed>>, variants: array<int, array<string, mixed>>}
     */
    private function buildCombinationData(array $products, ShortcodeRenderingContext $context): array
    {
        $psContext = $context->getPrestashopContext();
        $assembler = new ProductAssembler($psContext);
        $presenterFactory = new ProductPresenterFactory($psContext);
        $presentationSettings = $presenterFactory->getPresentationSettings();

        if (class_exists(\PrestaShop\PrestaShop\Core\Product\ProductListingPresenter::class)) {
            $presenter = new \PrestaShop\PrestaShop\Core\Product\ProductListingPresenter(
                new ImageRetriever($psContext->link),
                $psContext->link,
                new PriceFormatter(),
                new ProductColorsRetriever(),
                $psContext->getTranslator()
            );
        } else {
            $presenter = new \PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductPresenter(
                new ImageRetriever($psContext->link),
                $psContext->link,
                new PriceFormatter(),
                new ProductColorsRetriever(),
                $psContext->getTranslator()
            );
        }

        $presentationSettings->showPrices = true;

        $presented = [];
        $variants = [];

        foreach ($products as $product) {
            $raw = [
                'id_product' => $product->getProductId(),
                'id_product_attribute' => $product->getProductAttributeId(),
                'id_lang' => $context->getLanguageId(),
                'id_shop' => $context->getShopId(),
            ];

            $assembled = $assembler->assembleProduct($raw);
            $presentedProduct = $presenter->present($presentationSettings, $assembled, $psContext->language);
            $presented[] = $presentedProduct;

            $combination = new Combination((int) $product->getProductAttributeId());
            $attributeNames = [];
            if (Validate::isLoadedObject($combination)) {
                $attributes = $combination->getAttributesName($context->getLanguageId());
                foreach ($attributes as $attribute) {
                    $attributeNames[] = $attribute['name'];
                }
            }

            $imageId = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                'SELECT id_image FROM ' . _DB_PREFIX_ . 'product_attribute_image WHERE id_product_attribute = '
                . (int) $product->getProductAttributeId() . ' ORDER BY id_image ASC'
            );

            $imageUrl = '';
            if ($imageId && !empty($presentedProduct['link_rewrite'])) {
                $imageUrl = $context->getLink()->getImageLink($presentedProduct['link_rewrite'], $imageId);
            }

            $variants[] = [
                'id_product' => $product->getProductId(),
                'id_product_attribute' => $product->getProductAttributeId(),
                'attributes' => $attributeNames,
                'url' => $context->getLink()->getProductLink(
                    $product->getProductId(),
                    null,
                    null,
                    null,
                    $context->getLanguageId(),
                    $context->getShopId(),
                    $product->getProductAttributeId() ?? 0
                ),
                'image' => $imageUrl,
            ];
        }

        return ['products' => $presented, 'variants' => $variants];
    }
}
