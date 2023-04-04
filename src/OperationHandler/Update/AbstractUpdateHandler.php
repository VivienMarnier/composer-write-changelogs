<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\OperationHandler\Update;

use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Spiriit\ComposerWriteChangelogs\OperationHandler\OperationHandler;
use Spiriit\ComposerWriteChangelogs\UrlGenerator\UrlGenerator;

class AbstractUpdateHandler implements OperationHandler
{
    /**
     * {@inheritdoc}
     */
    public function supports(OperationInterface $operation): bool
    {
        return $operation instanceof UpdateOperation;
    }

    /**
     * {@inheritdoc}
     */
    public function extractSourceUrl(OperationInterface $operation): ?string
    {
        if (!($operation instanceof UpdateOperation)) {
            throw new \LogicException('Operation should be an instance of UpdateOperation');
        }

        return $operation->getTargetPackage()->getSourceUrl();
    }

    public function getOutput(OperationInterface $operation, UrlGenerator $urlGenerator = null): ?array
    {
        return [];
    }

    protected function getSemverOutput(string $versionFrom, string $versionTo, bool $withTags = true): string
    {
        if (false === strpos($versionFrom, '.') && false === strpos($versionTo, '.')) {
            return '';
        }

        $versionsFrom = \explode('.', $versionFrom);
        $versionsTo = \explode('.', $versionTo);

        if (version_compare($versionsFrom[0], $versionsTo[0], '!=')) {
            return $withTags ? ' <fg=red>major</>' : ' major';
        }

        if (version_compare($versionsFrom[0], $versionsTo[0], '==') && version_compare($versionsFrom[1], $versionsTo[1], '!=')) {
            return $withTags ? ' <fg=magenta>minor</>' : ' minor';
        }

        return $withTags ? ' <fg=cyan>patch</>' : ' patch';
    }
}
