<?php

declare(strict_types=1);

namespace Chaos\ActuatorBundle\Tests\DependencyInjection;

use Akondas\ActuatorBundle\DependencyInjection\ActuatorExtension;
use Akondas\ActuatorBundle\Service\Health\Indicator\Database;
use Akondas\ActuatorBundle\Service\Health\Indicator\DiskSpace;
use Akondas\ActuatorBundle\Service\Health\Indicator\Mailer;
use Akondas\ActuatorBundle\Service\Info\Collector\Database as DatabaseInfo;
use Akondas\ActuatorBundle\Service\Info\Collector\Git;
use Akondas\ActuatorBundle\Service\Info\Collector\Mailer as MailerInfo;
use Akondas\ActuatorBundle\Service\Info\Collector\Php;
use Akondas\ActuatorBundle\Service\Info\Collector\Symfony;
use Composer\Autoload\ClassLoader;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ActuatorExtensionTest extends TestCase
{
    private ActuatorExtension $extension;
    private ContainerBuilder $containerBuilder;
    private ?ClassLoader $classLoader = null;
    private vfsStreamDirectory $root;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension = new ActuatorExtension();
        $this->containerBuilder = new ContainerBuilder();

        $this->root = vfsStream::setup(uniqid());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (null !== $this->classLoader) {
            $this->classLoader->unregister();
        }
    }

    protected function registerClassLoader(): void
    {
        if (null !== $this->classLoader) {
            throw new \RuntimeException('Classloader already registered');
        }

        $this->classLoader = new ClassLoader($this->root->url());
        $this->classLoader->register(true);
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

    public function testHealthDatabaseDefaultDisabledWhenNoDependency(): void
    {
        // given
        $config = ['actuator' => ['health' => ['builtin' => ['database' => ['enabled' => true]]]]];

        // when
        $this->extension->load($config, $this->containerBuilder);

        // then
        self::assertFalse($this->containerBuilder->hasDefinition(Database::class));
    }

    public function testHealthDatabaseEnabledWhenEnabledAndDependency(): void
    {
        // given
        $this->registerClassLoader();
        $installed = '<?php return [\'root\' => [ \'name\' => \'project\' ], \'versions\' => [ \'doctrine/doctrine-bundle\' => [ \'dev_requirement\' => null, ], ], ];';

        vfsStream::create([
            'composer' => [
                'installed.php' => $installed,
            ],
        ], $this->root);

        $config = ['actuator' => ['health' => ['builtin' => ['database' => ['enabled' => true]]]]];

        // when
        $this->extension->load($config, $this->containerBuilder);

        // then
        self::assertTrue($this->containerBuilder->hasDefinition(Database::class));
    }

    public function testHealthDatabaseWillContainReferenceToConnections(): void
    {
        // given
        $this->registerClassLoader();
        $installed = '<?php return [\'root\' => [ \'name\' => \'project\' ], \'versions\' => [ \'doctrine/doctrine-bundle\' => [ \'dev_requirement\' => null, ], ], ];';

        vfsStream::create([
            'composer' => [
                'installed.php' => $installed,
            ],
        ], $this->root);
        $config = ['actuator' => ['health' => ['builtin' => ['database' => ['enabled' => true, 'connections' => ['name1' => ['service' => 'conn1'], 'name2' => ['service' => 'conn2', 'check_sql' => null]]]]]]];

        // when
        $this->extension->load($config, $this->containerBuilder);

        // then
        self::assertTrue($this->containerBuilder->hasDefinition(Database::class));
        $definition = $this->containerBuilder->getDefinition(Database::class);
        self::assertCount(1, $definition->getArguments());
        $argument = $definition->getArgument(0);
        self::assertIsArray($argument);
        self::assertCount(2, $argument);
        self::assertArrayHasKey('name1', $argument);
        self::assertArrayHasKey('name2', $argument);

        $name1 = $argument['name1'];
        $name2 = $argument['name2'];

        self::assertIsArray($name1);
        self::assertIsArray($name2);

        self::assertArrayHasKey('connection', $name1);
        self::assertArrayHasKey('connection', $name2);

        $reference1 = $name1['connection'];
        $reference2 = $name2['connection'];

        self::assertInstanceOf(Reference::class, $reference1);
        self::assertInstanceOf(Reference::class, $reference2);

        self::assertEquals('conn1', (string) $reference1);
        self::assertEquals('conn2', (string) $reference2);

        self::assertArrayHasKey('sql', $name1);
        self::assertArrayHasKey('sql', $name2);

        self::assertEquals('SELECT 1', $name1['sql']);
        self::assertNull($name2['sql']);
    }

    public function testHealthMailerDefaultDisabledWhenNoDependency(): void
    {
        // given
        $config = ['actuator' => ['health' => ['builtin' => ['mailer' => ['enabled' => true]]]]];

        // when
        $this->extension->load($config, $this->containerBuilder);

        // then
        self::assertFalse($this->containerBuilder->hasDefinition(Mailer::class));
    }

    public function testHealthMailerEnabledWhenEnabledAndDependency(): void
    {
        // given
        $this->registerClassLoader();
        $installed = '<?php return [\'root\' => [ \'name\' => \'project\' ], \'versions\' => [ \'symfony/mailer\' => [ \'dev_requirement\' => null, ], ], ];';

        vfsStream::create([
            'composer' => [
                'installed.php' => $installed,
            ],
        ], $this->root);

        $config = ['actuator' => ['health' => ['builtin' => ['mailer' => ['enabled' => true]]]]];

        // when
        $this->extension->load($config, $this->containerBuilder);

        // then
        self::assertTrue($this->containerBuilder->hasDefinition(Mailer::class));
    }

    public function testHealthMailerWillContainReferenceToTransports(): void
    {
        // given
        $this->registerClassLoader();
        $installed = '<?php return [\'root\' => [ \'name\' => \'project\' ], \'versions\' => [ \'symfony/mailer\' => [ \'dev_requirement\' => null, ], ], ];';

        vfsStream::create([
            'composer' => [
                'installed.php' => $installed,
            ],
        ], $this->root);
        $config = ['actuator' => ['health' => ['builtin' => ['mailer' => ['enabled' => true, 'transports' => ['name1' => ['service' => 'transport1'], 'name2' => ['service' => 'transport2']]]]]]];

        // when
        $this->extension->load($config, $this->containerBuilder);

        // then
        self::assertTrue($this->containerBuilder->hasDefinition(Mailer::class));
        $definition = $this->containerBuilder->getDefinition(Mailer::class);
        self::assertCount(2, $definition->getArguments());
        $argument = $definition->getArgument('$transports');
        self::assertIsArray($argument);
        self::assertCount(2, $argument);
        self::assertArrayHasKey('name1', $argument);
        self::assertArrayHasKey('name2', $argument);

        $reference1 = $argument['name1'];
        $reference2 = $argument['name2'];
        self::assertInstanceOf(Reference::class, $reference1);
        self::assertInstanceOf(Reference::class, $reference2);

        self::assertEquals('transport1', (string) $reference1);
        self::assertEquals('transport2', (string) $reference2);
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

    public function testInfoDatabaseEnabledButNoDependencyDoesNotLoad(): void
    {
        // given
        $config = ['actuator' => ['info' => ['builtin' => ['database' => ['enabled' => true]]]]];

        // when
        $this->extension->load($config, $this->containerBuilder);

        // then
        self::assertFalse($this->containerBuilder->hasDefinition(DatabaseInfo::class));
    }

    public function testInfoDatabaseEnabledWithDependencyDoesLoad(): void
    {
        // given
        $this->registerClassLoader();
        $installed = '<?php return [\'root\' => [ \'name\' => \'project\' ], \'versions\' => [ \'doctrine/doctrine-bundle\' => [ \'dev_requirement\' => null, ], ], ];';

        vfsStream::create([
            'composer' => [
                'installed.php' => $installed,
            ],
        ], $this->root);
        $config = ['actuator' => ['info' => ['builtin' => ['database' => ['enabled' => true]]]]];

        // when
        $this->extension->load($config, $this->containerBuilder);

        // then
        self::assertTrue($this->containerBuilder->hasDefinition(DatabaseInfo::class));
    }

    public function testInfoDatabaseWillContainReferenceToConnection(): void
    {
        // given
        $this->registerClassLoader();
        $installed = '<?php return [\'root\' => [ \'name\' => \'project\' ], \'versions\' => [ \'doctrine/doctrine-bundle\' => [ \'dev_requirement\' => null, ], ], ];';

        vfsStream::create([
            'composer' => [
                'installed.php' => $installed,
            ],
        ], $this->root);
        $config = ['actuator' => ['info' => ['builtin' => ['database' => ['enabled' => true, 'connections' => ['name1' => 'conn1', 'name2' => 'conn2']]]]]];

        // when
        $this->extension->load($config, $this->containerBuilder);

        // then
        self::assertTrue($this->containerBuilder->hasDefinition(DatabaseInfo::class));
        $definition = $this->containerBuilder->getDefinition(DatabaseInfo::class);
        self::assertCount(1, $definition->getArguments());
        $argument = $definition->getArgument(0);
        self::assertIsArray($argument);
        self::assertCount(2, $argument);
        self::assertArrayHasKey('name1', $argument);
        self::assertArrayHasKey('name2', $argument);

        $reference1 = $argument['name1'];
        $reference2 = $argument['name2'];
        self::assertInstanceOf(Reference::class, $reference1);
        self::assertInstanceOf(Reference::class, $reference2);

        self::assertEquals('conn1', (string) $reference1);
        self::assertEquals('conn2', (string) $reference2);
    }

    public function testInfoMailerEnabledButNoDependencyDoesNotLoad(): void
    {
        // given
        $config = ['actuator' => ['info' => ['builtin' => ['mailer' => ['enabled' => true]]]]];

        // when
        $this->extension->load($config, $this->containerBuilder);

        // then
        self::assertFalse($this->containerBuilder->hasDefinition(MailerInfo::class));
    }

    public function testInfoMailerEnabledWithDependencyDoesLoad(): void
    {
        // given
        $this->registerClassLoader();
        $installed = '<?php return [\'root\' => [ \'name\' => \'project\' ], \'versions\' => [ \'symfony/mailer\' => [ \'dev_requirement\' => null, ], ], ];';

        vfsStream::create([
            'composer' => [
                'installed.php' => $installed,
            ],
        ], $this->root);
        $config = ['actuator' => ['info' => ['builtin' => ['mailer' => ['enabled' => true]]]]];

        // when
        $this->extension->load($config, $this->containerBuilder);

        // then
        self::assertTrue($this->containerBuilder->hasDefinition(MailerInfo::class));
    }

    public function testInfoMailerWillContainReferenceToTransport(): void
    {
        // given
        $this->registerClassLoader();
        $installed = '<?php return [\'root\' => [ \'name\' => \'project\' ], \'versions\' => [ \'symfony/mailer\' => [ \'dev_requirement\' => null, ], ], ];';

        vfsStream::create([
            'composer' => [
                'installed.php' => $installed,
            ],
        ], $this->root);
        $config = ['actuator' => ['info' => ['builtin' => ['mailer' => ['enabled' => true, 'transports' => ['name1' => 'transport1', 'name2' => 'transport2']]]]]];

        // when
        $this->extension->load($config, $this->containerBuilder);

        // then
        self::assertTrue($this->containerBuilder->hasDefinition(MailerInfo::class));
        $definition = $this->containerBuilder->getDefinition(MailerInfo::class);
        self::assertCount(1, $definition->getArguments());
        $argument = $definition->getArgument(0);
        self::assertIsArray($argument);
        self::assertCount(2, $argument);
        self::assertArrayHasKey('name1', $argument);
        self::assertArrayHasKey('name2', $argument);

        $reference1 = $argument['name1'];
        $reference2 = $argument['name2'];
        self::assertInstanceOf(Reference::class, $reference1);
        self::assertInstanceOf(Reference::class, $reference2);

        self::assertEquals('transport1', (string) $reference1);
        self::assertEquals('transport2', (string) $reference2);
    }
}
