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

interface UrlGenerator
{
    public function supports(string $sourceUrl): bool;

    /**
     * Return the compare url for these versions or false if compare url is not
     * supported.
     *
     * In case the from and to source urls are different, this probably means
     * that an across fork compare url should be generated instead.
     */
    public function generateCompareUrl(?string $sourceUrlFrom, Version $versionFrom, ?string $sourceUrlTo, Version $versionTo): ?string;

    /**
     * Return the release url for the given version or false if compare url is
     * not supported.
     */
    public function generateReleaseUrl(?string $sourceUrl, Version $version): ?string;
}
