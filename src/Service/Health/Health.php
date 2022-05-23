<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Health;

final class Health
{
    const UP = 'UP';
    const DOWN = 'DOWN';
    const UNKNOWN = 'UNKNOWN';

    private string $status;

    /**
     * @var array<string, mixed>
     */
    private array $details;

    /**
     * @param array<string, mixed> $details
     */
    public function __construct(string $status, array $details = [])
    {
        $this->status = $status;
        $this->details = $details;
    }

    public static function up(): self
    {
        return new Health(self::UP);
    }

    public static function down(): self
    {
        return new Health(self::DOWN);
    }

    public static function unknown(): self
    {
        return new Health(self::UNKNOWN);
    }

    /**
     * @param array<string, mixed> $details
     */
    public function setDetails(array $details): self
    {
        $this->details = $details;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return array<string, string>
     */
    public function getDetails(): array
    {
        return $this->details;
    }
}
