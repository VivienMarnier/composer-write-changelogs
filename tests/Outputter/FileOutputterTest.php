<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\tests\Outputter;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Package\Package;
use PHPUnit\Framework\TestCase;
use Spiriit\ComposerWriteChangelogs\OperationHandler\Install\InstallOutputFileHandler;
use Spiriit\ComposerWriteChangelogs\OperationHandler\OperationHandler;
use Spiriit\ComposerWriteChangelogs\OperationHandler\Uninstall\UninstallOutputFileHandler;
use Spiriit\ComposerWriteChangelogs\OperationHandler\Update\UpdateOutputFileHandler;
use Spiriit\ComposerWriteChangelogs\Outputter\FileOutputter;
use Spiriit\ComposerWriteChangelogs\tests\resources\FakeHandler;
use Spiriit\ComposerWriteChangelogs\tests\resources\FakeOperation;
use Spiriit\ComposerWriteChangelogs\tests\resources\FakeUrlGenerator;
use Spiriit\ComposerWriteChangelogs\UrlGenerator\UrlGenerator;

class FileOutputterTest extends TestCase
{
    /** @var FileOutputter */
    private $fileOutputterText;

    /** @var FileOutputter */
    private $fileOutputterJSon;

    /** @var OperationHandler[] */
    private $operationHandlers;

    /** @var UrlGenerator[] */
    private $urlGenerators;

    protected function setUp(): void
    {
        $this->operationHandlers = [
            new InstallOutputFileHandler(FileOutputter::TEXT_FORMAT),
            new UninstallOutputFileHandler(FileOutputter::TEXT_FORMAT),
            new UpdateOutputFileHandler(FileOutputter::TEXT_FORMAT),
        ];

        $this->urlGenerators = [
            new FakeUrlGenerator(false, '/compare-url1', '/release-url1'),
            new FakeUrlGenerator(true, '/compare-url2', '/release-url2'),
            new FakeUrlGenerator(true, '/compare-url3', '/release-url3'),
        ];

        $handlers = [
            new InstallOutputFileHandler(FileOutputter::JSON_FORMAT),
            new UninstallOutputFileHandler(FileOutputter::JSON_FORMAT),
            new UpdateOutputFileHandler(FileOutputter::JSON_FORMAT),
        ];

        $this->fileOutputterText = new FileOutputter($this->operationHandlers, $this->urlGenerators);
        $this->fileOutputterJSon = new FileOutputter($handlers, $this->urlGenerators);
    }

    /**
     * @test
     */
    public function testItAddsOperation(): void
    {
        $operation = new InstallOperation(
            new Package('acme/my-project', 'v1.0.0.0', 'v1.0.0')
        );

        $operation2 = new UpdateOperation(
            new Package('acme/my-project1', 'v1.0.0.0', 'v1.0.0'),
            new Package('acme/my-project2', 'v1.0.1.0', 'v1.0.1')
        );

        $this->fileOutputterText->addOperation($operation);
        $this->fileOutputterText->addOperation($operation2);

        $expectedOutput = <<<TEXT
Changelogs summary:

 - acme/my-project installed in version v1.0.0
   Release notes: /release-url2

 - acme/my-project1 updated from v1.0.0 to v1.0.1 patch
   See changes: /compare-url2
   Release notes: /release-url2

TEXT;

        $this->assertFalse($this->fileOutputterText->isEmpty());
        $this->assertSame($expectedOutput, $this->fileOutputterText->getOutput(FileOutputter::TEXT_FORMAT));
    }

