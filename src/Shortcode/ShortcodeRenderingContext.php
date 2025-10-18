<?php

namespace Everblock\Tools\Shortcode;

use Cart;
use Context;
use Controller;
use Customer;
use Link;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;
use Validate;

final class ShortcodeRenderingContext
{
    public function __construct(
        private readonly Context $context,
        private readonly RequestStack $requestStack,
        private readonly ?Security $security = null
    ) {
    }

    public function getShopId(): int
    {
        $session = $this->getSession();

        if ($session instanceof SessionInterface && $session->has('id_shop')) {
            return (int) $session->get('id_shop');
        }

        $request = $this->getCurrentRequest();

        if ($request instanceof Request) {
            foreach (['id_shop', 'shopId'] as $attribute) {
                if ($request->attributes->has($attribute)) {
                    return (int) $request->attributes->get($attribute);
                }
            }
        }

        return (int) $this->context->shop->id;
    }

    public function getShopGroupId(): int
    {
        $session = $this->getSession();

        if ($session instanceof SessionInterface && $session->has('id_shop_group')) {
            return (int) $session->get('id_shop_group');
        }

        return (int) $this->context->shop->id_shop_group;
    }

    public function getLanguageId(): int
    {
        $session = $this->getSession();

        if ($session instanceof SessionInterface && $session->has('id_lang')) {
            return (int) $session->get('id_lang');
        }

        $request = $this->getCurrentRequest();

        if ($request instanceof Request && $request->attributes->has('id_lang')) {
            return (int) $request->attributes->get('id_lang');
        }

        return (int) $this->context->language->id;
    }

    public function getCustomerId(): int
    {
        $user = null;

        if ($this->security instanceof Security) {
            try {
                $user = $this->security->getUser();
            } catch (\Throwable) {
                $user = null;
            }
        }

        if ($user instanceof Customer) {
            return (int) $user->id;
        }

        $session = $this->getSession();

        if ($session instanceof SessionInterface && $session->has('id_customer')) {
            return (int) $session->get('id_customer');
        }

        return (int) $this->context->customer->id;
    }

    public function getLink(): Link
    {
        return $this->context->link;
    }

    public function getCart(): ?Cart
    {
        $session = $this->getSession();

        if ($session instanceof SessionInterface && $session->has('id_cart')) {
            $cartId = (int) $session->get('id_cart');
            if ($cartId > 0) {
                $cart = new Cart($cartId);

                if (Validate::isLoadedObject($cart)) {
                    return $cart;
                }
            }
        }

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

    private function getCurrentRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    private function getSession(): ?SessionInterface
    {
        $request = $this->getCurrentRequest();

        if (!$request instanceof Request || !$request->hasSession()) {
            return null;
        }

        $session = $request->getSession();

        return $session instanceof SessionInterface ? $session : null;
    }
}
