<?php

declare(strict_types=1);

namespace Chaos\ActuatorBundle\Tests\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InfoControllerTest extends ControllerTestCase
{
    public function testInfoEndpointWillReturnNotFoundIfNotEnabled(): void
    {
        $this->rebootKernelWithConfig(['info' => ['enabled' => false]]);
        self::expectException(NotFoundHttpException::class);
        $this->client->catchExceptions(false);
        $this->client->request('GET', '/info');

        $this->client->getResponse();
    }

    public function testInfoEndpoint(): void
    {
        $this->client->request('GET', '/info');

        $response = $this->client->getResponse();

        self::assertEquals(200, $response->getStatusCode());
        $json = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($json);
        self::assertArrayHasKey('php', $json);
        self::assertArrayHasKey('symfony', $json);
        self::assertArrayHasKey('git', $json);
    }
}
