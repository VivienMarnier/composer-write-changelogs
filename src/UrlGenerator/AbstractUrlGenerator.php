<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\UrlGenerator;

use Spiriit\ComposerWriteChangelogs\Version;

/**
 * @deprecated since v1.4, will be removed in v2.0. Use GitBasedUrlGenerator class instead
 */
abstract class AbstractUrlGenerator extends GitBasedUrlGenerator
{
    /**
     * Return whether the version is dev or not.
     *
     * @deprecated since v1.4, will be removed in v2.0. Use $version->isDev() instead
     */
    protected function isDevVersion(Version $version): bool
    {
        return $version->isDev();
    }
}
