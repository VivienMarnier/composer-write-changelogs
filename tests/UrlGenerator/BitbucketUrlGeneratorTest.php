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
use Spiriit\ComposerWriteChangelogs\UrlGenerator\BitbucketUrlGenerator;
use Spiriit\ComposerWriteChangelogs\Version;

class BitbucketUrlGeneratorTest extends TestCase
{
    private BitbucketUrlGenerator $SUT;

    protected function setUp(): void
    {
        $this->SUT = new BitbucketUrlGenerator();
    }

    /**
     * @test
     */
    public function testItSupportsBitbucketUrls(): void
    {
        $this->assertTrue($this->SUT->supports('https://bitbucket.org/mailchimp/mandrill-api-php.git'));
        $this->assertTrue($this->SUT->supports('https://bitbucket.org/rogoOOS/rog'));
        $this->assertTrue($this->SUT->supports('git@bitbucket.org:private/repo.git'));
    }

    /**
     * @test
     */
    public function testItDoesNotSupportNonBitbucketUrls(): void
    {
        $this->assertFalse($this->SUT->supports('https://github.com/phpunit/phpunit-mock-objects.git'));
        $this->assertFalse($this->SUT->supports('https://github.com/symfony/console'));
    }

    /**
     * @test
     */
    public function testItGeneratesCompareUrlsWithOrWithoutGitExtensionInSourceUrl(): void
    {
        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');

        $this->assertSame(
            'https://bitbucket.org/acme/repo/branches/compare/v1.0.1%0Dv1.0.0',
            $this->SUT->generateCompareUrl(
                'https://bitbucket.org/acme/repo',
                $versionFrom,
                'https://bitbucket.org/acme/repo',
                $versionTo
            )
        );

        $this->assertSame(
            'https://bitbucket.org/acme/repo/branches/compare/v1.0.1%0Dv1.0.0',
            $this->SUT->generateCompareUrl(
                'https://bitbucket.org/acme/repo.git',
                $versionFrom,
                'https://bitbucket.org/acme/repo.git',
                $versionTo
            )
        );
    }

    /**
     * @test
     */
    public function testItGeneratesCompareUrlsWithDevVersions(): void
    {
        $versionFrom = new Version('v1.0.9999999.9999999-dev', 'dev-master', 'dev-master 1234abc');
        $versionTo = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');

        $this->assertSame(
            'https://bitbucket.org/acme/repo/branches/compare/v1.0.1%0D1234abc',
            $this->SUT->generateCompareUrl(
                'https://bitbucket.org/acme/repo.git',
                $versionFrom,
                'https://bitbucket.org/acme/repo.git',
                $versionTo
            )
        );

        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('9999999-dev', 'dev-master', 'dev-master 6789def');

        $this->assertSame(
            'https://bitbucket.org/acme/repo/branches/compare/6789def%0Dv1.0.0',
            $this->SUT->generateCompareUrl(
                'https://bitbucket.org/acme/repo.git',
                $versionFrom,
                'https://bitbucket.org/acme/repo.git',
                $versionTo
            )
        );

        $versionFrom = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');
        $versionTo = new Version('dev-fix/issue', 'dev-fix/issue', 'dev-fix/issue 1234abc');

        $this->assertSame(
            'https://bitbucket.org/acme/repo/branches/compare/1234abc%0Dv1.0.1',
            $this->SUT->generateCompareUrl(
                'https://bitbucket.org/acme/repo.git',
                $versionFrom,
                'https://bitbucket.org/acme/repo.git',
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
            'https://bitbucket.org/acme2/repo/branches/compare/acme2/repo:v1.0.1%0Dacme1/repo:v1.0.0',
            $this->SUT->generateCompareUrl(
                'https://bitbucket.org/acme1/repo',
                $versionFrom,
                'https://bitbucket.org/acme2/repo',
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
                'https://bitbucket.org/acme2/repo',
                $versionTo
            )
        );

        $this->assertNull(
            $this->SUT->generateCompareUrl(
                'https://bitbucket.org/acme1/repo',
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
        $this->expectExceptionMessage('Unrecognized url format for bitbucket.org ("https://bitbucket.org/acme2")');

        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');

        $this->SUT->generateCompareUrl(
            'https://bitbucket.org/acme1/repo',
            $versionFrom,
            'https://bitbucket.org/acme2',
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
            'https://bitbucket.org/acme/repo/branches/compare/v1.0.1%0Dv1.0.0',
            $this->SUT->generateCompareUrl(
                'git@bitbucket.org:acme/repo.git',
                $versionFrom,
                'git@bitbucket.org:acme/repo.git',
                $versionTo
            )
        );
    }

    /**
     * @test
     */
    public function testItDoesNotGenerateReleaseUrls(): void
    {
        $this->assertNull(
            $this->SUT->generateReleaseUrl(
                'https://bitbucket.org/acme/repo',
                new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1')
            )
        );

        $this->assertNull(
            $this->SUT->generateReleaseUrl(
                'https://bitbucket.org/acme/repo.git',
                new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1')
            )
        );
    }
}
