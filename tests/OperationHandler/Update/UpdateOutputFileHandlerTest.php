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
use Spiriit\ComposerWriteChangelogs\OperationHandler\Update\UpdateOutputFileHandler;
use Spiriit\ComposerWriteChangelogs\Outputter\FileOutputter;
use Spiriit\ComposerWriteChangelogs\tests\resources\FakeOperation;
use Spiriit\ComposerWriteChangelogs\tests\resources\FakeUrlGenerator;

class UpdateOutputFileHandlerTest extends TestCase
{
    private UpdateOutputFileHandler $updateOutputFileHandlerText;

    private UpdateOutputFileHandler $updateOutputFileHandlerJson;

    protected function setUp(): void
    {
        $this->updateOutputFileHandlerText = new UpdateOutputFileHandler(FileOutputter::TEXT_FORMAT);
        $this->updateOutputFileHandlerJson = new UpdateOutputFileHandler(FileOutputter::JSON_FORMAT);
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

        $this->assertTrue($this->updateOutputFileHandlerText->supports($operation));
    }

    /**
     * @test
     */
    public function testItDoesNotSupportNonUpdateOperation(): void
    {
        $this->assertFalse($this->updateOutputFileHandlerText->supports(new FakeOperation('')));
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
            $this->updateOutputFileHandlerText->extractSourceUrl($operation)
        );
    }

    /**
     * @test
     */
    public function testItThrowsExceptionWhenExtractingSourceUrlFromNonUpdateOperation(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Operation should be an instance of UpdateOperation');

        $this->updateOutputFileHandlerText->extractSourceUrl(new FakeOperation(''));
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
            ' - acme/my-project1 updated from v1.0.0 to v1.1.1 minor',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->updateOutputFileHandlerText->getOutput($operation, null)
        );
    }

    /**
     * @test
     */
    public function testItGetsArrayOutputWithoutUrlGenerator(): void
    {
        $package1 = new Package('acme/my-project1', 'v1.0.0.0', 'v1.0.0');
        $package1->setSourceUrl('https://example.com/acme/my-project1.git');

        $package2 = new Package('acme/my-project2', 'v1.1.1.0', 'v1.1.1');
        $package2->setSourceUrl('https://example.com/acme/my-project2.git');

        $operation = new UpdateOperation($package1, $package2);

        $expectedOutput = [
            'operation' => 'update',
            'package' => 'acme/my-project1',
            'action' => 'updated',
            'phrasing' => 'updated from',
            'versionFrom' => 'v1.0.0',
            'versionTo' => 'v1.1.1',
            'semver' => 'minor',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->updateOutputFileHandlerJson->getOutput($operation, null)
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
            ' - acme/my-project1 updated from v1.0.0 to v1.0.1 patch',
            '   Release notes: https://example.com/acme/my-project/release/v1.0.1',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->updateOutputFileHandlerText->getOutput($operation, $urlGenerator)
        );
    }

    /**
     * @test
     */
    public function testItGetsArrayOutputWithUrlGeneratorNoSupportingCompareUrl(): void
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
            'operation' => 'update',
            'package' => 'acme/my-project1',
            'action' => 'updated',
            'phrasing' => 'updated from',
            'versionFrom' => 'v1.0.0',
            'versionTo' => 'v1.0.1',
            'semver' => 'patch',
            'releaseUrl' => 'https://example.com/acme/my-project/release/v1.0.1',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->updateOutputFileHandlerJson->getOutput($operation, $urlGenerator)
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
            ' - acme/my-project1 updated from v1.0.0 to v1.0.1 patch',
            '   See changes: https://example.com/acme/my-project/compare/v1.0.0/v1.0.1',
            '   Release notes: https://example.com/acme/my-project/release/v1.0.1',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->updateOutputFileHandlerText->getOutput($operation, $urlGenerator)
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
            ' - acme/my-project1 updated from v1.0.0 to v1.0.1 patch',
            '   See changes: https://example.com/acme/my-project/compare/v1.0.0/v1.0.1',
            '   Release notes: https://example.com/acme/my-project/release/v1.0.1',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->updateOutputFileHandlerText->getOutput($operation, $urlGenerator)
        );
    }

    /**
     * @test
     */
    public function testItGetsArrayOutputWithUrlGeneratorSupportingAllUrls(): void
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
            'operation' => 'update',
            'package' => 'acme/my-project1',
            'action' => 'updated',
            'phrasing' => 'updated from',
            'versionFrom' => 'v1.0.0',
            'versionTo' => 'v1.0.1',
            'semver' => 'patch',
            'changesUrl' => 'https://example.com/acme/my-project/compare/v1.0.0/v1.0.1',
            'releaseUrl' => 'https://example.com/acme/my-project/release/v1.0.1',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->updateOutputFileHandlerJson->getOutput($operation, $urlGenerator)
        );
    }

    /**
     * @test
     */
    public function testItThrowsExceptionWhenGettingOutputFromNonUpdateOperation(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Operation should be an instance of UpdateOperation');

        $this->updateOutputFileHandlerText->getOutput(new FakeOperation(''));
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
            ' - acme/my-project1 updated from v1.0.0 to v1.0.1 patch',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->updateOutputFileHandlerText->getOutput($operationUpdate, null)
        );

        $operationDowngrade = new UpdateOperation($package2, $package1);

        $expectedOutput = [
            ' - acme/my-project2 downgraded from v1.0.1 to v1.0.0 patch',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->updateOutputFileHandlerText->getOutput($operationDowngrade, null)
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
            ' - acme/my-project1 updated from v1.0.0 to v1.0.1 patch',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->updateOutputFileHandlerText->getOutput($patchUpdate, null)
        );

        $minorUpdate = new UpdateOperation($base, $minor);

        $expectedOutput = [
            ' - acme/my-project1 updated from v1.0.0 to v1.1.0 minor',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->updateOutputFileHandlerText->getOutput($minorUpdate, null)
        );

        $majorUpdate = new UpdateOperation($base, $major);

        $expectedOutput = [
            ' - acme/my-project1 updated from v1.0.0 to v2.0.0 major',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->updateOutputFileHandlerText->getOutput($majorUpdate, null)
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
            ' - acme/my-project1 updated from dev-master@958a5dd to dev-master@6d57476',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->updateOutputFileHandlerText->getOutput($operationUpdate, null)
        );
    }
}
