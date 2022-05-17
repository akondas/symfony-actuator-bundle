<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Controller;

use Akondas\ActuatorBundle\Service\Health\HealthIndicator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class HealthController extends AbstractController
{
    public const HEALTHY_UP = 'UP';
    public const HEALTHY_DOWN = 'DOWN';

    /**
     * @var HealthIndicator[]
     */
    private iterable $handlers;

    /**
     * @param HealthIndicator[] $handlers
     */
    public function __construct(iterable $handlers)
    {
        $this->handlers = $handlers;
    }

    public function health(): JsonResponse
    {
        $healthy = true;
        $details = [];
        foreach ($this->handlers as $handler) {
            $handlerHealth = $handler->health();
            $status = $handlerHealth->getStatus();
            if ($status === false) {
                $healthy = false;
            }

            $details[$handler->name()] = ['status' => $status ? self::HEALTHY_UP : self::HEALTHY_DOWN, 'details' => $handlerHealth->getDetails()];
        }

        $response = [
            'status' => $healthy ? self::HEALTHY_UP : self::HEALTHY_DOWN,
        ];

        if (count($details) !== 0) {
            $response['details'] = $details;
        }

        return new JsonResponse($response, $healthy ? 200 : 503);
    }
}
