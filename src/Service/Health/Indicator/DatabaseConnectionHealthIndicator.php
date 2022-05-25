<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Health\Indicator;

use Akondas\ActuatorBundle\Service\Health\Health;
use Akondas\ActuatorBundle\Service\Health\HealthInterface;
use Akondas\ActuatorBundle\Service\Health\HealthStack;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\ConnectionException;
use InvalidArgumentException;

class DatabaseConnectionHealthIndicator implements HealthIndicator
{

    /**
     * @var array<array{'connection': Connection, 'sql': ?string}>
     */
    private array $checks;

    /**
     * @param array<array{'connection': Connection, 'sql': ?string}> $checks
     */
    public function __construct(array $checks = [])
    {
        $this->checks = $checks;
    }

    public function name(): string
    {
        return 'database';
    }

    public function health(): HealthInterface
    {
        $healthList = [];
        foreach ($this->checks as $name => $check) {
            $connection = $check['connection'];
            $checkSql = $check['sql'];
            try {
                if (!$connection instanceof Connection) {
                    throw new InvalidArgumentException();
                }

                if (null !== $checkSql) {
                    $connection->executeQuery($checkSql);
                } else {
                    $connection->connect();
                }

                $healthList[$name] = Health::up()->setDetails(['checkedWith' => null !== $checkSql ? 'sql' : 'connection']);
            } catch (ConnectionException $e) {
                $healthList[$name] = Health::down()->setDetails(['checkedWith' => null !== $checkSql ? 'sql' : 'connection', 'error' => $e->getMessage()]);
            }
        }

        return new HealthStack($healthList);
    }
}
