<?php

namespace Chaos\ActuatorBundle\Tests\Service\Health\Indicator;

use Akondas\ActuatorBundle\Service\Health\Indicator\Mailer;
use Akondas\ActuatorBundle\Service\Health\Indicator\MailerTransport\TransportHealthIndicator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Transport\TransportInterface;

class MailerTest extends TestCase
{
    public function testCorrectName(): void
    {
        $mailer = $this->build();

        self::assertEquals('mailer', $mailer->name());
    }

    public function testHealthNOKIfNoTransports(): void
    {
        $mailer = $this->build();

        $health = $mailer->health();

        self::assertFalse($health->isUp());
    }

    /**
     * @param array<string, TransportInterface> $transports
     * @param array<TransportHealthIndicator> $transportHealthIndicators
     * @return Mailer
     */
    private function build(array $transports = [], array $transportHealthIndicators = []): Mailer
    {
        return new Mailer($transports, $transportHealthIndicators);
    }

}
