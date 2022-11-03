<?php

declare(strict_types=1);

namespace Chaos\ActuatorBundle\Tests\Controller;

use Akondas\ActuatorBundle\Service\Health\Health;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HealthControllerTest extends ControllerTestCase
{
    public function testHealthEndpointWillReturnNotFoundIfNotEnabled(): void
    {
        $this->rebootKernelWithConfig(['health' => ['enabled' => false]]);

        self::expectException(NotFoundHttpException::class);
        $this->client->catchExceptions(false);

        $this->client->request('GET', '/health');

        $this->client->getResponse();
    }

    public function testHealthEndpoint(): void
    {
        $this->client->request('GET', '/health');

        $response = $this->client->getResponse();

        self::assertEquals(200, $response->getStatusCode());
        $response = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($response);
        self::assertArrayHasKey('status', $response);
        self::assertEquals(Health::UP, $response['status']);
    }
}
