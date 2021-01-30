<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Controller;

use Akondas\ActuatorBundle\Service\Info\InfoCollector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class InfoController extends AbstractController
{
    private InfoCollector $collector;

    public function __construct(InfoCollector $collector)
    {
        $this->collector = $collector;
    }

    public function info(): JsonResponse
    {
        return new JsonResponse($this->collector->collect());
    }
}
