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
    private bool $opCacheEnabled;

    public function __construct(string $version, int $architecture, string $intlLocale, string $timezone, bool $xdebugEnabled, bool $apcuEnabled, bool $OPcacheEnabled)
    {
        $this->version = $version;
        $this->architecture = $architecture;
        $this->intlLocale = $intlLocale;
        $this->timezone = $timezone;
        $this->xdebugEnabled = $xdebugEnabled;
        $this->apcuEnabled = $apcuEnabled;
        $this->opCacheEnabled = $OPcacheEnabled;
    }

    public function version(): string
    {
        return $this->version;
    }

    public function architecture(): int
    {
        return $this->architecture;
    }

    public function intlLocale(): string
    {
        return $this->intlLocale;
    }

    public function timezone(): string
    {
        return $this->timezone;
    }

    public function xdebugEnabled(): bool
    {
        return $this->xdebugEnabled;
    }

    public function apcuEnabled(): bool
    {
        return $this->apcuEnabled;
    }

    public function opCacheEnabled(): bool
    {
        return $this->opCacheEnabled;
    }

    public function jsonSerialize(): array
    {
        return [
            'version' => $this->version(),
            'architecture' => $this->architecture(),
            'intlLocale' => $this->intlLocale(),
            'timezone' => $this->timezone(),
            'xdebugEnabled' => $this->xdebugEnabled(),
            'apcuEnabled' => $this->apcuEnabled(),
            'opCacheEnabled' => $this->opCacheEnabled(),
        ];
    }
}
