<?php

declare(strict_types=1);

namespace Chaos\ActuatorBundle\Tests\Service\Health\Indicator\MailerTransport;

use Akondas\ActuatorBundle\Service\Health\Health;
use Akondas\ActuatorBundle\Service\Health\Indicator\MailerTransport\NullTransport as NullTransportIndicator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Transport\NullTransport;

class NullTransportTest extends TestCase
{
    private NullTransportIndicator $nullTransportIndicator;

    private NullTransport $nullTransport;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nullTransportIndicator = new NullTransportIndicator();
        $this->nullTransport = new NullTransport();
    }

    public function testSupportsNullTransport(): void
    {
        self::assertTrue($this->nullTransportIndicator->supports($this->nullTransport));
    }

    public function testIsAlwaysUp(): void
    {
        self::assertTrue($this->nullTransportIndicator->health($this->nullTransport)->isUp());
        self::assertEquals(Health::UP, $this->nullTransportIndicator->health($this->nullTransport)->getStatus());
    }
}
