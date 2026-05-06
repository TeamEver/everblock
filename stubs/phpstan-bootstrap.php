<?php

namespace {
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
