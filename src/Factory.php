<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs;

use Spiriit\ComposerWriteChangelogs\Outputter\FileOutputter;
use Spiriit\ComposerWriteChangelogs\Outputter\Outputter;

class Factory
{
    public static function createOperationHandlers(): array
    {
        return [
            new OperationHandler\Install\InstallHandler(),
            new OperationHandler\Update\UpdateHandler(),
            new OperationHandler\Uninstall\UninstallHandler(),
        ];
    }

    public static function createOperationOutputFileHandlers(string $outputFormat): array
    {
        return [
            new OperationHandler\Install\InstallOutputFileHandler($outputFormat),
            new OperationHandler\Update\UpdateOutputFileHandler($outputFormat),
            new OperationHandler\Uninstall\UninstallOutputFileHandler($outputFormat),
        ];
    }

    public static function createUrlGenerators(array $gitlabHosts = []): array
    {
        $hosts = [
            new UrlGenerator\GithubUrlGenerator(),
            new UrlGenerator\BitbucketUrlGenerator(),
            new UrlGenerator\WordPressUrlGenerator(),
        ];

        foreach ($gitlabHosts as $gitlabHost) {
            $hosts[] = new UrlGenerator\GitlabUrlGenerator($gitlabHost);
        }

        return $hosts;
    }

    public static function createOutputter(array $gitlabHosts = []): Outputter
    {
        return new Outputter(
            self::createOperationHandlers(),
            self::createUrlGenerators($gitlabHosts)
        );
    }

    public static function createFileOutputter(string $outputFormat, array $gitlabHosts = []): FileOutputter
    {
        return new FileOutputter(
            self::createOperationOutputFileHandlers($outputFormat),
            self::createUrlGenerators($gitlabHosts)
        );
    }
}
