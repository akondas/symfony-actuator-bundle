<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Health;

final class HealthStack implements HealthInterface
{
    /**
     * @var array<string, HealthInterface>
     */
    private array $healthList;

    /**
     * @param array<string, HealthInterface> $healthList
     */
    public function __construct(array $healthList = [])
    {
        $this->healthList = $healthList;
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

    public function getStatus(): string
    {
        $status = Health::UP;
        foreach ($this->healthList as $health) {
            $currentKey = array_search($status, $this->defaultOrder(), true);
            $key = array_search($health->getStatus(), $this->defaultOrder(), true);

            if ($key === false) {
                $status = Health::UNKNOWN;
            }
            if ($currentKey > $key) {
                $status = $this->defaultOrder()[$key];
            }
        }

        return $status;
    }

    public function isUp(): bool
    {
        return Health::UP === $this->getStatus();
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return array_merge(['status' => $this->getStatus()], $this->healthList);
    }
}
