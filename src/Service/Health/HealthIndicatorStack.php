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

    /**
     * @return array<int, string>
     */
    private function defaultOrder(): array
    {
        return [
            Health::UNKNOWN,
            Health::DOWN,
            Health::UP,
        ];
    }

    public function check(): HealthStack
    {
        $status = Health::UP;
        $details = [];
        foreach ($this->indicators as $indicator) {
            $health = $indicator->health();
            $currentKey = array_search($status, $this->defaultOrder(), true);
            $key = array_search($health->getStatus(), $this->defaultOrder(), true);

            if ($key === false) {
                $status = Health::UNKNOWN;
            }
            if ($currentKey > $key) {
                $status = $this->defaultOrder()[$key];
            }

            $details[$indicator->name()] = [
                'status' => $this->defaultOrder()[$key],
                'details' => $health->getDetails(),
            ];
        }

        return new HealthStack($status, $details);
    }
}
