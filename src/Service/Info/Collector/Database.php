<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Info\Collector;

use Akondas\ActuatorBundle\Service\Info\Info;
use Doctrine\DBAL\Connection;
use InvalidArgumentException;

class Database implements Collector
{
    /**
     * @var array<string, Connection>
     */
    private array $connections;

    /**
     * @param array<string, Connection> $connections
     */
    public function __construct(array $connections)
    {
        $this->connections = $connections;
    }

    public function collect(): Info
    {
        $connectionInfo = [];
        foreach ($this->connections as $name => $connection) {
            if (!$connection instanceof Connection) {
                throw new InvalidArgumentException();
            }

            $connectionInfo[$name] = [
                'type' => trim((new \ReflectionClass($connection->getDatabasePlatform()))->getShortName(), 'Platform'),
                'driver' => get_class($connection->getDriver())
            ];
        }
        return new Info('database', $connectionInfo);
    }
}
