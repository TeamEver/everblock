<?php

namespace Everblock\Tools\Application\Cart;

use Address;
use Cart;
use CartRule;
use Context;
use Exception;
use Link;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CartManager
{
    public function __construct(
        private readonly Context $context,
        private readonly TranslatorInterface $translator,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    public function addProduct(int $productId, int $productAttributeId = 0, int $quantity = 1): CartOperationResult
    {
        if ($productId <= 0) {
            $message = $this->translator->trans('Unable to add the product to the cart.', [], 'Modules.Everblock.Shop');

            return new CartOperationResult(false, $message, errors: [$message]);
        }

        $quantity = max(1, $quantity);

        try {
            $cart = $this->ensureCart();
        } catch (Exception $exception) {
            $this->logException($exception);
            $message = $this->translator->trans('Unable to create a cart for this session.', [], 'Modules.Everblock.Shop');

            $this->pushControllerError($message);

            return new CartOperationResult(false, $message, errors: [$message]);
        }

        try {
            $updated = $cart->updateQty($quantity, $productId, $productAttributeId);
        } catch (Exception $exception) {
            $this->logException($exception);
            $updated = false;
        }

        if (!$updated) {
            $message = $this->translator->trans('Unable to add the product to the cart.', [], 'Modules.Everblock.Shop');
            $this->pushControllerError($message);

            return new CartOperationResult(false, $message, errors: [$message]);
        }

        CartRule::autoRemoveFromCart($this->context);
        CartRule::autoAddToCart($this->context);

        $message = $this->translator->trans('Product added to cart successfully', [], 'Modules.Everblock.Shop');
        $this->pushControllerSuccess($message);

        return new CartOperationResult(true, $message, $this->resolveCartSummaryUrl());
    }

    private function ensureCart(): Cart
    {
        if (isset($this->context->cart) && $this->context->cart instanceof Cart && (int) $this->context->cart->id > 0) {
            return $this->context->cart;
        }

        $cart = new Cart();
        $cart->id_lang = (int) $this->context->language->id;
        $cart->id_currency = (int) $this->context->currency->id;
        $cart->id_shop_group = (int) $this->context->shop->id_shop_group;
        $cart->id_shop = (int) $this->context->shop->id;
        $cart->id_customer = (int) $this->context->customer->id;

        if ($cart->id_customer > 0) {
            $cart->id_address_delivery = (int) Address::getFirstCustomerAddressId($cart->id_customer);
            $cart->id_address_invoice = (int) $cart->id_address_delivery;
        } else {
            $cart->id_address_delivery = 0;
            $cart->id_address_invoice = 0;
        }

        if (!$cart->add()) {
            throw new Exception('Failed to create cart');
        }

        $this->context->cart = $cart;

        if (isset($this->context->cookie)) {
            $this->context->cookie->id_cart = (int) $cart->id;
            if (method_exists($this->context->cookie, 'write')) {
                $this->context->cookie->write();
            }
        }

        CartRule::autoRemoveFromCart($this->context);
        CartRule::autoAddToCart($this->context);

        return $cart;
    }

    private function resolveCartSummaryUrl(): ?string
    {
        $link = $this->context->link;

        if ($link instanceof Link) {
            return $link->getPageLink('cart', true, null, ['action' => 'show']);
        }

        return null;
    }

    private function pushControllerSuccess(string $message): void
    {
        if (!isset($this->context->controller)) {
            return;
        }

        if (!is_array($this->context->controller->success ?? null)) {
            $this->context->controller->success = [];
        }

        $this->context->controller->success[] = $message;
    }

    private function pushControllerError(string $message): void
    {
        if (!isset($this->context->controller)) {
            return;
        }

        if (!is_array($this->context->controller->errors ?? null)) {
            $this->context->controller->errors = [];
        }

        $this->context->controller->errors[] = $message;
    }

    private function logException(Exception $exception): void
    {
        if ($this->logger !== null) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
        }
    }
}
