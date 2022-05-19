<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Health;

final class HealthStack implements \JsonSerializable
{
    private string $status;

    /**
     * @var array<string, array{'status': string, 'details': array<string, string|int|float|bool|null>|null}>
     */
    private array $details;

    /**
     * @param array<string, array{'status': string, 'details': array<string, string|int|float|bool|null>|null}> $details
     */
    public function __construct(string $status, array $details = [])
    {
        $this->status = $status;
        $this->details = $details;
    }

    public function isUp(): bool
    {
        return Health::UP === $this->status;
    }

    /**
     * @return array{'status': string, 'details': array<string, array{'status': string, 'details': array<string, string|int|float|bool|null>|null}>}
     */
    public function jsonSerialize(): array
    {
        return [
            'status' => $this->status,
            'details' => $this->details,
        ];
    }
}