    /**
     * @test
     */
    public function testItOutputsWithNoSupportedUrlGenerator(): void
    {
        $this->fileOutputterText = new FileOutputter($this->operationHandlers, []);

        $operation = new InstallOperation(
            new Package('acme/my-project', 'v1.0.0.0', 'v1.0.0')
        );

        $operation2 = new UpdateOperation(
            new Package('acme/my-project1', 'v1.0.0.0', 'v1.0.0'),
            new Package('acme/my-project2', 'v1.0.1.0', 'v1.0.1')
        );

        $this->fileOutputterText->addOperation($operation);
        $this->fileOutputterText->addOperation($operation2);

        $expectedOutput = <<<TEXT
Changelogs summary:

 - acme/my-project installed in version v1.0.0

 - acme/my-project1 updated from v1.0.0 to v1.0.1 patch

TEXT;

        $this->assertFalse($this->fileOutputterText->isEmpty());
        $this->assertSame($expectedOutput, $this->fileOutputterText->getOutput(FileOutputter::TEXT_FORMAT));
    }

    /**
     * @test
     */
    public function testItOutputsWithNoSupportedOperationHandler(): void
    {
        $this->fileOutputterText = new FileOutputter([], []);

        $operation = new InstallOperation(
            new Package('acme/my-project', 'v1.0.0.0', 'v1.0.0')
        );

        $operation2 = new UpdateOperation(
            new Package('acme/my-project1', 'v1.0.0.0', 'v1.0.0'),
            new Package('acme/my-project2', 'v1.0.1.0', 'v1.0.1')
        );

        $this->fileOutputterText->addOperation($operation);
        $this->fileOutputterText->addOperation($operation2);

        $expectedOutput = <<<TEXT
Changelogs summary:

TEXT;

        $this->assertFalse($this->fileOutputterText->isEmpty());
        $this->assertSame($expectedOutput, $this->fileOutputterText->getOutput(FileOutputter::TEXT_FORMAT));
    }

    /**
     * @test
     */
    public function testItOutputsRightText(): void
    {
        $operation = new InstallOperation(
            new Package('acme/my-project', 'v1.0.0.0', 'v1.0.0')
        );

        $operation2 = new UpdateOperation(
            new Package('acme/my-project1', 'v1.0.0.0', 'v1.0.0'),
            new Package('acme/my-project2', 'v1.0.1.0', 'v1.0.1')
        );

        $this->fileOutputterText->addOperation($operation);
        $this->fileOutputterText->addOperation($operation2);

        $expectedOutput = <<<TEXT
Changelogs summary:

 - acme/my-project installed in version v1.0.0
   Release notes: /release-url2

 - acme/my-project1 updated from v1.0.0 to v1.0.1 patch
   See changes: /compare-url2
   Release notes: /release-url2

TEXT;

        $this->assertFalse($this->fileOutputterText->isEmpty());
        $this->assertSame($expectedOutput, $this->fileOutputterText->getOutput(FileOutputter::TEXT_FORMAT));
    }

    /**
     * @test
     */
    public function testItOutputsRightJson(): void
    {
        $operation = new InstallOperation(
            new Package('acme/my-project', 'v1.0.0.0', 'v1.0.0')
        );

        $operation2 = new UpdateOperation(
            new Package('acme/my-project1', 'v1.0.0.0', 'v1.0.0'),
            new Package('acme/my-project2', 'v1.0.1.0', 'v1.0.1')
        );

        $this->fileOutputterJSon->addOperation($operation);
        $this->fileOutputterJSon->addOperation($operation2);

        $expectedOutput = '[{"operation":"install","package":"acme/my-project","phrasing":"installed in version","version":"v1.0.0","releaseUrl":"/release-url2"},{"operation":"update","package":"acme/my-project1","action":"updated","phrasing":"updated from","versionFrom":"v1.0.0","versionTo":"v1.0.1","semver":"patch","changesUrl":"/compare-url2","releaseUrl":"/release-url2"}]';

        $this->assertFalse($this->fileOutputterJSon->isEmpty());
        $this->assertSame($expectedOutput, $this->fileOutputterJSon->getOutput(FileOutputter::JSON_FORMAT));
    }

    /**
     * @test
     */
    public function testItOutputsNothingWithoutOperation(): void
    {
        $expectedOutput = <<<TEXT
No changelogs summary
TEXT;

        $this->assertTrue($this->fileOutputterText->isEmpty());
        $this->assertSame($expectedOutput, $this->fileOutputterText->getOutput(FileOutputter::TEXT_FORMAT));
    }
}
