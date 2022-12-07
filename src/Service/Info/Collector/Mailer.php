<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Info\Collector;

use Akondas\ActuatorBundle\Service\Info\Info;
use Symfony\Component\Mailer\Transport\TransportInterface;

class Mailer implements Collector
{
    /**
     * @var array<string, TransportInterface>
     */
    private array $transports;

    /**
     * @param array<string, TransportInterface> $transports
     */
    public function __construct(array $transports)
    {
        $this->transports = $transports;
    }

    public function collect(): Info
    {
        $transportInfo = [];

        foreach ($this->transports as $name => $transport) {
            $transportInfo[$name] = [
                'class' => get_class($transport),
                'dsn' => $transport->__toString(),
            ];
        }

        return new Info('mailer', [
            'transport' => $transportInfo,
        ]);
    }
}
