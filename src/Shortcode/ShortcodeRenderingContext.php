<?php

namespace Everblock\Tools\Shortcode;

use Cart;
use Context;
use Controller;
use Link;
use Smarty;

final class ShortcodeRenderingContext
{
    private function __construct(private readonly Context $context)
    {
    }

    public static function fromContext(Context $context): self
    {
        return new self($context);
    }

    public function getShopId(): int
    {
        return (int) $this->context->shop->id;
    }

    public function getShopGroupId(): int
    {
        return (int) $this->context->shop->id_shop_group;
    }

    public function getLanguageId(): int
    {
        return (int) $this->context->language->id;
    }

    public function getCustomerId(): int
    {
        return (int) $this->context->customer->id;
    }

    public function getSmarty(): Smarty
    {
        return $this->context->smarty;
    }

    public function getLink(): Link
    {
        return $this->context->link;
    }

    public function getCart(): ?Cart
    {
        return $this->context->cart instanceof Cart ? $this->context->cart : null;
    }

    public function getControllerType(): ?string
    {
        return $this->context->controller->controller_type ?? null;
    }

    public function getTemplateVarCustomer(): mixed
    {
        return $this->context->controller instanceof Controller ? $this->context->controller->getTemplateVarCustomer() : null;
    }

    public function getTemplateVarCurrency(): mixed
    {
        return $this->context->controller instanceof Controller ? $this->context->controller->getTemplateVarCurrency() : null;
    }

    public function getTemplateVarShop(): mixed
    {
        return $this->context->controller instanceof Controller ? $this->context->controller->getTemplateVarShop() : null;
    }

    public function getTemplateVarUrls(): mixed
    {
        return $this->context->controller instanceof Controller ? $this->context->controller->getTemplateVarUrls() : null;
    }

    public function getTemplateVarConfiguration(): mixed
    {
        return $this->context->controller instanceof Controller ? $this->context->controller->getTemplateVarConfiguration() : null;
    }

    public function getBreadcrumb(): mixed
    {
        return $this->context->controller instanceof Controller ? $this->context->controller->getBreadcrumb() : null;
    }

    public function getPrestashopContext(): Context
    {
        return $this->context;
    }
}
