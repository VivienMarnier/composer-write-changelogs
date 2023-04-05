<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\tests\Util;

use PHPUnit\Framework\TestCase;
use Spiriit\ComposerWriteChangelogs\Util\FileSystemHelper;

class FileSystemHelperTest extends TestCase
{
    /**
     * @test
     *
     * @return void
     */
    public function test_it_correctly_differentiates_absolute_paths_from_relative_ones()
    {
        $this->assertTrue(FileSystemHelper::isAbsolute('/var/lib'));
        $this->assertTrue(FileSystemHelper::isAbsolute('c:\\\\var\\lib'));
        $this->assertTrue(FileSystemHelper::isAbsolute('\\var\\lib'));

        $this->assertFalse(FileSystemHelper::isAbsolute('var/lib'));
        $this->assertFalse(FileSystemHelper::isAbsolute('../var/lib'));
        $this->assertFalse(FileSystemHelper::isAbsolute(''));
    }
}
