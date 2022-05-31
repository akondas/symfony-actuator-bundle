<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\Service\Info\Collector;

use Akondas\ActuatorBundle\Service\Info\Info;
use Symfony\Component\HttpKernel\KernelInterface;

class Git implements Collector
{
    private KernelInterface $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function collect(): Info
    {
        $gitDir = $this->kernel->getProjectDir().DIRECTORY_SEPARATOR.'.git';
        if (!file_exists($gitDir)) {
            return new Info('git', []);
        }

        $commit = null;
        $head = explode(' ', trim((string) file_get_contents($gitDir.DIRECTORY_SEPARATOR.'HEAD')));
        if (count($head) > 1) {
            if (!file_exists($gitDir.DIRECTORY_SEPARATOR.trim($head[1]))) {
                return new Info('git', []);
            }
            $commit = trim((string) file_get_contents($gitDir.'/'.trim($head[1])));
        } elseif (count($head) === 1) {
            $commit = $head[0];
        }

        return new Info('git', [
            'branch' => $head[0] === $commit ? $commit : str_replace('refs/heads/', '', $head[1]),
            'commit' => $commit,
        ]);
    }
}
