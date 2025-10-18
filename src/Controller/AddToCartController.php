<?php

namespace Everblock\Tools\Controller;

use Everblock\Tools\Application\Cart\CartManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;

final class AddToCartController
{
    public function __construct(
        private readonly CartManager $cartManager,
        private readonly RouterInterface $router,
    ) {
    }

    public function add(Request $request): Response
    {
        $productId = (int) $request->get('id_product', 0);
        $productAttributeId = (int) $request->get('id_product_attribute', 0);
        $quantity = (int) $request->get('qty', 1);

        $result = $this->cartManager->addProduct($productId, $productAttributeId, $quantity);

        if ($this->shouldReturnJson($request)) {
            return new JsonResponse([
                'success' => $result->isSuccess(),
                'message' => $result->getMessage(),
                'errors' => $result->getErrors(),
                'redirect' => $result->getRedirectUrl(),
            ], $result->isSuccess() ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);
        }

        $target = $result->getRedirectUrl();
        if ($target === null || $target === '') {
            $target = $request->headers->get('referer');
        }

        if ($target === null || $target === '') {
            try {
                $target = $this->router->generate('index', [], UrlGeneratorInterface::ABSOLUTE_PATH);
            } catch (Throwable) {
                $target = '/';
            }
        }

        return new RedirectResponse($target);
    }

    private function shouldReturnJson(Request $request): bool
    {
        if ($request->isXmlHttpRequest()) {
            return true;
        }

        $format = $request->getRequestFormat(null);
        if ($format === 'json') {
            return true;
        }

        $accept = $request->headers->get('Accept');
        if (is_string($accept) && str_contains($accept, 'application/json')) {
            return true;
        }

        return false;
    }
}
