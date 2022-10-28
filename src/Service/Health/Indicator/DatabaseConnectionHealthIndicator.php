<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Health\Indicator;

use Akondas\ActuatorBundle\Service\Health\Health;
use Akondas\ActuatorBundle\Service\Health\HealthInterface;
use Akondas\ActuatorBundle\Service\Health\HealthStack;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

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
                    throw new \InvalidArgumentException(sprintf('"connection" should be instance of %s, but got %s', Connection::class, get_class($connection)));
                }

                $detailCheck = [];
                if (null !== $checkSql) {
                    $connection->executeQuery($checkSql);
                    $detailCheck['check_sql'] = $checkSql;
                } else {
                    $connection->connect();
                }

                $healthList[$name] = Health::up($detailCheck);
            } catch (Exception $e) {
                $healthList[$name] = Health::down($e->getMessage());
            } catch (\InvalidArgumentException $e) {
                $healthList[$name] = Health::unknown($e->getMessage());
            }
        }

        if (count($healthList) === 0) {
            return Health::unknown('No database connection checked');
        }
        if (count($healthList) === 1) {
            return current($healthList);
        }

        return new HealthStack($healthList);
    }
}
