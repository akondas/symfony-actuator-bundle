<?php

declare(strict_types=1);

namespace Chaos\ActuatorBundle\Tests\Service\Info\Collector;

use Akondas\ActuatorBundle\Service\Info\Collector\Mailer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Transport\TransportInterface;

class MailerTest extends TestCase
{
    public function testWillDisplayDSNString(): void
    {
        // given
        $transport = self::createMock(TransportInterface::class);
        $transport->expects(self::once())
            ->method('__toString')
            ->willReturn('dsnIdentifier');

        $mailer = $this->build(['name' => $transport]);

        // when
        $info = $mailer->collect();

        // then
        self::assertFalse($info->isEmpty());
        self::assertEquals('mailer', $info->name());
        $infoArray = $info->jsonSerialize();
        self::assertArrayHasKey('transport', $infoArray);

        self::assertIsArray($infoArray['transport']);
        self::assertArrayHasKey('name', $infoArray['transport']);

        self::assertIsArray($infoArray['transport']['name']);
        self::assertArrayHasKey('class', $infoArray['transport']['name']);
        self::assertArrayHasKey('dsn', $infoArray['transport']['name']);

        self::assertEquals(get_class($transport), $infoArray['transport']['name']['class']);
        self::assertEquals('dsnIdentifier', $infoArray['transport']['name']['dsn']);
    }

    public function testEmptyWithoutTransports(): void
    {
        // given
        $mailer = $this->build();

        // when
        $info = $mailer->collect();

        // then
        self::assertTrue($info->isEmpty());
    }

    /**
     * @param array<string, TransportInterface> $transports
     */
    protected function build(array $transports = []): Mailer
    {
        return new Mailer($transports);
    }
}
