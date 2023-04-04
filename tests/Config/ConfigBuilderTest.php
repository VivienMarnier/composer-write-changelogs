<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\tests\Config;

use PHPUnit\Framework\TestCase;
use Spiriit\ComposerWriteChangelogs\Config\ConfigBuilder;

class ConfigBuilderTest extends TestCase
{
    private ConfigBuilder $SUT;

    protected function setUp(): void
    {
        $this->SUT = new ConfigBuilder();
    }

    /**
     * @test
     */
    public function testItHasADefaultSetup(): void
    {
        $extra = [];

        $config = $this->SUT->build($extra);

        $this->assertInstanceOf('Spiriit\ComposerWriteChangelogs\Config\Config', $config);
        $this->assertEmpty($config->getGitlabHosts());
        $this->assertEquals('text', $config->getOutputFileFormat());
        $this->assertNull($config->getChangelogsDirPath());

        $this->assertCount(0, $this->SUT->getWarnings());
    }

    /**
     * @test
     */
    public function testItWarnsWhenGitlabHostsIsNotAnArray(): void
    {
        $extra = [
            'gitlab-hosts' => 'gitlab.company1.com',
        ];

        $config = $this->SUT->build($extra);

        $this->assertInstanceOf('Spiriit\ComposerWriteChangelogs\Config\Config', $config);
        $this->assertEmpty($config->getGitlabHosts());

        $this->assertCount(1, $this->SUT->getWarnings());
        $this->assertStringContainsString('"gitlab-hosts" is specified but should be an array. Ignoring.', $this->SUT->getWarnings()[0]);
    }

    /**
     * @test
     */
    public function testItWarnsWhenChangelogsDirPathIsSpecifiedButEmpty(): void
    {
        $extra = [
          'changelogs-dir-path' => '',
        ];

        $config = $this->SUT->build($extra);

        $this->assertInstanceOf('Spiriit\ComposerWriteChangelogs\Config\Config', $config);
        $this->assertEmpty($config->getGitlabHosts());

        $this->assertCount(1, $this->SUT->getWarnings());
        $this->assertStringContainsString('"changelogs-dir-path" is specified but empty. Ignoring and using default changelogs dir path.', $this->SUT->getWarnings()[0]);
    }

    /**
     * @test
     */
    public function testItWarnsWhenOutputFileFormatIsInvalid(): void
    {
        $extra = [
            'output-file-format' => 'foo',
        ];

        $config = $this->SUT->build($extra);

        $this->assertInstanceOf('Spiriit\ComposerWriteChangelogs\Config\Config', $config);
        $this->assertEmpty($config->getGitlabHosts());

        $this->assertCount(1, $this->SUT->getWarnings());
        $this->assertStringContainsString('Invalid value "foo" for option "output-file-format"', $this->SUT->getWarnings()[0]);
    }

    /**
     * @test
     */
    public function testItAcceptsValidSetup(): void
    {
        $extra = [
            'gitlab-hosts' => ['gitlab.company1.com', 'gitlab.company2.com'],
            'changelogs-dir-path' => 'my/custom/path',
            'output-file-format' => 'text',
        ];

        $config = $this->SUT->build($extra);

        $this->assertInstanceOf('Spiriit\ComposerWriteChangelogs\Config\Config', $config);
        $this->assertCount(2, $config->getGitlabHosts());
        $this->assertEquals('text', $config->getOutputFileFormat());
        $this->assertEquals('my/custom/path', $config->getChangelogsDirPath());

        $this->assertCount(0, $this->SUT->getWarnings());
    }
}
