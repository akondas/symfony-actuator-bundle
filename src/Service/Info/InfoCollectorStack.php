<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Info;

use Akondas\ActuatorBundle\Service\Info\Collector\Collector;

class InfoCollectorStack
{
    /**
     * @var Collector[]
     */
    private iterable $collectors;

    /**
     * @param Collector[] $collectors
     */
    public function __construct(iterable $collectors)
    {
        $this->collectors = $collectors;
    }

    public function collect(): InfoStack
    {
        $infos = [];
        foreach ($this->collectors as $collector) {
            $infos[] = $collector->collect();
        }

        return new InfoStack($infos);
    }
}
