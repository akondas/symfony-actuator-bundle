<?php

declare(strict_types=1);

namespace Chaos\ActuatorBundle\Tests\Service\Info;

use Akondas\ActuatorBundle\Service\Info\Info;
use Akondas\ActuatorBundle\Service\Info\InfoStack;
use PHPUnit\Framework\TestCase;

class InfoStackTest extends TestCase
{
    public function testStackDoesNotReturnEmptyInfo(): void
    {
        // given
        $info = new Info('someName', []);
        $infoStack = new InfoStack([$info]);

        // when
        $result = $infoStack->jsonSerialize();

        // then
        self::assertEmpty($result);
    }

    public function testStackReturnInfoWithName(): void
    {
        // given
        $info = new Info('someName', ['someParam' => 'someValue']);
        $infoStack = new InfoStack([$info]);

        // when
        $result = $infoStack->jsonSerialize();

        // then
        self::assertNotEmpty($result);
        self::assertArrayHasKey('someName', $result);
        self::assertIsArray($result['someName']);
        self::assertEquals(['someParam' => 'someValue'], $result['someName']);
    }
}
