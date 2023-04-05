<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\tests;

use Composer\Composer;
use Composer\Config;
use Composer\Package\RootPackage;
use PHPUnit\Framework\TestCase;
use Spiriit\ComposerWriteChangelogs\Config\ConfigLocator;

class ConfigLocatorTest extends TestCase
{
    private ?string $localConfigPath;

    private ?string $globalConfigPath;

    private ConfigLocator $SUT;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->localConfigPath = realpath(__DIR__ . '/../fixtures/local') ? realpath(__DIR__ . '/../fixtures/local') : null;
        $this->globalConfigPath = realpath(__DIR__ . '/../fixtures/home') ? realpath(__DIR__ . '/../fixtures/home') : null;

        $config = new Config(false, $this->localConfigPath);
        $config->merge([
            'config' => [
                'home' => $this->globalConfigPath,
            ],
        ]);

        $package = new RootPackage('my/project', '1.0.0', '1.0.0');
        $package->setExtra([
            'my-local-config' => [
                'foo' => 'bar',
            ],
        ]);

        $composer = new Composer();
        $composer->setConfig($config);
        $composer->setPackage($package);

        $this->SUT = new ConfigLocator($composer);
    }

    /**
     * @test
     */
    public function test_it_locates_local_config(): void
    {
        $key = 'my-local-config';

        $this->assertTrue($this->SUT->locate($key));

        $this->assertSame($this->localConfigPath, $this->SUT->getPath($key));
        $this->assertSame(['foo' => 'bar'], $this->SUT->getConfig($key));
    }

    /**
     * @test
     */
    public function test_it_locates_global_config(): void
    {
        $key = 'my-global-config';

        $this->assertTrue($this->SUT->locate($key));

        $this->assertSame($this->globalConfigPath, $this->SUT->getPath($key));
        $this->assertSame(['bar' => 'foo'], $this->SUT->getConfig($key));
    }

    /**
     * @test
     */
    public function test_it_does_not_locate_non_existing_config(): void
    {
        $key = 'my-non-existing-config';

        $this->assertFalse($this->SUT->locate($key));

        $this->assertNull($this->SUT->getPath($key));
        $this->assertSame([], $this->SUT->getConfig($key));
    }
}
