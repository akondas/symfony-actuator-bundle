<?php

declare(strict_types=1);

namespace Chaos\ActuatorBundle\Tests\Service\Health\Indicator;

use Akondas\ActuatorBundle\Service\Health\Health;
use Akondas\ActuatorBundle\Service\Health\HealthStack;
use Akondas\ActuatorBundle\Service\Health\Indicator\Database;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\DriverException;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    public function testName(): void
    {
        $databaseHealthIndicator = new Database([]);

        self::assertEquals('database', $databaseHealthIndicator->name());
    }

    public function testWillThrowIfConnectionIsNotConnection(): void
    {
        $connection = new \stdClass();
        $checks = [
            [
                'connection' => $connection,
                'sql' => null,
            ],
        ];

        $healthIndicator = new Database($checks); // @phpstan-ignore-line

        $health = $healthIndicator->health();

        self::assertEquals(Health::UNKNOWN, $health->getStatus());
    }

    public function testWillConnectToConnectionIfNoSql(): void
    {
        $connection = self::createMock(Connection::class);

        $connection->expects(self::once())
            ->method('connect');

        $checks = [
            [
                'connection' => $connection,
                'sql' => null,
            ],
        ];
        $healthIndicator = new Database($checks);

        $health = $healthIndicator->health();

        self::assertTrue($health->isUp());
    }

    public function testHealthIsNotUpIfConnectionFails(): void
    {
        $connection = self::createMock(Connection::class);

        $connection->expects(self::once())
            ->method('connect')
            ->willThrowException(self::createMock(DriverException::class))
        ;

        $checks = [
            [
                'connection' => $connection,
                'sql' => null,
            ],
        ];
        $healthIndicator = new Database($checks);

        $health = $healthIndicator->health();

        self::assertFalse($health->isUp());
    }

    public function testWillCheckConnectionWithSql(): void
    {
        $connection = self::createMock(Connection::class);

        $connection->expects(self::once())
            ->method('executeQuery')
        ;

        $checks = [
            [
                'connection' => $connection,
                'sql' => 'SELECT 1=1',
            ],
        ];
        $healthIndicator = new Database($checks);

        $health = $healthIndicator->health();

        self::assertTrue($health->isUp());
    }

    public function testHealthIsNotUpIfSqlFails(): void
    {
        $connection = self::createMock(Connection::class);

        $connection->expects(self::once())
            ->method('executeQuery')
            ->willThrowException(self::createMock(DriverException::class))
        ;

        $checks = [
            [
                'connection' => $connection,
                'sql' => 'SELECT 1=1',
            ],
        ];
        $healthIndicator = new Database($checks);

        $health = $healthIndicator->health();

        self::assertFalse($health->isUp());
    }

    public function testHealthChecksEveryConnection(): void
    {
        $connection1 = self::createMock(Connection::class);
        $connection2 = self::createMock(Connection::class);

        $connection1->expects(self::once())
            ->method('executeQuery')
        ;

        $connection2->expects(self::once())
            ->method('connect')
        ;

        $checks = [
            'conn1' => [
                'connection' => $connection1,
                'sql' => 'SELECT 1=1',
            ],
            'conn2' => [
                'connection' => $connection2,
                'sql' => null,
            ],
        ];
        $healthIndicator = new Database($checks);

        $health = $healthIndicator->health();

        self::assertInstanceOf(HealthStack::class, $health);
        self::assertCount(3, $health->jsonSerialize());
        self::assertArrayHasKey('conn1', $health->jsonSerialize());
        self::assertArrayHasKey('conn2', $health->jsonSerialize());
    }
}
