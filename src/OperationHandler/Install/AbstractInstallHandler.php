<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\OperationHandler\Install;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Spiriit\ComposerWriteChangelogs\OperationHandler\OperationHandler;
use Spiriit\ComposerWriteChangelogs\UrlGenerator\UrlGenerator;

abstract class AbstractInstallHandler implements OperationHandler
{
    public function supports(OperationInterface $operation): bool
    {
        return $operation instanceof InstallOperation;
    }

    public function extractSourceUrl(OperationInterface $operation): ?string
    {
        if (!($operation instanceof InstallOperation)) {
            throw new \LogicException('Operation should be an instance of InstallOperation');
        }

        return $operation->getPackage()->getSourceUrl();
    }

    public function getOutput(OperationInterface $operation, UrlGenerator $urlGenerator = null): ?array
    {
        return [];
    }
}
