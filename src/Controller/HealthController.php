<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class HealthController extends AbstractController
{
    public function health(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'up',
        ]);
    }
}
