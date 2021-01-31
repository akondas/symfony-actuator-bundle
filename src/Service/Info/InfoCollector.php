<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Info;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

class InfoCollector
{
    private KernelInterface $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function collect(): Info
    {
        return new Info(
            $this->collectPhpInfo(),
            $this->collectSymfonyInfo(),
            $this->collectGitInfo()
        );
    }

    private function collectPhpInfo(): Php
    {
        return new Php(
            PHP_VERSION,
            PHP_INT_SIZE * 8,
            class_exists(\Locale::class, false) && \Locale::getDefault() ? \Locale::getDefault() : 'n/a',
            date_default_timezone_get(),
            extension_loaded('xdebug'),
            extension_loaded('apcu') && filter_var(ini_get('apc.enabled'), FILTER_VALIDATE_BOOLEAN),
            extension_loaded('Zend OPcache') && filter_var(ini_get('opcache.enable'), FILTER_VALIDATE_BOOLEAN)
        );
    }

    private function collectSymfonyInfo(): Symfony
    {
        return new Symfony(
            Kernel::VERSION,
            4 === Kernel::MINOR_VERSION,
            $this->kernel->getEnvironment(),
            \DateTimeImmutable::createFromFormat('d/m/Y', '01/'.Kernel::END_OF_MAINTENANCE),
            \DateTimeImmutable::createFromFormat('d/m/Y', '01/'.Kernel::END_OF_LIFE),
            array_map(function (Bundle $bundle): string {return get_class($bundle); }, $this->kernel->getBundles())
        );
    }

    private function collectGitInfo(): ?Git
    {
        return null;
    }
}
