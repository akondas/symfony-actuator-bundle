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

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new ActuatorBundle(),
        ];
    }

    private function configureContainer(ContainerConfigurator $containerConfigurator, LoaderInterface $loader): void
    {
        $loader->load($this->getProjectDir().'/src/Resources/config/services_test.yml');
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import($this->getProjectDir().'/src/Resources/config/routing.yml');
    }
}
