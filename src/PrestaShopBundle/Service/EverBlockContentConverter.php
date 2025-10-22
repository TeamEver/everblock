<?php

namespace Everblock\PrestaShopBundle\Service;

use Everblock\Tools\Service\EverblockTools;

class EverBlockContentConverter
{
    /**
     * @var callable
     */
    private $webpConverter;

    public function __construct(?callable $webpConverter = null)
    {
        $this->webpConverter = $webpConverter ?: [EverblockTools::class, 'convertImagesToWebP'];
    }

    /**
     * @param array<int, mixed> $localizedContent
     *
     * @return array<int, mixed>
     */
    public function convert(array $localizedContent): array
    {
        foreach ($localizedContent as $idLang => $content) {
            if (!is_string($content)) {
                continue;
            }

            $localizedContent[$idLang] = (string) \call_user_func($this->webpConverter, $content);
        }

        return $localizedContent;
    }
}
