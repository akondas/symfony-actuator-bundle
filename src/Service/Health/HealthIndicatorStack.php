<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Health;

use Akondas\ActuatorBundle\Service\Health\Indicator\HealthIndicator;

class HealthIndicatorStack
{
    /**
     * @var iterable<HealthIndicator>
     */
    private iterable $indicators;

    /**
     * @param iterable<HealthIndicator> $indicators
     */
    public function __construct(iterable $indicators)
    {
        $this->indicators = $indicators;
    }

    public function check(): HealthStack
    {
        $details = [];

        foreach ($this->indicators as $indicator) {
            $details[$indicator->name()] = $indicator->health();
        }

        return new HealthStack($details);
    }
}
