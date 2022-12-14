<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\Config;

use Spiriit\ComposerWriteChangelogs\Outputter\FileOutputter;

class ConfigBuilder
{
    /** @var string[] */
    private static $validOutputFormatValues = [
        FileOutputter::TEXT_FORMAT,
        FileOutputter::JSON_FORMAT,
    ];

    /** @var string[] */
    private $warnings = [];

    /**
     * @param array $extra
     *
     * @return Config
     */
    public function build(array $extra)
    {
        $this->reset();

        $gitlabHosts = [];
        $changelogsDirPath = null;
        $outputFileFormat = FileOutputter::TEXT_FORMAT;

        if (array_key_exists('gitlab-hosts', $extra)) {
            if (!is_array($extra['gitlab-hosts'])) {
                $this->warnings[] = '"gitlab-hosts" is specified but should be an array. Ignoring.';
            } else {
                $gitlabHosts = (array) $extra['gitlab-hosts'];
            }
        }

        if (array_key_exists('changelogs-dir-path', $extra)) {
            if (0 === strlen(trim($extra['changelogs-dir-path']))) {
                $this->warnings[] = '"changelogs-dir-path" is specified but empty. Ignoring and using default changelogs dir path.';
            } else {
                $changelogsDirPath = $extra['changelogs-dir-path'];
            }
        }

        if (array_key_exists('output-file-format', $extra)) {
            if (in_array($extra['output-file-format'], self::$validOutputFormatValues, true)) {
                $outputFileFormat = $extra['output-file-format'];
            } else {
                $this->warnings[] = self::createWarningFromInvalidValue(
                    $extra,
                    'output-file-format',
                    $outputFileFormat,
                    sprintf('Valid options are "%s".', implode('", "', self::$validOutputFormatValues))
                );
            }
        }

        return new Config($gitlabHosts, $changelogsDirPath, $outputFileFormat);
    }

    /**
     * @return string[]
     */
    public function getWarnings()
    {
        return $this->warnings;
    }

    private function reset(): void
    {
        $this->warnings = [];
    }

    /**
     * @param array  $extra
     * @param string $key
     * @param mixed  $default
     * @param string $additionalMessage
     *
     * @return string
     */
    private static function createWarningFromInvalidValue(array $extra, $key, $default, $additionalMessage = '')
    {
        $warning = sprintf(
            'Invalid value "%s" for option "%s", defaulting to "%s".',
            $extra[$key],
            $key,
            $default
        );

        if ($additionalMessage) {
            $warning .= ' ' . $additionalMessage;
        }

        return $warning;
    }
}
