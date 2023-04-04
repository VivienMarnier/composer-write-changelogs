<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\tests\OperationHandler\Install;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\Package\Package;
use LogicException;
use PHPUnit\Framework\TestCase;
use Spiriit\ComposerWriteChangelogs\OperationHandler\Install\InstallOutputFileHandler;
use Spiriit\ComposerWriteChangelogs\Outputter\FileOutputter;
use Spiriit\ComposerWriteChangelogs\tests\resources\FakeOperation;
use Spiriit\ComposerWriteChangelogs\tests\resources\FakeUrlGenerator;

class InstallOutputFileHandlerTest extends TestCase
{
    private InstallOutputFileHandler $installOutputFileHandlerText;

    private InstallOutputFileHandler $installOutputFileHandlerJson;

    protected function setUp(): void
    {
        $this->installOutputFileHandlerText = new InstallOutputFileHandler(FileOutputter::TEXT_FORMAT);
        $this->installOutputFileHandlerJson = new InstallOutputFileHandler(FileOutputter::JSON_FORMAT);
    }

    /**
     * @test
     */
    public function testItSupportsInstallOperation(): void
    {
        $operation = new InstallOperation(
            new Package('acme/my-project', 'v1.0.0.0', 'v1.0.0')
        );

        $this->assertTrue($this->installOutputFileHandlerText->supports($operation));
    }

    /**
     * @test
     */
    public function testItDoesNotSupportNonInstallOperation(): void
    {
        $this->assertFalse($this->installOutputFileHandlerText->supports(new FakeOperation('')));
    }

    /**
     * @test
     */
    public function testItExtractsSourceUrl(): void
    {
        $package = new Package('acme/my-project', 'v1.0.0.0', 'v1.0.0');
        $package->setSourceUrl('https://example.com/acme/my-project.git');

        $operation = new InstallOperation($package);

        $this->assertSame(
            'https://example.com/acme/my-project.git',
            $this->installOutputFileHandlerText->extractSourceUrl($operation)
        );
    }

    /**
     * @test
     */
    public function testItThrowsExceptionWhenExtractingSourceUrlFromNonInstallOperation(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Operation should be an instance of InstallOperation');

        $this->installOutputFileHandlerText->extractSourceUrl(new FakeOperation(''));
    }

    /**
     * @test
     */
    public function testItGetsOutputWithoutUrlGenerator(): void
    {
        $package = new Package('acme/my-project', 'v1.0.0.0', 'v1.0.0');
        $package->setSourceUrl('https://example.com/acme/my-project.git');

        $operation = new InstallOperation($package);

        $expectedOutput = [
            ' - acme/my-project installed in version v1.0.0',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->installOutputFileHandlerText->getOutput($operation, null)
        );
    }

    /**
     * @test
     */
    public function testItGetsArrayOutputWithoutUrlGenerator(): void
    {
        $package = new Package('acme/my-project', 'v1.0.0.0', 'v1.0.0');
        $package->setSourceUrl('https://example.com/acme/my-project.git');

        $operation = new InstallOperation($package);

        $expectedOutput = [
            'operation' => 'install',
            'package' => 'acme/my-project',
            'phrasing' => 'installed in version',
            'version' => 'v1.0.0',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->installOutputFileHandlerJson->getOutput($operation, null)
        );
    }

    /**
     * @test
     */
    public function testItGetsOutputWithUrlGeneratorNoSupportingCompareUrl(): void
    {
        $operation = new InstallOperation(
            new Package('acme/my-project', 'v1.0.0.0', 'v1.0.0')
        );

        $urlGenerator = new FakeUrlGenerator(
            true,
            null,
            'https://example.com/acme/my-project/release/v1.0.1'
        );

        $expectedOutput = [
            ' - acme/my-project installed in version v1.0.0',
            '   Release notes: https://example.com/acme/my-project/release/v1.0.1',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->installOutputFileHandlerText->getOutput($operation, $urlGenerator)
        );
    }

    /**
     * @test
     */
    public function testItGetsArrayOutputWithUrlGeneratorNoSupportingCompareUrl(): void
    {
        $operation = new InstallOperation(
            new Package('acme/my-project', 'v1.0.0.0', 'v1.0.0')
        );

        $urlGenerator = new FakeUrlGenerator(
            true,
            null,
            'https://example.com/acme/my-project/release/v1.0.1'
        );

        $expectedOutput = [
            'operation' => 'install',
            'package' => 'acme/my-project',
            'phrasing' => 'installed in version',
            'version' => 'v1.0.0',
            'releaseUrl' => 'https://example.com/acme/my-project/release/v1.0.1',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->installOutputFileHandlerJson->getOutput($operation, $urlGenerator)
        );
    }

    /**
     * @test
     */
    public function testItGetsOutputWithUrlGeneratorNoSupportingReleaseUrl(): void
    {
        $operation = new InstallOperation(
            new Package('acme/my-project', 'v1.0.0.0', 'v1.0.0')
        );

        $urlGenerator = new FakeUrlGenerator(
            true,
            'https://example.com/acme/my-project/compare/v1.0.0/v1.0.1',
            'https://example.com/acme/my-project/release/v1.0.1'
        );

        $expectedOutput = [
            ' - acme/my-project installed in version v1.0.0',
            '   Release notes: https://example.com/acme/my-project/release/v1.0.1',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->installOutputFileHandlerText->getOutput($operation, $urlGenerator)
        );
    }

    /**
     * @test
     */
    public function testItGetsOutputWithUrlGeneratorSupportingAllUrls(): void
    {
        $operation = new InstallOperation(
            new Package('acme/my-project', 'v1.0.0.0', 'v1.0.0')
        );

        $urlGenerator = new FakeUrlGenerator(
            true,
            'https://example.com/acme/my-project/compare/v1.0.0/v1.0.1',
            'https://example.com/acme/my-project/release/v1.0.1'
        );

        $expectedOutput = [
            ' - acme/my-project installed in version v1.0.0',
            '   Release notes: https://example.com/acme/my-project/release/v1.0.1',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->installOutputFileHandlerText->getOutput($operation, $urlGenerator)
        );
    }

    /**
     * @test
     */
    public function testItThrowsExceptionWhenGettingOutputFromNonInstallOperation(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Operation should be an instance of InstallOperation');

        $this->installOutputFileHandlerText->getOutput(new FakeOperation(''));
    }
}
