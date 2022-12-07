<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Health\Indicator\MailerTransport;

use Akondas\ActuatorBundle\Service\Health\HealthInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;

interface TransportHealthIndicator
{
    public function supports(TransportInterface $transport): bool;

    public function health(TransportInterface $transport): HealthInterface;
}
