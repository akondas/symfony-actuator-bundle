<?php

declare(strict_types=1);

namespace Chaos\ActuatorBundle\Tests;

use Akondas\ActuatorBundle\ActuatorBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * @var array<string, mixed>
     */
    private array $actuatorConfig;

    /**
     * @param array<string, mixed> $actuatorConfig
     */
    public function __construct(string $environment, bool $debug, array $actuatorConfig = [])
    {
        parent::__construct($environment, $debug);

        $this->actuatorConfig = $actuatorConfig;
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new ActuatorBundle(),
        ];
    }

    private function configureContainer(ContainerConfigurator $containerConfigurator, LoaderInterface $loader): void
    {
        $loader->load($this->getProjectDir().'/src/Resources/config/services_test.yaml');
        $containerConfigurator->extension('actuator', $this->actuatorConfig);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import($this->getProjectDir().'/src/Resources/config/routing.yaml');
    }
}
