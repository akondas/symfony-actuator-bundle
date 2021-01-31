<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Info;

class Git implements \JsonSerializable
{
    private string $branch;
    private string $commit;

    public function __construct(string $branch, string $commit)
    {
        $this->branch = $branch;
        $this->commit = $commit;
    }

    public function branch(): string
    {
        return $this->branch;
    }

    public function commit(): string
    {
        return $this->commit;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        return [
            'branch' => $this->branch(),
            'commit' => $this->commit(),
        ];
    }
}
