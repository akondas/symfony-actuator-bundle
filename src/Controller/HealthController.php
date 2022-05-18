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
        $response = $this->healthIndicatorStack->jsonSerialize();

        return new JsonResponse(
            $response,
            $response['status'] === Health::UP ? 200 : 503,
        );
    }
}
