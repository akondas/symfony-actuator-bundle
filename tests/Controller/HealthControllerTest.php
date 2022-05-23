<?php

declare(strict_types=1);

namespace Chaos\ActuatorBundle\Tests\Controller;

use Akondas\ActuatorBundle\Service\Health\Health;

class HealthControllerTest extends ControllerTestCase
{
    public function testHealthEndpoint(): void
    {
        $this->client->catchExceptions(true);
        $this->client->request('GET', '/health');

        $response = $this->client->getResponse();

        self::assertEquals(200, $response->getStatusCode());
        $response = json_decode((string) $response->getContent(), true);
        self::assertArrayHasKey('status', $response);
        self::assertEquals(Health::UP, $response['status']);
        self::assertArrayHasKey('details', $response);
        self::assertCount(0, $response['details']);
    }
}
