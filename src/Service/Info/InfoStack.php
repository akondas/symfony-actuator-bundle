<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Info;

class InfoStack implements \JsonSerializable
{
    /**
     * @var iterable<Info>
     */
    private iterable $infos;

    /**
     * @param iterable<Info> $infos
     */
    public function __construct(iterable $infos)
    {
        $this->infos = $infos;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        $data = [];
        foreach ($this->infos as $info) {
            if ($info->isEmpty()) {
                continue;
            }

            $data[$info->name()] = $info->jsonSerialize();
        }

        return $data;
    }
}
