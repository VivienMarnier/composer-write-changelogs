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

use PHPUnit\Framework\TestCase;
use Spiriit\ComposerWriteChangelogs\Version;

class VersionTest extends TestCase
{
    private Version $SUT;

    /**
     * @test
     *
     * @return void
     */
    public function testItKeepVersionFormats()
    {
        $this->SUT = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');

        $this->assertSame('v1.0.0.0', $this->SUT->getName());
        $this->assertSame('v1.0.0', $this->SUT->getPretty());
        $this->assertSame('v1.0.0', $this->SUT->getFullPretty());

        $this->SUT = new Version('v.1.0.9999999.9999999-dev', 'dev-master', 'dev-master 1234abc');

        $this->assertSame('v.1.0.9999999.9999999-dev', $this->SUT->getName());
        $this->assertSame('dev-master', $this->SUT->getPretty());
        $this->assertSame('dev-master 1234abc', $this->SUT->getFullPretty());
    }

    /**
     * @test
     *
     * @return void
     */
    public function testItDetectsDevVersion()
    {
        $this->SUT = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');

        $this->assertFalse($this->SUT->isDev());

        $this->SUT = new Version('v.1.0.9999999.9999999-dev', 'dev-master', 'dev-master 1234abc');

        $this->assertTrue($this->SUT->isDev());

        $this->SUT = new Version('dev-fix/issue', 'dev-fix/issue', 'dev-fix/issue 1234abc');

        $this->assertTrue($this->SUT->isDev());
    }
}
