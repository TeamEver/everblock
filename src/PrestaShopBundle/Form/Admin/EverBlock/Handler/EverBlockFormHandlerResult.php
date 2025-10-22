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

namespace Everblock\PrestaShopBundle\Form\Admin\EverBlock\Handler;

class EverBlockFormHandlerResult
{
    private $successful;
    private $blockId;
    private $errors;

    public function __construct(bool $successful, $blockId = null, array $errors = [])
    {
        $this->successful = $successful;
        $this->blockId = $blockId;
        $this->errors = $errors;
    }

    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    public function getBlockId()
    {
        return $this->blockId;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
