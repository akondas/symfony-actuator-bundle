<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Info;

final class Info implements \JsonSerializable
{
    private string $name;
    /**
     * @var array<mixed>
     */
    private array $informations;

    /**
     * @param array<mixed> $informations
     */
    public function __construct(string $name, array $informations)
    {
        $this->name = $name;
        $this->informations = $informations;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function isEmpty(): bool
    {
        return count($this->informations) === 0;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->informations;
    }
}
