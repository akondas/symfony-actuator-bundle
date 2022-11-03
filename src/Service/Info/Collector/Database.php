<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Info\Collector;

use Akondas\ActuatorBundle\Service\Info\Info;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

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
                $connectionInfo[$name] = [
                    'type' => 'Unknown',
                    'database' => 'Unknown',
                    'driver' => 'Unknown',
                ];

                continue;
            }

            try {
                $platform = $connection->getDatabasePlatform();
                if ($platform === null) { // @phpstan-ignore-line
                    $type = 'Unknown';
                } else {
                    $type = trim((new \ReflectionClass($connection->getDatabasePlatform()))->getShortName(), 'Platform');
                }
            } catch (Exception $e) {
                $type = 'Unknown';
            }

            try {
                $database = $connection->getDatabase();
            } catch (Exception $e) {
                $database = 'Unknown';
            }

            $connectionInfo[$name] = [
            'type' => $type,
            'database' => $database,
            'driver' => get_class($connection->getDriver()),
            ];
        }

        return new Info('database', $connectionInfo);
    }
}
