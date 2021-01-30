<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Info;

class Git implements \JsonSerializable
{
    private string $branch;
    private string $commitHash;
    private \DateTimeImmutable $commitTime;

    public function __construct(string $branch, string $commitHash, \DateTimeImmutable $commitTime)
    {
        $this->branch = $branch;
        $this->commitHash = $commitHash;
        $this->commitTime = $commitTime;
    }

    public function branch(): string
    {
        return $this->branch;
    }

    public function commitHash(): string
    {
        return $this->commitHash;
    }

    public function commitTime(): \DateTimeImmutable
    {
        return $this->commitTime;
    }

    public function jsonSerialize(): array
    {
        return [
            'branch' => $this->branch(),
            'commit' => [
                'id' => $this->commitHash(),
                'time' => $this->commitTime()->format(DATE_ISO8601),
            ],
        ];
    }
}
