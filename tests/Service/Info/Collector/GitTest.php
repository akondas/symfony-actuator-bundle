<?php

declare(strict_types=1);

namespace Chaos\ActuatorBundle\Tests\Service\Info\Collector;

use Akondas\ActuatorBundle\Service\Info\Collector\Git;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class GitTest extends TestCase
{
    /**
     * @var KernelInterface&MockObject
     */
    private KernelInterface $kernel;

    private vfsStreamDirectory $root;

    private Git $git;

    protected function setUp(): void
    {
        $this->root = vfsStream::setup('exampleDir');
        $this->kernel = self::createMock(KernelInterface::class);
        $this->kernel->method('getProjectDir')
            ->willReturn($this->root->url());

        $this->git = new Git($this->kernel);
    }

    public function testWillReturnGitAsName(): void
    {
        self::assertEquals('git', $this->git->collect()->name());
    }

    public function testGitWillReturnEmptyIfNoGitDirectory(): void
    {
        // when
        $collect = $this->git->collect();

        // then
        self::assertTrue($collect->isEmpty());
    }

    public function testWillLoadBranchFromGitDirectory(): void
    {
        // given
        $gitDirectory = vfsStream::newDirectory('.git')->at($this->root);
        vfsStream::newFile('HEAD')->withContent('ref: refs/heads/branchName')->at($gitDirectory);
        $refsDirectory = vfsStream::newDirectory('refs')->at($gitDirectory);
        $branchDirectory = vfsStream::newDirectory('heads')->at($refsDirectory);
        vfsStream::newFile('branchName')->withContent('c00000')->at($branchDirectory);

        // when
        $collect = $this->git->collect();

        // then
        self::assertFalse($collect->isEmpty());
        self::assertArrayHasKey('branch', $collect->jsonSerialize());
        self::assertEquals('branchName', $collect->jsonSerialize()['branch']);
    }

    public function testWillHandleAsEmptyIfNoHeadsFile(): void
    {
        // given
        $gitDirectory = vfsStream::newDirectory('.git')->at($this->root);
        vfsStream::newFile('HEAD')->withContent('ref: refs/heads/branchName')->at($gitDirectory);

        // when
        $collect = $this->git->collect();

        // then
        self::assertTrue($collect->isEmpty());
    }

    public function testWillHandleCommitNumberAsBranch(): void
    {
        // given
        $gitDirectory = vfsStream::newDirectory('.git')->at($this->root);
        vfsStream::newFile('HEAD')->withContent('c00000')->at($gitDirectory);

        // when
        $collect = $this->git->collect();

        // then
        self::assertFalse($collect->isEmpty());
        self::assertArrayHasKey('branch', $collect->jsonSerialize());
        self::assertEquals('c00000', $collect->jsonSerialize()['branch']);
    }

    public function testWillHandleCommitNumberFromBranch(): void
    {
        // given
        $gitDirectory = vfsStream::newDirectory('.git')->at($this->root);
        vfsStream::newFile('HEAD')->withContent('ref: refs/heads/branchName')->at($gitDirectory);
        $refsDirectory = vfsStream::newDirectory('refs')->at($gitDirectory);
        $branchDirectory = vfsStream::newDirectory('heads')->at($refsDirectory);
        vfsStream::newFile('branchName')->withContent('c00000')->at($branchDirectory);

        // when
        $collect = $this->git->collect();

        // then
        self::assertFalse($collect->isEmpty());
        self::assertArrayHasKey('commit', $collect->jsonSerialize());
        self::assertEquals('c00000', $collect->jsonSerialize()['commit']);
    }
}
