<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\tests\OperationHandler\Update;

use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Package\Package;
use LogicException;
use PHPUnit\Framework\TestCase;
use Spiriit\ComposerWriteChangelogs\OperationHandler\Update\UpdateHandler;
use Spiriit\ComposerWriteChangelogs\tests\resources\FakeOperation;
use Spiriit\ComposerWriteChangelogs\tests\resources\FakeUrlGenerator;

class UpdateHandlerTest extends TestCase
{
    /** @var UpdateHandler */
    private $SUT;

    protected function setUp(): void
    {
        $this->SUT = new UpdateHandler();
    }

    /**
     * @test
     */
    public function testItSupportsUpdateOperation(): void
    {
        $operation = new UpdateOperation(
            new Package('acme/my-project', 'v1.0.0.0', 'v1.0.0'),
            new Package('acme/my-project', 'v1.0.1.0', 'v1.0.1')
        );

        $this->assertTrue($this->SUT->supports($operation));
    }

    /**
     * @test
     */
    public function testItDoesNotSupportNonUpdateOperation(): void
    {
        $this->assertFalse($this->SUT->supports(new FakeOperation('')));
    }

    /**
     * @test
     */
    public function testItExtractsSourceUrl(): void
    {
        $package1 = new Package('acme/my-project1', 'v1.0.0.0', 'v1.0.0');
        $package1->setSourceUrl('https://example.com/acme/my-project1.git');

        $package2 = new Package('acme/my-project2', 'v1.0.1.0', 'v1.0.1');
        $package2->setSourceUrl('https://example.com/acme/my-project2.git');

        $operation = new UpdateOperation($package1, $package2);

        $this->assertSame(
            'https://example.com/acme/my-project2.git',
            $this->SUT->extractSourceUrl($operation)
        );
    }

    /**
     * @test
     */
    public function testItThrowsExceptionWhenExtractingSourceUrlFromNonUpdateOperation(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Operation should be an instance of UpdateOperation');

        $this->SUT->extractSourceUrl(new FakeOperation(''));
    }

    /**
     * @test
     */
    public function testItGetsOutputWithoutUrlGenerator(): void
    {
        $package1 = new Package('acme/my-project1', 'v1.0.0.0', 'v1.0.0');
        $package1->setSourceUrl('https://example.com/acme/my-project1.git');

        $package2 = new Package('acme/my-project2', 'v1.1.1.0', 'v1.1.1');
        $package2->setSourceUrl('https://example.com/acme/my-project2.git');

        $operation = new UpdateOperation($package1, $package2);

        $expectedOutput = [
            ' - <fg=green>acme/my-project1</fg=green> updated from <fg=yellow>v1.0.0</fg=yellow> to <fg=yellow>v1.1.1</fg=yellow> <fg=magenta>minor</>',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->SUT->getOutput($operation, null)
        );
    }

