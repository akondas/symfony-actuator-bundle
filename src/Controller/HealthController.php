<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Controller;

use Akondas\ActuatorBundle\Service\Health\Health;
use Akondas\ActuatorBundle\Service\Health\HealthIndicatorStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class HealthController extends AbstractController
{
    private HealthIndicatorStack $healthIndicatorStack;

    public function __construct(HealthIndicatorStack $healthIndicatorStack)
    {
        $this->healthIndicatorStack = $healthIndicatorStack;
    }

    public function health(): JsonResponse
    {
        $healthStack = $this->healthIndicatorStack->check();

        return new JsonResponse(
            $healthStack,
            $healthStack->isUp() ? 200 : 503,
        );
    }
}
