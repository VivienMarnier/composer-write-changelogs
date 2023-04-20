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
    private static array $validOutputFormatValues = [
        FileOutputter::TEXT_FORMAT,
        FileOutputter::JSON_FORMAT,
    ];

    private array $warnings = [];

    public function build(array $extra): Config
    {
        $this->reset();

        $gitlabHosts = [];
        $changelogsDirPath = null;
        $outputFileFormat = FileOutputter::TEXT_FORMAT;
        $writeSummaryFile = true;

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

        if(array_key_exists('write-summary-file', $extra)){
            if(0 === strlen($extra['write-summary-file'])){
                $this->warnings[] = '"write-summary-file" is specified but empty. Ignoring and using default state.';
            }else if(strcmp('false', $extra['write-summary-file']) == 0){
                $writeSummaryFile = false;
            }
        }

        return new Config($gitlabHosts, $changelogsDirPath, $outputFileFormat, $writeSummaryFile);
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    private function reset(): void
    {
        $this->warnings = [];
    }

    private static function createWarningFromInvalidValue(array $extra, string $key, string $default, string $additionalMessage = ''): string
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
