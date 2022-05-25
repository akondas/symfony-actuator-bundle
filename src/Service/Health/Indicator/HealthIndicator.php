<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Health\Indicator;

use Akondas\ActuatorBundle\Service\Health\HealthInterface;

interface HealthIndicator
{
    public function name(): string;

    public function health(): HealthInterface;
}
