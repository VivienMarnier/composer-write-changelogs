<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\tests\resources;

use Spiriit\ComposerWriteChangelogs\UrlGenerator\UrlGenerator;
use Spiriit\ComposerWriteChangelogs\Version;

class FakeUrlGenerator implements UrlGenerator
{
    private bool $supports;

    private ?string $compareUrl;

    private ?string $releaseUrl;

    public function __construct(bool $supports, ?string $compareUrl, ?string $releaseUrl)
    {
        $this->supports = $supports;
        $this->compareUrl = $compareUrl;
        $this->releaseUrl = $releaseUrl;
    }

    public function supports(?string $sourceUrl): bool
    {
        return $this->supports;
    }

    /**
     * {@inheritdoc}
     */
    public function generateCompareUrl(?string $sourceUrlFrom, Version $versionFrom, ?string $sourceUrlTo, Version $versionTo): ?string
    {
        return $this->compareUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function generateReleaseUrl(?string $sourceUrl, Version $version): ?string
    {
        return $this->releaseUrl;
    }
}
