<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Health;

class DiskSpaceHealthIndicator implements HealthIndicator
{
    private string $cacheDir;
    private int $threshold;

    public function __construct(string $cacheDir, int $threshold)
    {
        $this->cacheDir = $cacheDir;
        $this->threshold = $threshold;
    }

    public function name(): string
    {
        return 'diskSpace';
    }

    public function health(): Health
    {
        $space = disk_free_space($this->cacheDir);

        if ($space === false) {
            return Health::down()->setDetails(['disk_free_space' => 'unkown', 'threshold' => $this->threshold]);
        }

        if ($space < $this->threshold) {
            return Health::down()->setDetails(['disk_free_space' => $space, 'threshold' => $this->threshold]);
        }

        return Health::up()->setDetails(['disk_free_space' => $space, 'threshold' => $this->threshold]);
    }
}
