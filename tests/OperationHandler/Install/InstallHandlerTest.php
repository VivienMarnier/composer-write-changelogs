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
use Spiriit\ComposerWriteChangelogs\OperationHandler\Install\InstallHandler;
use Spiriit\ComposerWriteChangelogs\tests\resources\FakeOperation;
use Spiriit\ComposerWriteChangelogs\tests\resources\FakeUrlGenerator;

class InstallHandlerTest extends TestCase
{
    private InstallHandler $SUT;

    protected function setUp(): void
    {
        $this->SUT = new InstallHandler();
    }

    /**
     * @test
     */
    public function test_it_supports_install_operation(): void
    {
        $operation = new InstallOperation(
            new Package('acme/my-project', 'v1.0.0.0', 'v1.0.0')
        );

        $this->assertTrue($this->SUT->supports($operation));
    }

    /**
     * @test
     */
    public function test_it_does_not_support_non_install_operation(): void
    {
        $this->assertFalse($this->SUT->supports(new FakeOperation('')));
    }

    /**
     * @test
     */
    public function test_it_extracts_source_url(): void
    {
        $package = new Package('acme/my-project', 'v1.0.0.0', 'v1.0.0');
        $package->setSourceUrl('https://example.com/acme/my-project.git');

        $operation = new InstallOperation($package);

        $this->assertSame(
            'https://example.com/acme/my-project.git',
            $this->SUT->extractSourceUrl($operation)
        );
    }

    /**
     * @test
     */
    public function test_it_throws_exception_when_extracting_source_url_from_non_install_operation(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Operation should be an instance of InstallOperation');

        $this->SUT->extractSourceUrl(new FakeOperation(''));
    }

    /**
     * @test
     */
    public function test_it_gets_output_without_url_generator(): void
    {
        $package = new Package('acme/my-project', 'v1.0.0.0', 'v1.0.0');
        $package->setSourceUrl('https://example.com/acme/my-project.git');

        $operation = new InstallOperation($package);

        $expectedOutput = [
            ' - <fg=green>acme/my-project</fg=green> installed in version <fg=yellow>v1.0.0</fg=yellow>',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->SUT->getOutput($operation, null)
        );
    }

    /**
     * @test
     */
    public function test_it_gets_output_with_url_generator_no_supporting_compare_url(): void
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
            ' - <fg=green>acme/my-project</fg=green> installed in version <fg=yellow>v1.0.0</fg=yellow>',
            '   Release notes: https://example.com/acme/my-project/release/v1.0.1',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->SUT->getOutput($operation, $urlGenerator)
        );
    }

    /**
     * @test
     */
    public function test_it_gets_output_with_url_generator_no_supporting_release_url(): void
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
            ' - <fg=green>acme/my-project</fg=green> installed in version <fg=yellow>v1.0.0</fg=yellow>',
            '   Release notes: https://example.com/acme/my-project/release/v1.0.1',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->SUT->getOutput($operation, $urlGenerator)
        );
    }

    /**
     * @test
     */
    public function test_it_gets_output_with_url_generator_supporting_all_urls(): void
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
            ' - <fg=green>acme/my-project</fg=green> installed in version <fg=yellow>v1.0.0</fg=yellow>',
            '   Release notes: https://example.com/acme/my-project/release/v1.0.1',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->SUT->getOutput($operation, $urlGenerator)
        );
    }

    /**
     * @test
     */
    public function test_it_throws_exception_when_getting_output_from_non_install_operation(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Operation should be an instance of InstallOperation');

        $this->SUT->getOutput(new FakeOperation(''));
    }
}
