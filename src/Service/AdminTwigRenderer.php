<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 *
 * @author    Team Ever <https://www.team-ever.com/>
 * @copyright Since 2007 Team Ever
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace Everblock\Tools\Service;

use Twig\Environment;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Exposes the Twig environment to the legacy module class.
 *
 * PrestaShop 8 ships Symfony 4.4, where the "twig" service is declared public,
 * so a legacy hook could simply call $container->get('twig'). PrestaShop 9
 * ships Symfony 6.4, which no longer declares it public: both has('twig') and
 * get('twig') fail from the legacy context, silently breaking every admin panel
 * rendered from a hook.
 *
 * Depending on a private service is perfectly legal, so this module-owned
 * public service receives the environment by injection and hands it back.
 * Registered in config/services.yml as everblock.twig_renderer.
 */
final class AdminTwigRenderer
{
    /**
     * @var Environment
     */
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function render(string $template, array $parameters = []): string
    {
        return (string) $this->twig->render($template, $parameters);
    }

    public function getEnvironment(): Environment
    {
        return $this->twig;
    }
}
