<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Health;

final class Health
{
    private bool $status;

    /**
     * @var array<string, mixed>
     */
    private array $details;

    /**
     * @param array<string, mixed> $details
     */
    public function __construct(bool $status, array $details = [])
    {
        $this->status = $status;
        $this->details = $details;
    }

    public static function up(): self
    {
        return new Health(true);
    }

    public static function down(): self
    {
        return new Health(false);
    }

    /**
     * @param array<string, mixed> $details
     */
    public function setDetails(array $details): self
    {
        $this->details = $details;

        return $this;
    }

    public function getStatus(): bool
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
