<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Info\Collector;

use Akondas\ActuatorBundle\Service\Info\Info;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

class Symfony implements Collector
{
    private KernelInterface $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function collect(): Info
    {
        return new Info('symfony', [
            'version' => Kernel::VERSION,
            'lts' => 4 === Kernel::MINOR_VERSION, // @phpstan-ignore-line
            'environment' => $this->kernel->getEnvironment(),
            'endOfMaintenance' => \DateTimeImmutable::createFromFormat('d/m/Y', '01/'.Kernel::END_OF_MAINTENANCE),
            'endOfLife' => \DateTimeImmutable::createFromFormat('d/m/Y', '01/'.Kernel::END_OF_LIFE),
            'bundles' => array_map(function (BundleInterface $bundle): string {
                return get_class($bundle);
            }, $this->kernel->getBundles()),
        ]);
    }
}
