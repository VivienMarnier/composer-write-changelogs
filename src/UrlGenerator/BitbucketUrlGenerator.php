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

class BitbucketUrlGenerator extends AbstractUrlGenerator
{
    /**
     * {@inheritdoc}
     */
    protected function getDomain(): string
    {
        return 'bitbucket.org';
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

        // Check if comparison across forks is needed
        if ($sourceUrlFrom !== $sourceUrlTo) {
            $repositoryFrom = $this->extractRepositoryInformation($sourceUrlFrom);
            $repositoryTo = $this->extractRepositoryInformation($sourceUrlTo);

            return sprintf(
                '%s/branches/compare/%s/%s:%s%%0D%s/%s:%s',
                $sourceUrlTo,
                $repositoryTo['user'],
                $repositoryTo['repository'],
                $this->getCompareVersion($versionTo),
                $repositoryFrom['user'],
                $repositoryFrom['repository'],
                $this->getCompareVersion($versionFrom)
            );
        }

        return sprintf(
            '%s/branches/compare/%s%%0D%s',
            $sourceUrlTo,
            $this->getCompareVersion($versionTo),
            $this->getCompareVersion($versionFrom)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function generateReleaseUrl(?string $sourceUrl, Version $version): ?string
    {
        // Releases are not supported on Bitbucket :'(
        return null;
    }
}
