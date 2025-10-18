<?php

declare(strict_types=1);

namespace Everblock\Tools\Tests\Controller;

use Everblock\Tools\Application\Cart\CartManager;
use Everblock\Tools\Application\Cart\CartOperationResult;
use Everblock\Tools\Controller\AddToCartController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

final class AddToCartControllerTest extends TestCase
{
    public function testAjaxRequestReturnsJsonResponse(): void
    {
        $manager = $this->createMock(CartManager::class);
        $manager->expects(self::once())
            ->method('addProduct')
            ->with(42, 11, 3)
            ->willReturn(new CartOperationResult(true, 'ok', '/cart'));

        $router = $this->createMock(RouterInterface::class);
        $router->expects(self::never())->method('generate');

        $controller = new AddToCartController($manager, $router);
        $request = new Request([
            'id_product' => 42,
            'id_product_attribute' => 11,
            'qty' => 3,
        ], [], [], [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $response = $controller->add($request);

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(200, $response->getStatusCode());
        $payload = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame([
            'success' => true,
            'message' => 'ok',
            'errors' => [],
            'redirect' => '/cart',
        ], $payload);
    }

    public function testRedirectUsesResultUrlWhenAvailable(): void
    {
        $manager = $this->createMock(CartManager::class);
        $manager->expects(self::once())
            ->method('addProduct')
            ->with(51, 0, 1)
            ->willReturn(new CartOperationResult(true, 'added', '/cart'));

        $router = $this->createMock(RouterInterface::class);
        $router->expects(self::never())->method('generate');

        $controller = new AddToCartController($manager, $router);
        $request = new Request(['id_product' => 51]);

        $response = $controller->add($request);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/cart', $response->getTargetUrl());
    }

    public function testRedirectFallsBackToRouterWhenNoReferer(): void
    {
        $manager = $this->createMock(CartManager::class);
        $manager->expects(self::once())
            ->method('addProduct')
            ->with(19, 0, 1)
            ->willReturn(new CartOperationResult(false, 'ko'));

        $router = $this->createMock(RouterInterface::class);
        $router->expects(self::once())
            ->method('generate')
            ->with('index', [], UrlGeneratorInterface::ABSOLUTE_PATH)
            ->willReturn('/');

        $controller = new AddToCartController($manager, $router);
        $request = new Request(['id_product' => 19]);

        $response = $controller->add($request);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/', $response->getTargetUrl());
    }
}
