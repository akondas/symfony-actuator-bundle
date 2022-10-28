<?php

declare(strict_types=1);

namespace Chaos\ActuatorBundle\Tests\Service\Info\Collector;

use Akondas\ActuatorBundle\Service\Info\Collector\Database;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    public function testNameWillBeDatabase(): void
    {
        self::assertEquals('database', (new Database([]))->collect()->name());
    }

    public function testDatabaseWillReturnUnknownIfNoConnection(): void
    {
        $collection = (new Database(['default' => new \stdClass()]))->collect();

        self::assertFalse($collection->isEmpty());
        self::assertArrayHasKey('default', $collection->jsonSerialize());

        self::assertArrayHasKey('type', $collection->jsonSerialize()['default']);
        self::assertEquals('Unknown', $collection->jsonSerialize()['default']['type']);
        self::assertArrayHasKey('database', $collection->jsonSerialize()['default']);
        self::assertEquals('Unknown', $collection->jsonSerialize()['default']['database']);
        self::assertArrayHasKey('driver', $collection->jsonSerialize()['default']);
        self::assertEquals('Unknown', $collection->jsonSerialize()['default']['driver']);
    }

    public function testDatabaseIsUnknownIfTypeThrows(): void
    {
        $connection = self::createMock(Connection::class);

        $driver = self::createMock(Driver::class);

        $databasePlatform = self::createMock(AbstractPlatform::class);
        $connection->method('getDatabasePlatform')
            ->willReturn($databasePlatform);

        $connection->method('getDriver')
            ->willReturn($driver);

        $collection = (new Database(['default' => $connection]))->collect();

        self::assertFalse($collection->isEmpty());
        self::assertArrayHasKey('default', $collection->jsonSerialize());

        self::assertArrayHasKey('type', $collection->jsonSerialize()['default']);
        self::assertNotEmpty($collection->jsonSerialize()['default']['type']);
        self::assertArrayHasKey('database', $collection->jsonSerialize()['default']);
        self::assertNull($collection->jsonSerialize()['default']['database']);
        self::assertArrayHasKey('driver', $collection->jsonSerialize()['default']);
        self::assertNotEmpty($collection->jsonSerialize()['default']['type']);
    }
}
