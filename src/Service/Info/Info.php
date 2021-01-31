<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Info;

class Info implements \JsonSerializable
{
    private Php $php;
    private Symfony $symfony;
    private ?Git $git;

    public function __construct(Php $php, Symfony $symfony, ?Git $git = null)
    {
        $this->php = $php;
        $this->symfony = $symfony;
        $this->git = $git;
    }

    public function php(): Php
    {
        return $this->php;
    }

    public function symfony(): Symfony
    {
        return $this->symfony;
    }

    public function git(): ?Git
    {
        return $this->git;
    }

    public function jsonSerialize(): array
    {
        $data = [
            'php' => $this->php(),
            'symfony' => $this->symfony(),
        ];
        if ($this->git !== null) {
            $data['git'] = $this->git();
        }

        return $data;
    }
}
