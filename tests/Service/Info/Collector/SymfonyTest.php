<?php

declare(strict_types=1);

namespace Chaos\ActuatorBundle\Tests\Service\Info\Collector;

use Akondas\ActuatorBundle\Service\Info\Collector\Symfony;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class SymfonyTest extends TestCase
{
    /**
     * @var KernelInterface&MockObject
     */
    private KernelInterface $kernel;

    private Symfony $symfony;

    protected function setUp(): void
    {
        $this->kernel = self::createMock(KernelInterface::class);

        $this->symfony = new Symfony($this->kernel);
    }

    public function testNameWillBeSymfony(): void
    {
        $this->kernel->method('getBundles')
            ->willReturn([]);

        self::assertEquals('symfony', $this->symfony->collect()->name());
    }

    public function testSymfonyInformations(): void
    {
        // given
        $this->kernel->method('getBundles')
            ->willReturn([]);

        // when
        $info = $this->symfony->collect();

        // then
        self::assertFalse($info->isEmpty());
    }

    public function testEnvironmentIsReadFromKernel(): void
    {
        // given
        $this->kernel->method('getEnvironment')
            ->willReturn('someValue');

        $this->kernel->method('getBundles')
            ->willReturn([]);

        // when
        $info = $this->symfony->collect();

        // then
        self::assertArrayHasKey('environment', $info->jsonSerialize());
        self::assertEquals('someValue', $info->jsonSerialize()['environment']);
    }

    public function testBundlesAreReadFromKernel(): void
    {
        // given
        $bundle = self::createMock(BundleInterface::class);
        $class = get_class($bundle);
        $this->kernel->method('getBundles')
            ->willReturn([$bundle]);

        // when
        $info = $this->symfony->collect();

        // then
        self::assertArrayHasKey('bundles', $info->jsonSerialize());
        self::assertIsArray($info->jsonSerialize()['bundles']);
        self::assertCount(1, $info->jsonSerialize()['bundles']);
        self::assertEquals($class, $info->jsonSerialize()['bundles'][0]);
    }
}
