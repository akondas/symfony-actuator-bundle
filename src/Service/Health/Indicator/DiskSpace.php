<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Health\Indicator;

use Akondas\ActuatorBundle\Service\Health\Health;
use Akondas\ActuatorBundle\Service\Health\HealthInterface;

class DiskSpace implements HealthIndicator
{
    private string $path;
    private int $threshold;

    public function __construct(string $path, int $threshold)
    {
        $this->path = $path;
        $this->threshold = $threshold;
    }

    public function name(): string
    {
        return 'diskSpace';
    }

    public function health(): HealthInterface
    {
        $space = @disk_free_space($this->path);

        if ($space === false) {
            return Health::unknown();
        }

        if ($space < $this->threshold) {
            return Health::down()->setDetails(['disk_free_space' => $space, 'threshold' => $this->threshold, 'path' => $this->path]);
        }

        return Health::up()->setDetails(['disk_free_space' => $space, 'threshold' => $this->threshold, 'path' => $this->path]);
    }
}