    /**
     * @test
     */
    public function testItGetsOutputWithUrlGeneratorNoSupportingCompareUrl(): void
    {
        $operation = new UpdateOperation(
            new Package('acme/my-project1', 'v1.0.0.0', 'v1.0.0'),
            new Package('acme/my-project2', 'v1.0.1.0', 'v1.0.1')
        );

        $urlGenerator = new FakeUrlGenerator(
            true,
            null,
            'https://example.com/acme/my-project/release/v1.0.1'
        );

        $expectedOutput = [
            ' - <fg=green>acme/my-project1</fg=green> updated from <fg=yellow>v1.0.0</fg=yellow> to <fg=yellow>v1.0.1</fg=yellow> <fg=cyan>patch</>',
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
    public function testItGetsOutputWithUrlGeneratorNoSupportingReleaseUrl(): void
    {
        $operation = new UpdateOperation(
            new Package('acme/my-project1', 'v1.0.0.0', 'v1.0.0'),
            new Package('acme/my-project2', 'v1.0.1.0', 'v1.0.1')
        );

        $urlGenerator = new FakeUrlGenerator(
            true,
            'https://example.com/acme/my-project/compare/v1.0.0/v1.0.1',

            'https://example.com/acme/my-project/release/v1.0.1'
        );

        $expectedOutput = [
            ' - <fg=green>acme/my-project1</fg=green> updated from <fg=yellow>v1.0.0</fg=yellow> to <fg=yellow>v1.0.1</fg=yellow> <fg=cyan>patch</>',
            '   See changes: https://example.com/acme/my-project/compare/v1.0.0/v1.0.1',
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
    public function testItGetsOutputWithUrlGeneratorSupportingAllUrls(): void
    {
        $operation = new UpdateOperation(
            new Package('acme/my-project1', 'v1.0.0.0', 'v1.0.0'),
            new Package('acme/my-project2', 'v1.0.1.0', 'v1.0.1')
        );

        $urlGenerator = new FakeUrlGenerator(
            true,
            'https://example.com/acme/my-project/compare/v1.0.0/v1.0.1',
            'https://example.com/acme/my-project/release/v1.0.1'
        );

        $expectedOutput = [
            ' - <fg=green>acme/my-project1</fg=green> updated from <fg=yellow>v1.0.0</fg=yellow> to <fg=yellow>v1.0.1</fg=yellow> <fg=cyan>patch</>',
            '   See changes: https://example.com/acme/my-project/compare/v1.0.0/v1.0.1',
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
    public function testItThrowsExceptionWhenGettingOutputFromNonUpdateOperation(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Operation should be an instance of UpdateOperation');

        $this->SUT->getOutput(new FakeOperation(''));
    }

    /**
     * @test
     */
    public function testItUsesCorrectActionName(): void
    {
        $package1 = new Package('acme/my-project1', 'v1.0.0.0', 'v1.0.0');
        $package2 = new Package('acme/my-project2', 'v1.0.1.0', 'v1.0.1');

        $operationUpdate = new UpdateOperation($package1, $package2);

        $expectedOutput = [
            ' - <fg=green>acme/my-project1</fg=green> updated from <fg=yellow>v1.0.0</fg=yellow> to <fg=yellow>v1.0.1</fg=yellow> <fg=cyan>patch</>',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->SUT->getOutput($operationUpdate, null)
        );

        $operationDowngrade = new UpdateOperation($package2, $package1);

        $expectedOutput = [
            ' - <fg=green>acme/my-project2</fg=green> downgraded from <fg=yellow>v1.0.1</fg=yellow> to <fg=yellow>v1.0.0</fg=yellow> <fg=cyan>patch</>',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->SUT->getOutput($operationDowngrade, null)
        );
    }

    /**
     * @test
     */
    public function testItOutputsTheCorrectSemverColors(): void
    {
        $base = new Package('acme/my-project1', 'v1.0.0.0', 'v1.0.0');
        $patch = new Package('acme/my-project1', 'v1.0.1.0', 'v1.0.1');
        $minor = new Package('acme/my-project2', 'v1.1.0.0', 'v1.1.0');
        $major = new Package('acme/my-project2', 'v2.0.0.0', 'v2.0.0');

        $patchUpdate = new UpdateOperation($base, $patch);

        $expectedOutput = [
            ' - <fg=green>acme/my-project1</fg=green> updated from <fg=yellow>v1.0.0</fg=yellow> to <fg=yellow>v1.0.1</fg=yellow> <fg=cyan>patch</>',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->SUT->getOutput($patchUpdate, null)
        );

        $minorUpdate = new UpdateOperation($base, $minor);

        $expectedOutput = [
            ' - <fg=green>acme/my-project1</fg=green> updated from <fg=yellow>v1.0.0</fg=yellow> to <fg=yellow>v1.1.0</fg=yellow> <fg=magenta>minor</>',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->SUT->getOutput($minorUpdate, null)
        );

        $majorUpdate = new UpdateOperation($base, $major);

        $expectedOutput = [
            ' - <fg=green>acme/my-project1</fg=green> updated from <fg=yellow>v1.0.0</fg=yellow> to <fg=yellow>v2.0.0</fg=yellow> <fg=red>major</>',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->SUT->getOutput($majorUpdate, null)
        );
    }

    /**
     * @test
     */
    public function testItDisplaysVcsRevisionForDevPackage(): void
    {
        $package1 = new Package('acme/my-project1', 'dev-master', 'dev-master');
        $package1->setSourceType('git');
        $package1->setSourceReference('958a5dd');
        $package2 = new Package('acme/my-project2', 'dev-master', 'dev-master');
        $package2->setSourceType('git');
        $package2->setSourceReference('6d57476');

        $operationUpdate = new UpdateOperation($package1, $package2);

        $expectedOutput = [
            ' - <fg=green>acme/my-project1</fg=green> updated from <fg=yellow>dev-master@958a5dd</fg=yellow> to <fg=yellow>dev-master@6d57476</fg=yellow>',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->SUT->getOutput($operationUpdate, null)
        );
    }
}
