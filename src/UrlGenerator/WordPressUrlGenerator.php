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
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
class WordPressUrlGenerator implements UrlGenerator
{
    public const DOMAIN = 'svn.wordpress.org';

    public function supports(string $sourceUrl): bool
    {
        return false !== strpos($sourceUrl, self::DOMAIN);
    }

    /**
     * {@inheritdoc}
     */
    public function generateCompareUrl(?string $sourceUrlFrom, Version $versionFrom, ?string $sourceUrlTo, Version $versionTo): ?string
    {
        if ((!is_null($sourceUrlFrom) && !$this->supports($sourceUrlFrom)) || (!is_null($sourceUrlTo) && !$this->supports($sourceUrlTo))) {
            return null;
        }

        if (preg_match('#plugins.svn.wordpress.org/(.*)/#', $sourceUrlTo, $matches)) {
            $plugin = $matches[1];

            return sprintf('https://wordpress.org/plugins/%s/changelog/', $plugin);
        }

        if (preg_match('#themes.svn.wordpress.org/(.*)/#', $sourceUrlTo, $matches)) {
            $theme = $matches[1];

            return sprintf('https://themes.trac.wordpress.org/log/%s/', $theme);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function generateReleaseUrl(?string $sourceUrl, Version $version): ?string
    {
        return null;
    }
}
