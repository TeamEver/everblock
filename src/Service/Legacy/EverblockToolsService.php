<?php

namespace Everblock\Tools\Service\Legacy;

use BadMethodCallException;
final class EverblockToolsService
{
    /**
     * @param array<int, mixed> $arguments
     */
    public function __call(string $name, array $arguments)
    {
        if (!method_exists(\EverblockLegacyTools::class, $name)) {
            throw new BadMethodCallException(sprintf('Method %s::%s does not exist.', \EverblockLegacyTools::class, $name));
        }

        return call_user_func_array([\EverblockLegacyTools::class, $name], $arguments);
    }
}
