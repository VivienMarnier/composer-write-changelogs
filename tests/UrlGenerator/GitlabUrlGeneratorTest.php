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

use PHPUnit\Framework\TestCase;
use Spiriit\ComposerWriteChangelogs\UrlGenerator\GitlabUrlGenerator;
use Spiriit\ComposerWriteChangelogs\Version;

class GitlabUrlGeneratorTest extends TestCase
{
    private GitlabUrlGenerator $SUT;

    protected function setUp(): void
    {
        $this->SUT = new GitlabUrlGenerator('gitlab.company.org');
    }

    /**
     * @test
     *
     * @return void
     */
    public function testItSupportsGitlabUrls()
    {
        $this->assertTrue($this->SUT->supports('https://gitlab.company.org/phpunit/phpunit-mock-objects.git'));
        $this->assertTrue($this->SUT->supports('https://gitlab.company.org/symfony/console'));
        $this->assertTrue($this->SUT->supports('git@gitlab.company.org:private/repo.git'));
    }

    /**
     * @test
     *
     * @return void
     */
    public function testItDoesNotSupportNonGitlabUrls()
    {
        $this->assertFalse($this->SUT->supports('https://company.org/about-us'));
        $this->assertFalse($this->SUT->supports('https://bitbucket.org/rogoOOS/rog'));
    }

    /**
     * @test
     *
     * @return void
     */
    public function testItGeneratesCompareUrlsWithOrWithoutGitExtensionInSourceUrl()
    {
        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');

        $this->assertSame(
            'https://gitlab.company.org/acme/repo/compare/v1.0.0...v1.0.1',
            $this->SUT->generateCompareUrl(
                'https://gitlab.company.org/acme/repo',
                $versionFrom,
                'https://gitlab.company.org/acme/repo',
                $versionTo
            )
        );

        $this->assertSame(
            'https://gitlab.company.org/acme/repo/compare/v1.0.0...v1.0.1',
            $this->SUT->generateCompareUrl(
                'https://gitlab.company.org/acme/repo.git',
                $versionFrom,
                'https://gitlab.company.org/acme/repo.git',
                $versionTo
            )
        );
    }

    /**
     * @test
     *
     * @return void
     */
    public function testItGeneratesCompareUrlsWithDevVersions()
    {
        $versionFrom = new Version('v.1.0.9999999.9999999-dev', 'dev-master', 'dev-master 1234abc');
        $versionTo = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');

        $this->assertSame(
            'https://gitlab.company.org/acme/repo/compare/1234abc...v1.0.1',
            $this->SUT->generateCompareUrl(
                'https://gitlab.company.org/acme/repo.git',
                $versionFrom,
                'https://gitlab.company.org/acme/repo.git',
                $versionTo
            )
        );

        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('9999999-dev', 'dev-master', 'dev-master 6789def');

        $this->assertSame(
            'https://gitlab.company.org/acme/repo/compare/v1.0.0...6789def',
            $this->SUT->generateCompareUrl(
                'https://gitlab.company.org/acme/repo.git',
                $versionFrom,
                'https://gitlab.company.org/acme/repo.git',
                $versionTo
            )
        );

        $versionFrom = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');
        $versionTo = new Version('dev-fix/issue', 'dev-fix/issue', 'dev-fix/issue 1234abc');

        $this->assertSame(
            'https://gitlab.company.org/acme/repo/compare/v1.0.1...1234abc',
            $this->SUT->generateCompareUrl(
                'https://gitlab.company.org/acme/repo.git',
                $versionFrom,
                'https://gitlab.company.org/acme/repo.git',
                $versionTo
            )
        );
    }

    /**
     * @test
     *
     * @return void
     */
    public function testItDoesNotGenerateCompareUrlsAcrossForks()
    {
        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');

        $this->assertNull(
            $this->SUT->generateCompareUrl(
                'https://gitlab.company.org/acme1/repo',
                $versionFrom,
                'https://gitlab.company.org/acme2/repo',
                $versionTo
            )
        );
    }

    /**
     * @test
     *
     * @return void
     */
    public function testItDoesNotGenerateCompareUrlsForUnsupportedUrl()
    {
        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');

        $this->assertNull(
            $this->SUT->generateCompareUrl(
                '/home/toto/work/my-package',
                $versionFrom,
                'https://gitlab.company.org/acme2/repo',
                $versionTo
            )
        );

        $this->assertNull(
            $this->SUT->generateCompareUrl(
                'https://gitlab.company.org/acme1/repo',
                $versionFrom,
                '/home/toto/work/my-package',
                $versionTo
            )
        );
    }

    /**
     * @test
     *
     * @return void
     */
    public function testItGeneratesCompareUrlsWithSshSourceUrl()
    {
        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');

        $this->assertSame(
            'https://gitlab.company.org/acme/repo/compare/v1.0.0...v1.0.1',
            $this->SUT->generateCompareUrl(
                'git@gitlab.company.org:acme/repo.git',
                $versionFrom,
                'git@gitlab.company.org:acme/repo.git',
                $versionTo
            )
        );
    }

    /**
     * @test
     *
     * @return void
     */
    public function testItDoesNotGenerateReleaseUrlsForDevVersion()
    {
        $this->assertNull(
            $this->SUT->generateReleaseUrl(
                'https://gitlab.company.org/acme/repo',
                new Version('9999999-dev', 'dev-master', 'dev-master 1234abc')
            )
        );

        $this->assertNull(
            $this->SUT->generateReleaseUrl(
                'https://gitlab.company.org/acme/repo',
                new Version('dev-fix/issue', 'dev-fix/issue', 'dev-fix/issue 1234abc')
            )
        );
    }

    /**
     * @test
     *
     * @return void
     */
    public function testItGeneratesReleaseUrls()
    {
        $this->assertSame(
            'https://gitlab.company.org/acme/repo/tags/v1.0.1',
            $this->SUT->generateReleaseUrl(
                'https://gitlab.company.org/acme/repo',
                new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1')
            )
        );

        $this->assertSame(
            'https://gitlab.company.org/acme/repo/tags/v1.0.1',
            $this->SUT->generateReleaseUrl(
                'https://gitlab.company.org/acme/repo.git',
                new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1')
            )
        );
    }

    /**
     * @test
     *
     * @return void
     */
    public function testItGeneratesReleaseUrlWithSshSourceUrl()
    {
        $this->assertSame(
            'https://gitlab.company.org/acme/repo/tags/v1.0.1',
            $this->SUT->generateReleaseUrl(
                'git@gitlab.company.org:acme/repo.git',
                new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1')
            )
        );
    }
}
