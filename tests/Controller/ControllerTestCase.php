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
        if (file_exists('/buddy/symfony-actuator-bundle/var/cache')) {
            exec('rm -Rf /buddy/symfony-actuator-bundle/var/cache');
        }
        $kernel = new Kernel('test', false);
        $this->client = new KernelBrowser($kernel);
        $this->client->disableReboot();
        $kernel->boot();
    }
}
