<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Controller;

use Akondas\ActuatorBundle\Service\Info\InfoCollectorStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InfoController extends AbstractController
{
    private InfoCollectorStack $collector;

    public function __construct(InfoCollectorStack $collector)
    {
        $this->collector = $collector;
    }

    public function info(): JsonResponse
    {
        if ($this->getParameter('actuator.info.enabled') === false) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse($this->collector->collect());
    }
}
