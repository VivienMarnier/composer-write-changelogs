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

class GitlabUrlGenerator extends AbstractUrlGenerator
{
    private string $host;

    public function __construct(string $host)
    {
        $this->host = $host;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDomain(): string
    {
        return $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function generateCompareUrl(?string $sourceUrlFrom, Version $versionFrom, ?string $sourceUrlTo, Version $versionTo): ?string
    {
        // Check if both urls come from the supported domain
        // It avoids problems when one url is from another domain or is local
        if ((!is_null($sourceUrlFrom) && !$this->supports($sourceUrlFrom)) || (!is_null($sourceUrlTo) && !$this->supports($sourceUrlTo))) {
            return null;
        }

        $sourceUrlFrom = $this->generateBaseUrl($sourceUrlFrom);
        $sourceUrlTo = $this->generateBaseUrl($sourceUrlTo);

        if ($sourceUrlFrom !== $sourceUrlTo) {
            // Comparison across forks is not supported
            return null;
        }

        return sprintf(
            '%s/compare/%s...%s',
            $sourceUrlTo,
            $this->getCompareVersion($versionFrom),
            $this->getCompareVersion($versionTo)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function generateReleaseUrl(?string $sourceUrl, Version $version): ?string
    {
        if ($version->isDev()) {
            return null;
        }

        return sprintf(
            '%s/tags/%s',
            $this->generateBaseUrl($sourceUrl),
            $version->getPretty()
        );
    }
}
