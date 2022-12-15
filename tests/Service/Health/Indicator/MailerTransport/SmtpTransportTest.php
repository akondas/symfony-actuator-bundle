<?php

declare(strict_types=1);

namespace Chaos\ActuatorBundle\Tests\Service\Health\Indicator\MailerTransport;

use Akondas\ActuatorBundle\Service\Health\Health;
use Akondas\ActuatorBundle\Service\Health\Indicator\MailerTransport\SmtpTransport as SmtpTransportIndicator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\AbstractStream;

class SmtpTransportTest extends TestCase
{
    private SmtpTransportIndicator $smtpTransportIndicator;

    /**
     * @var SmtpTransport&MockObject
     */
    private SmtpTransport $smtpTransport;

    protected function setUp(): void
    {
        parent::setUp();

        $this->smtpTransportIndicator = new SmtpTransportIndicator();
        $this->smtpTransport = self::createMock(SmtpTransport::class);
    }

    public function testSupportsSmtpTransport(): void
    {
        self::assertTrue($this->smtpTransportIndicator->supports($this->smtpTransport));
    }

    public function testSupportsSubClassOfSmtpTransport(): void
    {
        $subClass = new class() extends SmtpTransport {};

        self::assertTrue($this->smtpTransportIndicator->supports($subClass));
    }

    public function testUpIfConnectionCanBeEstablished(): void
    {
        // given
        $stream = self::createMock(AbstractStream::class);

        $this->smtpTransport->expects(self::once())
            ->method('getStream')
            ->willReturn($stream);

        $stream->expects(self::once())
            ->method('initialize');

        $stream->expects(self::once())
            ->method('terminate');

        // when
        $health = $this->smtpTransportIndicator->health($this->smtpTransport);

        // then
        self::assertTrue($health->isUp());
        self::assertEquals(Health::UP, $health->getStatus());
    }

    public function testDownIfConnectionThrowsException(): void
    {
        // given
        $stream = self::createMock(AbstractStream::class);

        $this->smtpTransport->expects(self::once())
            ->method('getStream')
            ->willReturn($stream);

        $stream->expects(self::once())
            ->method('initialize')
            ->willThrowException(new TransportException());

        $stream->expects(self::once())
            ->method('terminate');

        // when
        $health = $this->smtpTransportIndicator->health($this->smtpTransport);

        // then
        self::assertFalse($health->isUp());
        self::assertEquals(Health::DOWN, $health->getStatus());
    }
}
