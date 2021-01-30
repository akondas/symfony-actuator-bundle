<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Info;

class Php implements \JsonSerializable
{
    private string $version;
    private int $architecture;
    private string $intlLocale;
    private string $timezone;
    private bool $xdebugEnabled;
    private bool $apcuEnabled;
    private bool $OPcacheEnabled;

    public function __construct(string $version, int $architecture, string $intlLocale, string $timezone, bool $xdebugEnabled, bool $apcuEnabled, bool $OPcacheEnabled)
    {
        $this->version = $version;
        $this->architecture = $architecture;
        $this->intlLocale = $intlLocale;
        $this->timezone = $timezone;
        $this->xdebugEnabled = $xdebugEnabled;
        $this->apcuEnabled = $apcuEnabled;
        $this->OPcacheEnabled = $OPcacheEnabled;
    }
}
