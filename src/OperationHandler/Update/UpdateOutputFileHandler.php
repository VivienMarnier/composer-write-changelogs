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
use Composer\Package\PackageInterface;
use Composer\Semver\Comparator;
use Spiriit\ComposerWriteChangelogs\Outputter\FileOutputter;
use Spiriit\ComposerWriteChangelogs\UrlGenerator\UrlGenerator;
use Spiriit\ComposerWriteChangelogs\Version;

class UpdateOutputFileHandler extends AbstractUpdateHandler
{
    private string $outputFormat;

    public function __construct(string $outputFormat)
    {
        $this->outputFormat = $outputFormat;
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

        if (FileOutputter::JSON_FORMAT === $this->outputFormat) {
            return $this->getJsonOutput($initialPackage, $targetPackage, $versionFrom, $versionTo, $action, $urlGenerator);
        } else {
            return $this->getTextOutput($initialPackage, $targetPackage, $versionFrom, $versionTo, $action, $urlGenerator);
        }

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

    private function getJsonOutput(PackageInterface $initialPackage, PackageInterface $targetPackage, Version $versionFrom, Version $versionTo, string $action, ?UrlGenerator $urlGenerator): array
    {
        $output['operation'] = 'update';
        $output['package'] = $initialPackage->getName();
        $output['action'] = $action;
        $output['phrasing'] = $action . ' from';
        $output['versionFrom'] = $versionFrom->getCliOutput();
        $output['versionTo'] = $versionTo->getCliOutput();
        $output['semver'] = trim($this->getSemverOutput($versionFrom->getName(), $versionTo->getName(), false));

        if ($urlGenerator) {
            $compareUrl = $urlGenerator->generateCompareUrl(
                $initialPackage->getSourceUrl(),
                $versionFrom,
                $targetPackage->getSourceUrl(),
                $versionTo
            );

            if (!empty($compareUrl)) {
                $output['changesUrl'] = $compareUrl;
            }

            $releaseUrl = $urlGenerator->generateReleaseUrl(
                $targetPackage->getSourceUrl(),
                $versionTo
            );

            if (!empty($releaseUrl)) {
                $output['releaseUrl'] = $releaseUrl;
            }
        }

        return $output;
    }

    private function getTextOutput(PackageInterface $initialPackage, PackageInterface $targetPackage, Version $versionFrom, Version $versionTo, string $action, ?UrlGenerator $urlGenerator): array
    {
        $output = [];

        $output[] = sprintf(
            ' - %s %s from %s to %s%s',
            $initialPackage->getName(),
            $action,
            $versionFrom->getCliOutput(),
            $versionTo->getCliOutput(),
            $this->getSemverOutput($versionFrom->getName(), $versionTo->getName(), false)
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
                $targetPackage->getSourceUrl(),
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
