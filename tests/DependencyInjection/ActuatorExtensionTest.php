<?php

declare(strict_types=1);

namespace Chaos\ActuatorBundle\Tests\DependencyInjection;

use Akondas\ActuatorBundle\DependencyInjection\ActuatorExtension;
use Akondas\ActuatorBundle\Service\Health\Indicator\Database;
use Akondas\ActuatorBundle\Service\Health\Indicator\DiskSpace;
use Akondas\ActuatorBundle\Service\Health\Indicator\Mailer;
use Akondas\ActuatorBundle\Service\Info\Collector\Git;
use Akondas\ActuatorBundle\Service\Info\Collector\Php;
use Akondas\ActuatorBundle\Service\Info\Collector\Symfony;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ActuatorExtensionTest extends TestCase
{
    private ActuatorExtension $extension;
    private ContainerBuilder $containerBuilder;

    protected function setUp(): void
    {
        $this->extension = new ActuatorExtension();
        $this->containerBuilder = new ContainerBuilder();
    }

    public function testDefaultConfigHasHealthFromEnvVariable(): void
    {
        // when
        $this->extension->load([], $this->containerBuilder);

        // then
        self::assertTrue($this->containerBuilder->hasParameter('actuator.health.enabled'));
        self::assertTrue($this->containerBuilder->getParameter('actuator.health.enabled'));
    }

    public function testHealthEnabledCanBeSetWithConfig(): void
    {
        // when
        $this->extension->load(['actuator' => ['health' => ['enabled' => false]]], $this->containerBuilder);

        // then
        self::assertTrue($this->containerBuilder->hasParameter('actuator.health.enabled'));
        self::assertFalse($this->containerBuilder->getParameter('actuator.health.enabled'));
    }

    public function testHealthBuiltinDiskSpaceIsEnabledByDefault(): void
    {
        // when
        $this->extension->load([], $this->containerBuilder);

        // then
        self::assertTrue($this->containerBuilder->hasDefinition(DiskSpace::class));
    }

    public function testHealthBuiltinDiskSpaceHasDefaultThreshold(): void
    {
        // when
        $this->extension->load([], $this->containerBuilder);

        // then
        self::assertTrue($this->containerBuilder->hasDefinition(DiskSpace::class));
        self::assertCount(2, $this->containerBuilder->getDefinition(DiskSpace::class)->getArguments());
        self::assertEquals(50 * 1024 * 1024, $this->containerBuilder->getDefinition(DiskSpace::class)->getArguments()[1]);
    }

    public function testHealthBuiltinDiskSpaceHasDefaultDirectory(): void
    {
        // when
        $this->extension->load([], $this->containerBuilder);

        // then
        self::assertTrue($this->containerBuilder->hasDefinition(DiskSpace::class));
        self::assertCount(2, $this->containerBuilder->getDefinition(DiskSpace::class)->getArguments());
        self::assertEquals('%kernel.project_dir%', $this->containerBuilder->getDefinition(DiskSpace::class)->getArguments()[0]);
    }

    public function testHealthBuiltinDiskSpaceCanBeDisabled(): void
    {
        // given
        $config = ['actuator' => ['health' => ['builtin' => ['disk_space' => ['enabled' => false]]]]];

        // when
        $this->extension->load($config, $this->containerBuilder);

        // then
        self::assertFalse($this->containerBuilder->hasDefinition(DiskSpace::class));
    }

    public function testHealthBuiltinDiskSpaceThresholdCanBeOverwritten(): void
    {
        // given
        $config = ['actuator' => ['health' => ['builtin' => ['disk_space' => ['threshold' => 1024]]]]];

        // when
        $this->extension->load($config, $this->containerBuilder);

        // then
        self::assertTrue($this->containerBuilder->hasDefinition(DiskSpace::class));
        self::assertCount(2, $this->containerBuilder->getDefinition(DiskSpace::class)->getArguments());
        self::assertEquals(1024, $this->containerBuilder->getDefinition(DiskSpace::class)->getArguments()[1]);
    }

    public function testHealthBuiltinDiskSpaceDirectoryCanBeOverwritten(): void
    {
        // given
        $config = ['actuator' => ['health' => ['builtin' => ['disk_space' => ['path' => 'someValue']]]]];

        // when
        $this->extension->load($config, $this->containerBuilder);

        // then
        self::assertTrue($this->containerBuilder->hasDefinition(DiskSpace::class));
        self::assertCount(2, $this->containerBuilder->getDefinition(DiskSpace::class)->getArguments());
        self::assertEquals('someValue', $this->containerBuilder->getDefinition(DiskSpace::class)->getArguments()[0]);
    }

    public function testHealthDatabaseDefaultDisabled(): void
    {
        // given
        $config = ['actuator' => ['health' => ['builtin' => ['database' => ['enabled' => true]]]]];

        // when
        $this->extension->load($config, $this->containerBuilder);

        // then
        self::assertFalse($this->containerBuilder->hasDefinition(Database::class));
    }

    public function testHealthMailerDefaultDisabled(): void
    {
        // given
        $config = ['actuator' => ['health' => ['builtin' => ['mailer' => ['enabled' => true]]]]];

        // when
        $this->extension->load($config, $this->containerBuilder);

        // then
        self::assertFalse($this->containerBuilder->hasDefinition(Mailer::class));
    }

    public function testInfoIsEnabledByDefault(): void
    {
        // given
        $config = [];

        // when
        $this->extension->load($config, $this->containerBuilder);

        // then
        self::assertTrue($this->containerBuilder->hasParameter('actuator.info.enabled'));
        self::assertTrue($this->containerBuilder->getParameter('actuator.info.enabled'));
    }

    public function testInfoCanBeDisabled(): void
    {
        // given
        $config = ['actuator' => ['info' => ['enabled' => false]]];

        // when
        $this->extension->load($config, $this->containerBuilder);

        // then
        self::assertTrue($this->containerBuilder->hasParameter('actuator.info.enabled'));
        self::assertFalse($this->containerBuilder->getParameter('actuator.info.enabled'));
    }

    public function testInfoBuiltinDefaultList(): void
    {
        // given
        $config = [];

        // when
        $this->extension->load($config, $this->containerBuilder);

        // then
        self::assertTrue($this->containerBuilder->hasDefinition(Php::class));
        self::assertTrue($this->containerBuilder->hasDefinition(Symfony::class));
        self::assertTrue($this->containerBuilder->hasDefinition(Git::class));
    }

    public function testInfoBuiltinListCanBeEmpty(): void
    {
        // given
        $config = ['actuator' => ['info' => ['builtin' => []]]];

        // when
        $this->extension->load($config, $this->containerBuilder);

        // then
        self::assertTrue($this->containerBuilder->hasDefinition(Php::class));
        self::assertTrue($this->containerBuilder->hasDefinition(Symfony::class));
        self::assertTrue($this->containerBuilder->hasDefinition(Git::class));
    }

    public function testInfoBuiltinListCanBeDefined(): void
    {
        // given
        $config = ['actuator' => ['info' => ['builtin' => ['php' => ['enabled' => true]]]]];

        // when
        $this->extension->load($config, $this->containerBuilder);

        // then
        self::assertTrue($this->containerBuilder->hasDefinition(Php::class));
    }

    public function testInfoBuiltinListCanBeDefinedWithMultipleEntries(): void
    {
        // given
        $config = ['actuator' => ['info' => ['builtin' => ['git' => ['enabled' => false]]]]];

        // when
        $this->extension->load($config, $this->containerBuilder);

        // then
        self::assertFalse($this->containerBuilder->hasDefinition(Git::class));
    }
}
