<?php

namespace Everblock\Tools\Shortcode;

use Context;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

final class ShortcodeRenderingContextFactory
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ?Security $security = null
    ) {
    }

    public function createFromContext(Context $context): ShortcodeRenderingContext
    {
        return new ShortcodeRenderingContext($context, $this->requestStack, $this->security);
    }
}
