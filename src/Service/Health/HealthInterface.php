<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Health;

interface HealthInterface extends \JsonSerializable
{
    public function getStatus(): string;
    public function isUp(): bool;
}
