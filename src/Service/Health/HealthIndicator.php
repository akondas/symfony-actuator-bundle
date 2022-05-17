<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Health;

interface HealthIndicator
{
    public function name(): string;

    public function health(): Health;
}
