<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Health\Indicator;

use Akondas\ActuatorBundle\Service\Health\Health;
use Akondas\ActuatorBundle\Service\Health\HealthInterface;
use Akondas\ActuatorBundle\Service\Health\HealthStack;
use Akondas\ActuatorBundle\Service\Health\Indicator\MailerTransport\TransportHealthIndicator;
use Symfony\Component\Mailer\Transport\TransportInterface;

class Mailer implements HealthIndicator
{
    /**
     * @var iterable<TransportHealthIndicator>
     */
    private iterable $mailerTransportHealthIndicators = [];

    /**
     * @var array<string, TransportInterface>
     */
    private array $transports;

    /**
     * @param array<string, TransportInterface>  $transports
     * @param iterable<TransportHealthIndicator> $mailerTransportHealthIndicators
     */
    public function __construct(array $transports, iterable $mailerTransportHealthIndicators)
    {
        $this->transports = $transports;
        $this->mailerTransportHealthIndicators = $mailerTransportHealthIndicators;
    }

    public function name(): string
    {
        return 'mailer';
    }

    public function health(): HealthInterface
    {
        $healthList = [];
        foreach ($this->transports as $name => $transport) {
            $transportChecked = false;
            foreach ($this->mailerTransportHealthIndicators as $transportHealthIndicator) {
                if ($transportChecked) {
                    continue;
                }
                if ($transportHealthIndicator->supports($transport)) {
                    $healthList[$name] = $transportHealthIndicator->health($transport);
                    $transportChecked = true;
                }
            }

            if (!$transportChecked) {
                $healthList[$name] = Health::unknown(
                    sprintf('No suitable transport health indicator available for class "%s"', get_class($transport))
                );
            }
        }

        if (count($healthList) === 0) {
            return Health::unknown('No transports checked');
        }
        if (count($healthList) === 1) {
            return current($healthList);
        }

        return new HealthStack($healthList);
    }
}
