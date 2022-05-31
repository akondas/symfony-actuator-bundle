<?php

declare(strict_types=1);

namespace Chaos\ActuatorBundle\Tests\Controller;

use Chaos\ActuatorBundle\Tests\Kernel;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class ControllerTestCase extends TestCase
{
    protected KernelBrowser $client;
    protected KernelInterface $kernel;

    protected function setUp(): void
    {
        $this->kernel = new Kernel('test', false);
        $this->client = new KernelBrowser($this->kernel);
        $this->client->disableReboot();
        $this->kernel->boot();
    }

    /**
     * @param array<mixed> $config
     */
    protected function rebootKernelWithConfig(array $config = []): void
    {
        $this->deleteCache();
        $this->kernel->shutdown();

        $this->kernel = new Kernel('test', false, $config);
        $this->client = new KernelBrowser($this->kernel);
        $this->client->disableReboot();
        $this->kernel->boot();
    }

    protected function tearDown(): void
    {
        $this->deleteCache();
        $this->kernel->shutdown();
    }

    protected function deleteCache(): void
    {
        $cacheDir = $this->kernel->getCacheDir();

        $filesystem = new Filesystem();

        if ($filesystem->exists($cacheDir)) {
            $filesystem->remove($cacheDir);
        }
    }
}
