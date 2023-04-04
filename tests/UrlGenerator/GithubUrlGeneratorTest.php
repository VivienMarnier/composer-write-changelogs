<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\tests\UrlGenerator;

use LogicException;
use PHPUnit\Framework\TestCase;
use Spiriit\ComposerWriteChangelogs\UrlGenerator\GithubUrlGenerator;
use Spiriit\ComposerWriteChangelogs\Version;

class GithubUrlGeneratorTest extends TestCase
{
    private GithubUrlGenerator $SUT;

    protected function setUp(): void
    {
        $this->SUT = new GithubUrlGenerator();
    }

    /**
     * @test
     */
    public function testItSupportsGithubUrls(): void
    {
        $this->assertTrue($this->SUT->supports('https://github.com/phpunit/phpunit-mock-objects.git'));
        $this->assertTrue($this->SUT->supports('https://github.com/symfony/console'));
        $this->assertTrue($this->SUT->supports('git@github.com:private/repo.git'));
    }

    /**
     * @test
     */
    public function testItDoesNotSupportNonGithubUrls(): void
    {
        $this->assertFalse($this->SUT->supports('https://bitbucket.org/mailchimp/mandrill-api-php.git'));
        $this->assertFalse($this->SUT->supports('https://bitbucket.org/rogoOOS/rog'));
    }

    /**
     * @test
     */
    public function testItGeneratesCompareUrlsWithOrWithoutGitExtensionInSourceUrl(): void
    {
        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');

        $this->assertSame(
            'https://github.com/acme/repo/compare/v1.0.0...v1.0.1',
            $this->SUT->generateCompareUrl(
                'https://github.com/acme/repo',
                $versionFrom,
                'https://github.com/acme/repo',
                $versionTo
            )
        );

        $this->assertSame(
            'https://github.com/acme/repo/compare/v1.0.0...v1.0.1',
            $this->SUT->generateCompareUrl(
                'https://github.com/acme/repo.git',
                $versionFrom,
                'https://github.com/acme/repo.git',
                $versionTo
            )
        );
    }

    /**
     * @test
     */
    public function testItGeneratesCompareUrlsWithDevVersions(): void
    {
        $versionFrom = new Version('v.1.0.9999999.9999999-dev', 'dev-master', 'dev-master 1234abc');
        $versionTo = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');

        $this->assertSame(
            'https://github.com/acme/repo/compare/1234abc...v1.0.1',
            $this->SUT->generateCompareUrl(
                'https://github.com/acme/repo.git',
                $versionFrom,
                'https://github.com/acme/repo.git',
                $versionTo
            )
        );

        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('9999999-dev', 'dev-master', 'dev-master 6789def');

        $this->assertSame(
            'https://github.com/acme/repo/compare/v1.0.0...6789def',
            $this->SUT->generateCompareUrl(
                'https://github.com/acme/repo.git',
                $versionFrom,
                'https://github.com/acme/repo.git',
                $versionTo
            )
        );

        $versionFrom = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');
        $versionTo = new Version('dev-fix/issue', 'dev-fix/issue', 'dev-fix/issue 1234abc');

        $this->assertSame(
            'https://github.com/acme/repo/compare/v1.0.1...1234abc',
            $this->SUT->generateCompareUrl(
                'https://github.com/acme/repo.git',
                $versionFrom,
                'https://github.com/acme/repo.git',
                $versionTo
            )
        );
    }

    /**
     * @test
     */
    public function testItGeneratesCompareUrlsAcrossForks(): void
    {
        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');

        $this->assertSame(
            'https://github.com/acme2/repo/compare/acme1:v1.0.0...acme2:v1.0.1',
            $this->SUT->generateCompareUrl(
                'https://github.com/acme1/repo',
                $versionFrom,
                'https://github.com/acme2/repo',
                $versionTo
            )
        );
    }

    /**
     * @test
     */
    public function testItDoesNotGenerateCompareUrlsForUnsupportedUrl(): void
    {
        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');

        $this->assertNull(
            $this->SUT->generateCompareUrl(
                '/home/toto/work/my-package',
                $versionFrom,
                'https://github.com/acme2/repo',
                $versionTo
            )
        );

        $this->assertNull(
            $this->SUT->generateCompareUrl(
                'https://github.com/acme1/repo',
                $versionFrom,
                '/home/toto/work/my-package',
                $versionTo
            )
        );
    }

    /**
     * @test
     */
    public function testItThrowsExceptionWhenGeneratingCompareUrlsAcrossForksIfASourceUrlIsInvalid(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Unrecognized url format for github.com ("https://github.com/acme2")');

        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');

        $this->SUT->generateCompareUrl(
            'https://github.com/acme1/repo',
            $versionFrom,
            'https://github.com/acme2',
            $versionTo
        );
    }

    /**
     * @test
     */
    public function testItGeneratesCompareUrlsWithSshSourceUrl(): void
    {
        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');

        $this->assertSame(
            'https://github.com/acme/repo/compare/v1.0.0...v1.0.1',
            $this->SUT->generateCompareUrl(
                'git@github.com:acme/repo.git',
                $versionFrom,
                'git@github.com:acme/repo.git',
                $versionTo
            )
        );
    }

    /**
     * @test
     */
    public function testItDoesNotGenerateReleaseUrlsForDevVersion(): void
    {
        $this->assertNull(
            $this->SUT->generateReleaseUrl(
                'https://github.com/acme/repo',
                new Version('9999999-dev', 'dev-master', 'dev-master 1234abc')
            )
        );

        $this->assertNull(
            $this->SUT->generateReleaseUrl(
                'https://github.com/acme/repo',
                new Version('dev-fix/issue', 'dev-fix/issue', 'dev-fix/issue 1234abc')
            )
        );
    }

    /**
     * @test
     */
    public function testItGeneratesReleaseUrls(): void
    {
        $this->assertSame(
            'https://github.com/acme/repo/releases/tag/v1.0.1',
            $this->SUT->generateReleaseUrl(
                'https://github.com/acme/repo',
                new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1')
            )
        );

        $this->assertSame(
            'https://github.com/acme/repo/releases/tag/v1.0.1',
            $this->SUT->generateReleaseUrl(
                'https://github.com/acme/repo.git',
                new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1')
            )
        );
    }

    /**
     * @test
     */
    public function testItGeneratesReleaseUrlWithSshSourceUrl(): void
    {
        $this->assertSame(
            'https://github.com/acme/repo/releases/tag/v1.0.1',
            $this->SUT->generateReleaseUrl(
                'git@github.com:acme/repo.git',
                new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1')
            )
        );
    }
}
