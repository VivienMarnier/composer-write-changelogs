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

abstract class GitBasedUrlGenerator implements UrlGenerator
{
    public const REGEX_USER = '(?P<user>[^/]+)';
    public const REGEX_REPOSITORY = '(?P<repository>[^/]+)';

    /**
     * Returns the domain of the service, like "example.org".
     */
    abstract protected function getDomain(): string;

    /**
     * {@inheritdoc}
     */
    public function supports(string $sourceUrl): bool
    {
        return false !== strpos($sourceUrl, $this->getDomain());
    }

    /**
     * Generates the canonical http url for a repository.
     *
     * It ensures there is no .git part in http url. It also supports ssh urls
     * by converting them in their http equivalent format.
     */
    protected function generateBaseUrl(?string $sourceUrl): string
    {
        if($sourceUrl != null){
            if ($this->isSshUrl($sourceUrl)) {
                return $this->transformSshUrlIntoHttp($sourceUrl);
            }

            $sourceUrl = parse_url($sourceUrl);

            if(is_array($sourceUrl) && isset($sourceUrl['path']) && isset($sourceUrl['scheme']) && isset($sourceUrl['host'])){
                $pos = strrpos($sourceUrl['path'], '.git');
                $length = strrpos($sourceUrl['path'], '.git');

                if(!$length)
                    $length = null;

                return sprintf(
                    '%s://%s%s',
                    $sourceUrl['scheme'],
                    $sourceUrl['host'],
                    false === $pos ? $sourceUrl['path'] : substr($sourceUrl['path'], 0, $length)
                );
            } 
                return "";
            

        } 
            return "";
        
    }

    /**
     * Get the version to use for the compare url.
     *
     * For dev versions, it returns the commit short hash in full pretty version.
     */
    protected function getCompareVersion(Version $version): string
    {
        if ($version->isDev()) {
            return substr(
                $version->getFullPretty(),
                strlen($version->getPretty()) + 1
            );
        }

        return $version->getPretty();
    }

    /**
     * Extracts information like user and repository from the http url.
     */
    protected function extractRepositoryInformation(string $sourceUrl): array
    {
        $pattern = '#' . $this->getDomain() . '/' . self::REGEX_USER . '/' . self::REGEX_REPOSITORY . '#';

        preg_match($pattern, $sourceUrl, $matches);

        if (!isset($matches['user']) || !isset($matches['repository'])) {
            throw new \LogicException(
                sprintf('Unrecognized url format for %s ("%s")', $this->getDomain(), $sourceUrl)
            );
        }

        return [
            'user' => $matches['user'],
            'repository' => $matches['repository'],
        ];
    }

    /**
     * Returns whether an url uses a ssh git protocol.
     */
    private function isSshUrl(string $url): bool
    {
        return false !== strpos($url, 'git@');
    }

    /**
     * Transform an ssh git url into a http one.
     */
    private function transformSshUrlIntoHttp(string $url): string
    {
        $pattern = '#git@' . $this->getDomain() . ':' . self::REGEX_USER . '/' . self::REGEX_REPOSITORY . '.git$#';

        if (preg_match($pattern, $url, $matches)) {
            return sprintf(
                'https://%s/%s/%s',
                $this->getDomain(),
                $matches['user'],
                $matches['repository']
            );
        }

        return $url;
    }
}
