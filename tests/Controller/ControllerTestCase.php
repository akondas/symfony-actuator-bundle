<?php

declare(strict_types=1);

namespace Chaos\ActuatorBundle\Tests\Controller;

use Chaos\ActuatorBundle\Tests\Kernel;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

abstract class ControllerTestCase extends TestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = new KernelBrowser($kernel = new Kernel('test', false));
        $this->client->disableReboot();
        $kernel->boot();
    }
}
