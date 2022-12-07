<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Health\Indicator\MailerTransport;

use Akondas\ActuatorBundle\Service\Health\Health;
use Akondas\ActuatorBundle\Service\Health\HealthInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;

class NullTransport implements TransportHealthIndicator
{
    public function supports(TransportInterface $transport): bool
    {
        return $transport instanceof \Symfony\Component\Mailer\Transport\NullTransport;
    }

    public function health(TransportInterface $transport): HealthInterface
    {
        return Health::up();
    }
}
