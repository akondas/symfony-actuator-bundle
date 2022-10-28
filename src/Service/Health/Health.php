<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Health;

final class Health implements HealthInterface
{
    public const UP = 'UP';
    public const DOWN = 'DOWN';
    public const UNKNOWN = 'UNKNOWN';

    private string $status;

    /**
     * @var array<string, mixed>
     */
    private array $details;

    private ?string $error;

    /**
     * @param array<string, mixed> $details
     */
    public function __construct(string $status, array $details = [], ?string $error = null)
    {
        $this->status = $status;
        $this->details = $details;
        $this->error = $error;
    }

    /**
     * @param array<string, mixed> $details
     */
    public static function up(array $details = []): self
    {
        return new Health(self::UP, $details);
    }

    public static function down(?string $error = null): self
    {
        return new Health(self::DOWN, [], $error);
    }

    public static function unknown(?string $error = null): self
    {
        return new Health(self::UNKNOWN, [], $error);
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function isUp(): bool
    {
        return Health::UP === $this->status;
    }

    /**
     * @param array<string, mixed> $details
     */
    public function setDetails(array $details): self
    {
        $this->details = $details;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    public function setError(?string $error): self
    {
        $this->error = $error;

        return $this;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * @return array<string, string|array<mixed>>
     */
    public function jsonSerialize(): array
    {
        $serialized = ['status' => $this->status];

        if (count($this->details) > 0) {
            $serialized['details'] = $this->details;
        }

        if (null !== $this->error) {
            $serialized['error'] = $this->error;
        }

        return $serialized;
    }
}
