<?php

declare(strict_types=1);

namespace Chaos\ActuatorBundle\Tests\Controller;

class InfoControllerTest extends ControllerTestCase
{
    public function testInfoEndpoint(): void
    {
        $this->client->request('GET', '/info');

        $response = $this->client->getResponse();

        self::assertEquals(200, $response->getStatusCode());
        $json = json_decode((string) $response->getContent(), true);

        self::assertArrayHasKey('php', $json);
        self::assertArrayHasKey('symfony', $json);
        self::assertArrayHasKey('git', $json);
    }
}
