<?php

declare(strict_types=1);

namespace Chaos\ActuatorBundle\Tests\Controller;

class HealthControllerTest extends ControllerTestCase
{
    public function testHealthEndpoint(): void
    {
        $this->client->request('GET', '/health');

        $response = $this->client->getResponse();

        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals(['status' => 'up'], json_decode((string) $response->getContent(), true));
    }
}
