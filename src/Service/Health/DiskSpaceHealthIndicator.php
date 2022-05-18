<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Health;

class DiskSpaceHealthIndicator implements HealthIndicator
{
    private string $projectDir;
    private int $threshold;

    public function __construct(string $projectDir, int $threshold)
    {
        $this->projectDir = $projectDir;
        $this->threshold = $threshold;
    }

    public function name(): string
    {
        return 'diskSpace';
    }

    public function health(): Health
    {
        $space = @disk_free_space($this->projectDir);

        if ($space === false) {
            return Health::unknown();
        }

        if ($space < $this->threshold) {
            return Health::down()->setDetails(['disk_free_space' => $space, 'threshold' => $this->threshold]);
        }

        return Health::up()->setDetails(['disk_free_space' => $space, 'threshold' => $this->threshold]);
    }
}
