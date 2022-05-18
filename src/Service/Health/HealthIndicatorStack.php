<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Health;

class HealthIndicatorStack implements \JsonSerializable
{
    /**
     * @var HealthIndicator[]
     */
    private iterable $indicators;

    /**
     * @param HealthIndicator[] $indicators
     */
    public function __construct(iterable $indicators)
    {
        $this->indicators = $indicators;
    }

    /**
     * @return array<int, string>
     */
    private function defaultOrder(): array
    {
        return [
            Health::UNKNOWN,
            Health::DOWN,
            Health::UP,
        ];
    }

    /**
     * @return array{'status': string, 'details'?: array<string, array{'status': string, 'details': array<mixed>}>}
     */
    public function toArray(): array
    {
        $status = Health::UP;
        $details = [];
        foreach ($this->indicators as $indicator) {
            $health = $indicator->health();
            $currentKey = array_search($status, $this->defaultOrder(), true);
            $key = array_search($health->getStatus(), $this->defaultOrder(), true);

            if ($key === false) {
                $status = Health::UNKNOWN;
            }
            if ($currentKey > $key) {
                $status = $this->defaultOrder()[$key];
            }

            $details[$indicator->name()] = [
                'status' => $this->defaultOrder()[$key],
                'details' => $health->getDetails(),
            ];
        }

        $response = [
            'status' => $status,
        ];

        if (count($details) !== 0) {
            $response['details'] = $details;
        }

        return $response;
    }

    /**
     *  @return array{'status': string, 'details'?: array<string, array{'status': string, 'details': array<mixed>}>}
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
