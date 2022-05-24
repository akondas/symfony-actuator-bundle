<?php

declare(strict_types=1);

namespace Chaos\ActuatorBundle\Tests\Service\Health;

use Akondas\ActuatorBundle\Service\Health\Health;
use Akondas\ActuatorBundle\Service\Health\HealthIndicatorStack;
use Akondas\ActuatorBundle\Service\Health\Indicator\HealthIndicator;
use PHPUnit\Framework\TestCase;

class HealthIndicatorStackTest extends TestCase
{
    public function testOverallStateIsUPIfAllIndicatorsAreUP(): void
    {
        // given
        $indicator1 = self::createMock(HealthIndicator::class);
        $indicator1->method('health')
            ->willReturn(Health::up());

        $indicator2 = self::createMock(HealthIndicator::class);
        $indicator2->method('health')
            ->willReturn(Health::up());

        $stack = new HealthIndicatorStack([$indicator1, $indicator2]);
        // when
        $response = $stack->check()->jsonSerialize();

        // then
        self::assertArrayHasKey('status', $response);
        self::assertEquals(Health::UP, $response['status']);
    }

    public function testOverallStateIsDownIfOneIndicatorIsDown(): void
    {
        // given
        $indicator1 = self::createMock(HealthIndicator::class);
        $indicator1->method('health')
            ->willReturn(Health::up());

        $indicator2 = self::createMock(HealthIndicator::class);
        $indicator2->method('health')
            ->willReturn(Health::down());

        $stack = new HealthIndicatorStack([$indicator1, $indicator2]);
        // when
        $response = $stack->check()->jsonSerialize();

        // then
        self::assertArrayHasKey('status', $response);
        self::assertEquals(Health::DOWN, $response['status']);
    }

    public function testOverallStateIsUnknownIfOneIndicatorIsUnknown(): void
    {
        // given
        $indicator1 = self::createMock(HealthIndicator::class);
        $indicator1->method('health')
            ->willReturn(Health::up());

        $indicator2 = self::createMock(HealthIndicator::class);
        $indicator2->method('health')
            ->willReturn(Health::unknown());

        $stack = new HealthIndicatorStack([$indicator1, $indicator2]);

        // when
        $response = $stack->check()->jsonSerialize();

        // then
        self::assertArrayHasKey('status', $response);
        self::assertEquals(Health::UNKNOWN, $response['status']);
    }
}
