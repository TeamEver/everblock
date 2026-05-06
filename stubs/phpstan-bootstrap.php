<?php

namespace {
    if (!defined('_PS_VERSION_')) {
        define('_PS_VERSION_', '9.0.0');
    }

    if (!class_exists('Module', false)) {
        class Module
        {
            public mixed $version;

            public static function getInstanceByName(string $name): ?self
            {
                unset($name);

                return null;
            }

            public function encrypt($data)
            {
                return (string) $data;
            }
        }
    }

    if (!class_exists('AbstractCheckoutStep', false)) {
        abstract class AbstractCheckoutStep
        {
            public mixed $context;

            public function __construct(mixed $context, mixed $translator)
            {
                $this->context = $context;
                unset($translator);
            }

            public function setTitle(string $title): self
            {
                unset($title);

                return $this;
            }
        }
    }

    if (!class_exists('Qcdpagebuilder', false)) {
        class Qcdpagebuilder extends Module
        {
            public function renderTargetField(
                string $module,
                int $id,
                string $field,
                string $value,
                int $idShop,
                int $idLang
            ): string {
                return $value;
            }
        }
    }
}

namespace Symfony\Component\Console\Command {
    if (!class_exists(__NAMESPACE__ . '\\Command', false)) {
        class Command
        {
            public const SUCCESS = 0;
            public const FAILURE = 1;
            public const INVALID = 2;

            public function __construct(?string $name = null)
            {
                unset($name);
            }

            public function setName(string $name): static
            {
                unset($name);

                return $this;
            }

            public function setDescription(string $description): static
            {
                unset($description);

                return $this;
            }

            public function setHelp(string $help): static
            {
                unset($help);

                return $this;
            }
        }
    }
}

namespace Symfony\Component\Console\Formatter {
    if (!class_exists(__NAMESPACE__ . '\\OutputFormatterStyle', false)) {
        class OutputFormatterStyle
        {
            public function __construct(?string $foreground = null, ?string $background = null, array $options = [])
            {
                unset($foreground, $background, $options);
            }
        }
    }
}

namespace Symfony\Component\Console\Input {
    if (!interface_exists(__NAMESPACE__ . '\\InputInterface', false)) {
        interface InputInterface
        {
        }
    }
}

namespace Symfony\Component\Console\Output {
    if (!interface_exists(__NAMESPACE__ . '\\OutputInterface', false)) {
        interface OutputInterface
        {
        }
    }
}

namespace Symfony\Component\Form {
    if (!class_exists(__NAMESPACE__ . '\\AbstractType', false)) {
        abstract class AbstractType
        {
        }
    }

    if (!interface_exists(__NAMESPACE__ . '\\FormBuilderInterface', false)) {
        interface FormBuilderInterface
        {
        }
    }
}

namespace Symfony\Component\Form\Extension\Core\Type {
    if (!class_exists(__NAMESPACE__ . '\\CheckboxType', false)) {
        class CheckboxType
        {
        }
    }

    if (!class_exists(__NAMESPACE__ . '\\ChoiceType', false)) {
        class ChoiceType
        {
        }
    }

    if (!class_exists(__NAMESPACE__ . '\\HiddenType', false)) {
        class HiddenType
        {
        }
    }

    if (!class_exists(__NAMESPACE__ . '\\IntegerType', false)) {
        class IntegerType
        {
        }
    }

    if (!class_exists(__NAMESPACE__ . '\\TextareaType', false)) {
        class TextareaType
        {
        }
    }

    if (!class_exists(__NAMESPACE__ . '\\TextType', false)) {
        class TextType
        {
        }
    }
}

namespace Symfony\Component\HttpKernel {
    if (!interface_exists(__NAMESPACE__ . '\\KernelInterface', false)) {
        interface KernelInterface
        {
        }
    }
}

namespace Symfony\Component\HttpFoundation {
    if (!class_exists(__NAMESPACE__ . '\\Response', false)) {
        class Response
        {
        }
    }

    if (!class_exists(__NAMESPACE__ . '\\RedirectResponse', false)) {
        class RedirectResponse extends Response
        {
        }
    }

    if (!class_exists(__NAMESPACE__ . '\\Request', false)) {
        class Request
        {
            public mixed $request;
            public mixed $query;
            public mixed $files;
        }
    }
}

namespace Symfony\Component\HttpFoundation\File {
    if (!class_exists(__NAMESPACE__ . '\\UploadedFile', false)) {
        class UploadedFile
        {
        }
    }
}

namespace Symfony\Component\OptionsResolver {
    if (!class_exists(__NAMESPACE__ . '\\OptionsResolver', false)) {
        class OptionsResolver
        {
            public function setDefaults(array $defaults): void
            {
                unset($defaults);
            }
        }
    }
}

namespace Symfony\Component\Translation {
    if (!interface_exists(__NAMESPACE__ . '\\TranslatorInterface', false)) {
        interface TranslatorInterface
        {
        }
    }
}

namespace Symfony\Contracts\Translation {
    if (!interface_exists(__NAMESPACE__ . '\\TranslatorInterface', false)) {
        interface TranslatorInterface
        {
        }
    }
}

namespace PrestaShopBundle\Controller\Admin {
    if (!class_exists(__NAMESPACE__ . '\\FrameworkBundleAdminController', false)) {
        class FrameworkBundleAdminController
        {
            public function addFlash(string $type, mixed $message): void
            {
                unset($type, $message);
            }

            public function createForm(string $type, mixed $data = null, array $options = []): mixed
            {
                unset($type, $data, $options);

                return null;
            }

            public function createNotFoundException(string $message = ''): \RuntimeException
            {
                return new \RuntimeException($message);
            }

            public function redirectToRoute(string $route, array $parameters = []): \Symfony\Component\HttpFoundation\RedirectResponse
            {
                unset($route, $parameters);

                return new \Symfony\Component\HttpFoundation\RedirectResponse();
            }

            public function render(string $view, array $parameters = []): \Symfony\Component\HttpFoundation\Response
            {
                unset($view, $parameters);

                return new \Symfony\Component\HttpFoundation\Response();
            }
        }
    }
}

namespace Everblock\Tools\Service {
    if (!class_exists(__NAMESPACE__ . '\\Qcdpagebuilder', false)) {
        class Qcdpagebuilder extends \Qcdpagebuilder
        {
        }
    }

    if (!class_exists(__NAMESPACE__ . '\\qcdacf', false)) {
        class qcdacf
        {
            public static function getVar(string $name, string $objectType, int $objectId, int $idLang): string
            {
                return '';
            }
        }
    }
}
