<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Info\Collector;

use Akondas\ActuatorBundle\Service\Info\Info;

class Php implements Collector
{
    public function collect(): Info
    {
        return new Info('php', [
            'version' => PHP_VERSION,
            'architecture' => PHP_INT_SIZE * 8,
            'intlLocale' => class_exists(\Locale::class, false) && \Locale::getDefault() ? \Locale::getDefault() : 'n/a',
            'timezone' => date_default_timezone_get(),
            'xdebugEnabled' => extension_loaded('xdebug'),
            'apcuEnabled' => extension_loaded('apcu') && filter_var(ini_get('apc.enabled'), FILTER_VALIDATE_BOOLEAN),
            'opCacheEnabled' => extension_loaded('Zend OPcache') && filter_var(ini_get('opcache.enable'), FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
