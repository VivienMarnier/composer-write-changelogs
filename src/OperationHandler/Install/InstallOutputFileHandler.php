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
use Composer\Package\PackageInterface;
use Spiriit\ComposerWriteChangelogs\Outputter\FileOutputter;
use Spiriit\ComposerWriteChangelogs\UrlGenerator\UrlGenerator;
use Spiriit\ComposerWriteChangelogs\Version;

class InstallOutputFileHandler extends AbstractInstallHandler
{
    private string $outputFormat;

    public function __construct(string $outputFormat)
    {
        $this->outputFormat = $outputFormat;
    }

    /**
     * {@inheritdoc}
     */
    public function getOutput(OperationInterface $operation, UrlGenerator $urlGenerator = null): ?array
    {
        if (!($operation instanceof InstallOperation)) {
            throw new \LogicException('Operation should be an instance of InstallOperation');
        }

        $package = $operation->getPackage();
        $version = new Version(
            $package->getVersion(),
            $package->getPrettyVersion(),
            method_exists($package, 'getFullPrettyVersion') // This method was added after composer v1.0.0-alpha10
                ? $package->getFullPrettyVersion()
                : $package->getPrettyVersion()
        );

        if (FileOutputter::JSON_FORMAT === $this->outputFormat) {
            return $this->getJsonOutput($package, $version, $urlGenerator);
        }  
            return $this->getTextOutput($package, $version, $urlGenerator);
        
    }

    private function getTextOutput(PackageInterface $package, Version $version, UrlGenerator $urlGenerator = null): array
    {
        $output = [];

        $output[] = sprintf(
            ' - %s installed in version %s',
            $package->getName(),
            $version->getCliOutput()
        );

        if ($urlGenerator) {
            $releaseUrl = $urlGenerator->generateReleaseUrl(
                $package->getSourceUrl(),
                $version
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

    private function getJsonOutput(PackageInterface $package, Version $version, UrlGenerator $urlGenerator = null): array
    {
        $output = [];

        $output['operation'] = 'install';
        $output['package'] = $package->getName();
        $output['phrasing'] = 'installed in version';
        $output['version'] = $version->getCliOutput();

        if ($urlGenerator) {
            $releaseUrl = $urlGenerator->generateReleaseUrl(
                $package->getSourceUrl(),
                $version
            );

            if (!empty($releaseUrl)) {
                $output['releaseUrl'] = $releaseUrl;
            }
        }

        return $output;
    }
}
