<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Info;

class Symfony implements \JsonSerializable
{
    private string $version;
    private bool $lts;
    private string $environment;
    private \DateTimeImmutable $endOfMaintenance;
    private \DateTimeImmutable $endOfLife;

    /**
     * @var string[]
     */
    private array $bundles;

    /**
     * @param string[] $bundles
     */
    public function __construct(string $version, bool $lts, string $environment, \DateTimeImmutable $endOfMaintenance, \DateTimeImmutable $endOfLife, array $bundles)
    {
        $this->version = $version;
        $this->lts = $lts;
        $this->environment = $environment;
        $this->endOfMaintenance = $endOfMaintenance;
        $this->endOfLife = $endOfLife;
        $this->bundles = $bundles;
    }

    public function version(): string
    {
        return $this->version;
    }

    public function lts(): bool
    {
        return $this->lts;
    }

    public function environment(): string
    {
        return $this->environment;
    }

    public function endOfMaintenance(): \DateTimeImmutable
    {
        return $this->endOfMaintenance;
    }

    public function endOfLife(): \DateTimeImmutable
    {
        return $this->endOfLife;
    }

    /**
     * @return string[]
     */
    public function bundles(): array
    {
        return $this->bundles;
    }

    public function jsonSerialize(): array
    {
        return [
            'version' => $this->version(),
            'lts' => $this->lts(),
            'environment' => $this->environment(),
            'endOfMaintenance' => $this->endOfMaintenance->format('F Y'),
            'endOfLife' => $this->endOfLife()->format('F Y'),
            'bundles' => $this->bundles(),
        ];
    }
}
