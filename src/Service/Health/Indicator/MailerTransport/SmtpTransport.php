<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Health\Indicator\MailerTransport;

use Akondas\ActuatorBundle\Service\Health\Health;
use Akondas\ActuatorBundle\Service\Health\HealthInterface;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Transport\TransportInterface;

class SmtpTransport implements TransportHealthIndicator
{
    public function supports(TransportInterface $transport): bool
    {
        return $transport instanceof \Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
    }

    public function health(TransportInterface $transport): HealthInterface
    {
        assert($transport instanceof \Symfony\Component\Mailer\Transport\Smtp\SmtpTransport);

        $stream = $transport->getStream();

        try {
            $stream->initialize();
        } catch (TransportException $e) {
            return Health::down('Mailer transport is down, exception: '.$e->getMessage());
        } finally {
            $stream->terminate();
        }

        return Health::up();
    }
}
