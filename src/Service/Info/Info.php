<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Info;

class Info implements \JsonSerializable
{
    private Php $env;
    private ?Git $git;

    public function __construct(Php $env, ?Git $git = null)
    {
        $this->env = $env;
        $this->git = $git;
    }

    public function env(): Php
    {
        return $this->env;
    }

    public function git(): ?Git
    {
        return $this->git;
    }

    public function jsonSerialize(): array
    {
        $data = [
            'env' => $this->env(),
        ];
        if ($this->git !== null) {
            $data['git'] = $this->git();
        }

        return $data;
    }
}
