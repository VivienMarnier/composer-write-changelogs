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
use Spiriit\ComposerWriteChangelogs\UrlGenerator\WordPressUrlGenerator;
use Spiriit\ComposerWriteChangelogs\Version;

class WordPressUrlGeneratorTest extends TestCase
{
    private WordPressUrlGenerator $SUT;

    protected function setUp(): void
    {
        $this->SUT = new WordPressUrlGenerator();
    }

    /**
     * @test
     */
    public function testItSupportsWordpressUrls(): void
    {
        $this->assertTrue($this->SUT->supports('http://plugins.svn.wordpress.org/social-networks-auto-poster-facebook-twitter-g/'));
        $this->assertTrue($this->SUT->supports('http://plugins.svn.wordpress.org/askimet/'));
        $this->assertTrue($this->SUT->supports('http://themes.svn.wordpress.org/minimize/'));
    }

    /**
     * @test
     */
    public function testItDoesNotSupportNonWordpressUrls(): void
    {
        $this->assertFalse($this->SUT->supports('https://github.com/phpunit/phpunit-mock-objects.git'));
        $this->assertFalse($this->SUT->supports('https://github.com/symfony/console'));
        $this->assertFalse($this->SUT->supports('https://bitbucket.org/mailchimp/mandrill-api-php.git'));
        $this->assertFalse($this->SUT->supports('https://bitbucket.org/rogoOOS/rog'));
    }

    /**
     * @test
     */
    public function testItGeneratesCompareUrls(): void
    {
        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');

        $this->assertSame(
            'https://wordpress.org/plugins/askimet/changelog/',
            $this->SUT->generateCompareUrl(
                'http://plugins.svn.wordpress.org/askimet/',
                $versionFrom,
                'http://plugins.svn.wordpress.org/askimet/',
                $versionTo
            )
        );

        $this->assertSame(
            'https://themes.trac.wordpress.org/log/minimize/',
            $this->SUT->generateCompareUrl(
                'http://themes.svn.wordpress.org/minimize/',
                $versionFrom,
                'http://themes.svn.wordpress.org/minimize/',
                $versionTo
            )
        );
    }

    /**
     * @test
     */
    public function testItGeneratesReleaseUrls(): void
    {
        $this->assertNull($this->SUT->generateReleaseUrl(
            'http://themes.svn.wordpress.org/minimize/',
            new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1')
        ));
    }
}
