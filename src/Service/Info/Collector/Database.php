<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Info\Collector;

use Akondas\ActuatorBundle\Service\Info\Info;
use Doctrine\DBAL\Connection;

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
                throw new \InvalidArgumentException();
            }

            $connectionParams = $connection->getParams();
            $connectionInfo[$name] = [
                'type' => trim((new \ReflectionClass($connection->getDatabasePlatform()))->getShortName(), 'Platform'),
                'driver' => $connectionParams['driver'] ?? get_class($connection->getDriver()),
            ];

            if (isset($connectionParams['path'])) {
                $connectionInfo[$name]['path'] = $connectionParams['path'];
            }

            if (isset($connectionParams['dbname'])) {
                $connectionInfo[$name]['dbname'] = $connectionParams['dbname'];
            }

            if (isset($connectionParams['host'])) {
                $connectionInfo[$name]['host'] = $connectionParams['host'];
            }

            if (isset($connectionParams['port'])) {
                $connectionInfo[$name]['port'] = $connectionParams['port'];
            }
        }

        return new Info('database', $connectionInfo);
    }
}
