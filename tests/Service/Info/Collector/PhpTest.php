<?php

declare(strict_types=1);

namespace Chaos\ActuatorBundle\Tests\Service\Info\Collector;

use Akondas\ActuatorBundle\Service\Info\Collector\Php;
use PHPUnit\Framework\TestCase;

class PhpTest extends TestCase
{
    private Php $php;

    protected function setUp(): void
    {
        $this->php = new Php();
    }

    public function testNameWillBePhp(): void
    {
        self::assertEquals('php', $this->php->collect()->name());
    }

    public function testPhpInformations(): void
    {
        // when
        $info = $this->php->collect();

        // then
        self::assertFalse($info->isEmpty());
    }
}
