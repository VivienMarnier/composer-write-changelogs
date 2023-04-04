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
use Composer\Semver\Comparator;
use Spiriit\ComposerWriteChangelogs\UrlGenerator\UrlGenerator;
use Spiriit\ComposerWriteChangelogs\Version;

class UpdateHandler extends AbstractUpdateHandler
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

    /**
     * {@inheritdoc}
     */
    public function getOutput(OperationInterface $operation, UrlGenerator $urlGenerator = null): array
    {
        if (!($operation instanceof UpdateOperation)) {
            throw new \LogicException('Operation should be an instance of UpdateOperation');
        }

        $output = [];

        $initialPackage = $operation->getInitialPackage();
        $targetPackage = $operation->getTargetPackage();

        $versionFrom = new Version(
            $initialPackage->getVersion(),
            $initialPackage->getPrettyVersion(),
            method_exists($initialPackage, 'getFullPrettyVersion') // This method was added after composer v1.0.0-alpha10
                ? $initialPackage->getFullPrettyVersion()
                : $initialPackage->getPrettyVersion()
        );
        $versionTo = new Version(
            $targetPackage->getVersion(),
            $targetPackage->getPrettyVersion(),
            method_exists($targetPackage, 'getFullPrettyVersion') // This method was added after composer v1.0.0-alpha10
                ? $targetPackage->getFullPrettyVersion()
                : $targetPackage->getPrettyVersion()
        );

        $action = 'updated';

        if (Comparator::greaterThan($versionFrom->getName(), $versionTo->getName())) {
            $action = 'downgraded';
        }

        $output[] = sprintf(
            ' - <fg=green>%s</fg=green> %s from <fg=yellow>%s</fg=yellow> to <fg=yellow>%s</fg=yellow>%s',
            $initialPackage->getName(),
            $action,
            $versionFrom->getCliOutput(),
            $versionTo->getCliOutput(),
            $this->getSemverOutput($versionFrom->getName(), $versionTo->getName())
        );

        if ($urlGenerator) {
            $compareUrl = $urlGenerator->generateCompareUrl(
                $initialPackage->getSourceUrl(),
                $versionFrom,
                $targetPackage->getSourceUrl(),
                $versionTo
            );

            if (!empty($compareUrl)) {
                $output[] = sprintf(
                    '   See changes: %s',
                    $compareUrl
                );
            }

            $releaseUrl = $urlGenerator->generateReleaseUrl(
                $this->extractSourceUrl($operation),
                $versionTo
            );

            if (!empty($releaseUrl)) {
                $output[] = sprintf(
                    '   Release notes: %s',
                    $releaseUrl
                );
            }
        }

        return $output;
    }
}
