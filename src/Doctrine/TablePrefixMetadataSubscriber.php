<?php

namespace Everblock\Tools\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;

class TablePrefixMetadataSubscriber implements EventSubscriber
{
    /**
     * @var string
     */
    private $prefix;

    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @return array<int, string>
     */
    public function getSubscribedEvents(): array
    {
        return [Events::loadClassMetadata];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        if ('' === $this->prefix) {
            return;
        }

        $classMetadata = $eventArgs->getClassMetadata();
        $tableName = $classMetadata->getTableName();

        if (!$this->startsWithPrefix($tableName)) {
            $classMetadata->setPrimaryTable([
                'name' => $this->prefix . $tableName,
            ]);
        }

        foreach ($classMetadata->associationMappings as $name => $mapping) {
            if (!isset($mapping['joinTable']['name'])) {
                continue;
            }

            $joinTableName = $mapping['joinTable']['name'];

            if ($this->startsWithPrefix($joinTableName)) {
                continue;
            }

            $mapping['joinTable']['name'] = $this->prefix . $joinTableName;
            $classMetadata->associationMappings[$name] = $mapping;
        }
    }

    private function startsWithPrefix(string $value): bool
    {
        return 0 === strpos($value, $this->prefix);
    }
}
