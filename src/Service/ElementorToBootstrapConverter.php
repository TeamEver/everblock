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

namespace Everblock\Tools\Service;

use DOMDocument;
use DOMElement;
use DOMXPath;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ElementorToBootstrapConverter
{
    /**
     * Mapping between Elementor structural classes and Bootstrap equivalents.
     *
     * @var array<string, string>
     */
    protected array $structureMapping = [
        'elementor-section' => 'container',
        'elementor-container' => 'row',
        'elementor-column' => 'col',
        'elementor-col-100' => 'col-12',
        'elementor-col-50' => 'col-md-6',
        'elementor-col-33' => 'col-md-4',
        'elementor-col-25' => 'col-md-3',
    ];

    /**
     * Mapping between Elementor widget identifiers and handler methods.
     *
     * @var array<string, string>
     */
    protected array $widgetHandlers = [
        'elementor-widget-heading' => 'handleHeadingWidget',
        'elementor-widget-text-editor' => 'handleTextWidget',
        'elementor-widget-image' => 'handleImageWidget',
        'elementor-widget-button' => 'handleButtonWidget',
    ];

    public function convert(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $internalErrors = libxml_use_internal_errors(true);
        $document->loadHTML(
            mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'),
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);

        $xpath = new DOMXPath($document);

        $this->convertStructure($xpath);
        $this->convertWidgets($xpath);
        $this->normalizeImages($xpath);
        $this->cleanAttributes($xpath);
        $this->removeElementorArtifacts($document, $xpath);

        return trim($document->saveHTML());
    }

    protected function convertStructure(DOMXPath $xpath): void
    {
        foreach ($this->structureMapping as $elementorClass => $bootstrapClass) {
            foreach ($this->getElementsByClass($xpath, $elementorClass) as $element) {
                $this->addClass($element, $bootstrapClass);
            }
        }
    }

    protected function convertWidgets(DOMXPath $xpath): void
    {
        foreach ($this->widgetHandlers as $elementorClass => $handler) {
            if (!method_exists($this, $handler)) {
                continue;
            }

            foreach ($this->getElementsByClass($xpath, $elementorClass) as $element) {
                $this->{$handler}($element, $xpath);
            }
        }
    }

    protected function cleanAttributes(DOMXPath $xpath): void
    {
        foreach ($xpath->query('//*') as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            $this->stripElementorClasses($node);
            $this->stripDataAttributes($node);
            $this->sanitizeInlineStyles($node);
        }
    }

    protected function normalizeImages(DOMXPath $xpath): void
    {
        foreach ($xpath->query('//img') as $img) {
            if (!$img instanceof DOMElement) {
                continue;
            }

            $this->addClass($img, 'img-fluid');
            $this->preserveDimensionAttributes($img);
        }
    }

    protected function removeElementorArtifacts(DOMDocument $document, DOMXPath $xpath): void
    {
        foreach ($xpath->query('//style') as $styleNode) {
            if (!$styleNode instanceof DOMElement) {
                continue;
            }

            $content = strtolower($styleNode->textContent ?? '');
            if (str_contains($content, 'elementor')) {
                $styleNode->parentNode?->removeChild($styleNode);
            }
        }

        $document->normalizeDocument();
    }

    protected function handleHeadingWidget(DOMElement $widget, DOMXPath $xpath): void
    {
        // Heading widgets primarily contain heading tags; no structural change required.
        // Ensures heading tags keep their semantic level while cleaning classes later.
        foreach ($xpath->query('.//*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6]', $widget) as $heading) {
            if ($heading instanceof DOMElement) {
                $this->stripElementorClasses($heading);
                $this->sanitizeInlineStyles($heading);
            }
        }
    }

    protected function handleTextWidget(DOMElement $widget, DOMXPath $xpath): void
    {
        // Text widgets often wrap content inside a container; ensure paragraphs remain intact.
        foreach ($xpath->query('.//*[self::p or self::div]', $widget) as $textNode) {
            if ($textNode instanceof DOMElement) {
                $this->stripElementorClasses($textNode);
            }
        }
    }

    protected function handleImageWidget(DOMElement $widget, DOMXPath $xpath): void
    {
        foreach ($xpath->query('.//img', $widget) as $img) {
            if (!$img instanceof DOMElement) {
                continue;
            }

            $this->addClass($img, 'img-fluid');
            $this->preserveDimensionAttributes($img);
        }
    }

    protected function handleButtonWidget(DOMElement $widget, DOMXPath $xpath): void
    {
        foreach ($xpath->query('.//a', $widget) as $link) {
            if (!$link instanceof DOMElement) {
                continue;
            }

            $this->addClass($link, 'btn');
            $this->addClass($link, 'btn-primary');
            $this->sanitizeInlineStyles($link);
        }
    }

    /**
     * @return iterable<DOMElement>
     */
    protected function getElementsByClass(DOMXPath $xpath, string $className): iterable
    {
        $query = sprintf('//*[contains(concat(" ", normalize-space(@class), " "), " %s ")]', $className);

        return $xpath->query($query) ?: [];
    }

    protected function addClass(DOMElement $element, string $className): void
    {
        $classes = $this->getClassList($element);

        if (!in_array($className, $classes, true)) {
            $classes[] = $className;
            $element->setAttribute('class', implode(' ', $classes));
        }
    }

    /**
     * @return array<int, string>
     */
    protected function getClassList(DOMElement $element): array
    {
        $classAttr = $element->getAttribute('class');

        if ($classAttr === '') {
            return [];
        }

        $classes = preg_split('/\s+/', $classAttr) ?: [];

        return array_values(array_filter($classes));
    }

    protected function stripElementorClasses(DOMElement $element): void
    {
        $classes = array_filter(
            $this->getClassList($element),
            static fn (string $class) => !str_starts_with($class, 'elementor-')
        );

        if (empty($classes)) {
            $element->removeAttribute('class');

            return;
        }

        $element->setAttribute('class', implode(' ', $classes));
    }

    protected function stripDataAttributes(DOMElement $element): void
    {
        for ($i = $element->attributes->length - 1; $i >= 0; --$i) {
            $attribute = $element->attributes->item($i);
            if ($attribute === null) {
                continue;
            }

            if (str_starts_with($attribute->nodeName, 'data-')) {
                $element->removeAttributeNode($attribute);
            }
        }
    }

    protected function sanitizeInlineStyles(DOMElement $element): void
    {
        if (!$element->hasAttribute('style')) {
            return;
        }

        if (strtolower($element->tagName) === 'img') {
            $style = $element->getAttribute('style');
            $filtered = $this->filterStyleDeclarations($style, ['width', 'height']);

            if ($filtered === '') {
                $element->removeAttribute('style');
            } else {
                $element->setAttribute('style', $filtered);
            }

            return;
        }

        $element->removeAttribute('style');
    }

    protected function preserveDimensionAttributes(DOMElement $img): void
    {
        if (!$img->hasAttribute('width') && $img->hasAttribute('style')) {
            $width = $this->extractStyleValue($img->getAttribute('style'), 'width');
            if ($width !== null) {
                $img->setAttribute('width', $width);
            }
        }

        if (!$img->hasAttribute('height') && $img->hasAttribute('style')) {
            $height = $this->extractStyleValue($img->getAttribute('style'), 'height');
            if ($height !== null) {
                $img->setAttribute('height', $height);
            }
        }
    }

    protected function filterStyleDeclarations(string $style, array $allowedProperties): string
    {
        $declarations = array_filter(array_map('trim', explode(';', $style)));
        $allowed = [];

        foreach ($declarations as $declaration) {
            [$property, $value] = array_map('trim', explode(':', $declaration, 2) + ['', '']);

            if ($property === '' || $value === '') {
                continue;
            }

            if (in_array(strtolower($property), $allowedProperties, true)) {
                $allowed[] = sprintf('%s: %s', $property, $value);
            }
        }

        return implode('; ', $allowed);
    }

    protected function extractStyleValue(string $style, string $property): ?string
    {
        $pattern = sprintf('/%s\s*:\s*([^;]+)\s*/i', preg_quote($property, '/'));
        if (preg_match($pattern, $style, $matches) !== 1) {
            return null;
        }

        return trim($matches[1]);
    }
}
