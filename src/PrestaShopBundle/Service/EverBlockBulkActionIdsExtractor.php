<?php

namespace Everblock\PrestaShopBundle\Service;

use Everblock\PrestaShopBundle\Grid\Search\Filters\EverBlockFilters;
use Symfony\Component\HttpFoundation\Request;

class EverBlockBulkActionIdsExtractor
{
    /**
     * @var string
     */
    private $gridId;

    public function __construct(string $gridId = EverBlockFilters::FILTER_ID)
    {
        $this->gridId = $gridId;
    }

    public function extractFromRequest(Request $request): array
    {
        return $this->extractFromArray($request->request->all(), $request->query->all());
    }

    public function extractFromArray(array ...$sources): array
    {
        foreach ($sources as $source) {
            if (!is_array($source)) {
                continue;
            }

            $ids = $this->resolveIdsFromSource($source);
            if (!empty($ids)) {
                return $ids;
            }
        }

        return [];
    }

    /**
     * @param array<string, mixed> $source
     *
     * @return array<int, int>
     */
    private function resolveIdsFromSource(array $source): array
    {
        if (isset($source['grid']) && is_array($source['grid'])) {
            $gridData = $source['grid'];
            if (isset($gridData[$this->gridId]['bulk_action']['ids'])) {
                return $this->sanitizeIds($gridData[$this->gridId]['bulk_action']['ids']);
            }
        }

        if (isset($source[$this->gridId]['bulk_action']['ids'])) {
            return $this->sanitizeIds($source[$this->gridId]['bulk_action']['ids']);
        }

        if (isset($source[$this->gridId . '_bulk']['ids'])) {
            return $this->sanitizeIds($source[$this->gridId . '_bulk']['ids']);
        }

        if (isset($source['ids'])) {
            return $this->sanitizeIds($source['ids']);
        }

        return [];
    }

    /**
     * @param mixed $ids
     *
     * @return array<int, int>
     */
    private function sanitizeIds($ids): array
    {
        if (!is_array($ids)) {
            return [];
        }

        $sanitized = [];
        foreach ($ids as $id) {
            if (is_numeric($id)) {
                $sanitized[] = (int) $id;
            }
        }

        return array_values(array_unique($sanitized));
    }
}
