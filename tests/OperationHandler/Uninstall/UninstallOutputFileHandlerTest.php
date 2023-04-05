<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\tests\OperationHandler\Uninstall;

use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\Package\Package;
use LogicException;
use PHPUnit\Framework\TestCase;
use Spiriit\ComposerWriteChangelogs\OperationHandler\Uninstall\UninstallOutputFileHandler;
use Spiriit\ComposerWriteChangelogs\Outputter\FileOutputter;
use Spiriit\ComposerWriteChangelogs\tests\resources\FakeOperation;
use Spiriit\ComposerWriteChangelogs\tests\resources\FakeUrlGenerator;

class UninstallOutputFileHandlerTest extends TestCase
{
    private UninstallOutputFileHandler $uninstallOutputFileHandlerText;

    private UninstallOutputFileHandler $uninstallOutputFileHandlerJson;

    protected function setUp(): void
    {
        $this->uninstallOutputFileHandlerText = new UninstallOutputFileHandler(FileOutputter::TEXT_FORMAT);
        $this->uninstallOutputFileHandlerJson = new UninstallOutputFileHandler(FileOutputter::JSON_FORMAT);
    }

    /**
     * @test
     */
    public function test_it_supports_uninstall_operation(): void
    {
        $operation = new UninstallOperation(
            new Package('acme/my-project', 'v1.0.0.0', 'v1.0.0')
        );

        $this->assertTrue($this->uninstallOutputFileHandlerText->supports($operation));
    }

    /**
     * @test
     */
    public function test_it_does_not_support_non_uninstall_operation(): void
    {
        $this->assertFalse($this->uninstallOutputFileHandlerText->supports(new FakeOperation('')));
    }

    /**
     * @test
     */
    public function test_it_extracts_source_url(): void
    {
        $package = new Package('acme/my-project', 'v1.0.0.0', 'v1.0.0');
        $package->setSourceUrl('https://example.com/acme/my-project.git');

        $operation = new UninstallOperation($package);

        $this->assertSame(
            'https://example.com/acme/my-project.git',
            $this->uninstallOutputFileHandlerText->extractSourceUrl($operation)
        );
    }

    /**
     * @test
     */
    public function test_it_throws_exception_when_extracting_source_url_from_non_uninstall_operation(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Operation should be an instance of UninstallOperation');

        $this->uninstallOutputFileHandlerText->extractSourceUrl(new FakeOperation(''));
    }

    /**
     * @test
     */
    public function test_it_gets_output_without_url_generator(): void
    {
        $package = new Package('acme/my-project', 'v1.0.0.0', 'v1.0.0');
        $package->setSourceUrl('https://example.com/acme/my-project.git');

        $operation = new UninstallOperation($package);

        $expectedOutput = [
            ' - acme/my-project removed (installed version was v1.0.0)',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->uninstallOutputFileHandlerText->getOutput($operation, null)
        );
    }

    /**
     * @test
     */
    public function test_it_gets_array_output_without_url_generator(): void
    {
        $package = new Package('acme/my-project', 'v1.0.0.0', 'v1.0.0');
        $package->setSourceUrl('https://example.com/acme/my-project.git');

        $operation = new UninstallOperation($package);

        $expectedOutput = [
            'operation' => 'uninstall',
            'package' => 'acme/my-project',
            'phrasing' => 'removed',
            'version' => 'v1.0.0',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->uninstallOutputFileHandlerJson->getOutput($operation, null)
        );
    }

    /**
     * @test
     */
    public function test_it_gets_output_with_url_generator_no_supporting_compare_url(): void
    {
        $operation = new UninstallOperation(
            new Package('acme/my-project', 'v1.0.0.0', 'v1.0.0')
        );

        $urlGenerator = new FakeUrlGenerator(
            true,
            null,
            'https://example.com/acme/my-project/release/v1.0.1'
        );

        $expectedOutput = [
            ' - acme/my-project removed (installed version was v1.0.0)',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->uninstallOutputFileHandlerText->getOutput($operation, $urlGenerator)
        );
    }

    /**
     * @test
     */
    public function test_it_gets_output_with_url_generator_no_supporting_release_url(): void
    {
        $operation = new UninstallOperation(
            new Package('acme/my-project', 'v1.0.0.0', 'v1.0.0')
        );

        $urlGenerator = new FakeUrlGenerator(
            true,
            'https://example.com/acme/my-project/compare/v1.0.0/v1.0.1',
            'https://example.com/acme/my-project/release/v1.0.1'
        );

        $expectedOutput = [
            ' - acme/my-project removed (installed version was v1.0.0)',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->uninstallOutputFileHandlerText->getOutput($operation, $urlGenerator)
        );
    }

    /**
     * @test
     */
    public function test_it_gets_output_with_url_generator_supporting_all_urls(): void
    {
        $operation = new UninstallOperation(
            new Package('acme/my-project', 'v1.0.0.0', 'v1.0.0')
        );

        $urlGenerator = new FakeUrlGenerator(
            true,
            'https://example.com/acme/my-project/compare/v1.0.0/v1.0.1',
            'https://example.com/acme/my-project/release/v1.0.1'
        );

        $expectedOutput = [
            ' - acme/my-project removed (installed version was v1.0.0)',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->uninstallOutputFileHandlerText->getOutput($operation, $urlGenerator)
        );
    }

    /**
     * @test
     */
    public function test_it_throws_exception_when_getting_output_from_non_uninstall_operation(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Operation should be an instance of UninstallOperation');

        $this->uninstallOutputFileHandlerText->getOutput(new FakeOperation(''));
    }
}
