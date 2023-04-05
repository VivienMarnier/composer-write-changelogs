<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\OperationHandler\Uninstall;

use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\Package\PackageInterface;
use Spiriit\ComposerWriteChangelogs\Outputter\FileOutputter;
use Spiriit\ComposerWriteChangelogs\UrlGenerator\UrlGenerator;
use Spiriit\ComposerWriteChangelogs\Version;

class UninstallOutputFileHandler extends AbstractUninstallHandler
{
    private string $outputFormat;

    public function __construct(string $outputFormat)
    {
        $this->outputFormat = $outputFormat;
    }

    public function getOutput(OperationInterface $operation, UrlGenerator $urlGenerator = null): array
    {
        if (!($operation instanceof UninstallOperation)) {
            throw new \LogicException('Operation should be an instance of UninstallOperation');
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
            return $this->getJsonOutput($package, $version);
        }  
            return $this->getTextOutput($package, $version);
        
    }

    private function getJsonOutput(PackageInterface $package, Version $version): array
    {
        $output = [];

        $output['operation'] = 'uninstall';
        $output['package'] = $package->getName();
        $output['phrasing'] = 'removed';
        $output['version'] = $version->getCliOutput();

        return $output;
    }

    private function getTextOutput(PackageInterface $package, Version $version): array
    {
        $output = [];

        $output[] = sprintf(
            ' - %s removed (installed version was %s)',
            $package->getName(),
            $version->getCliOutput()
        );

        return $output;
    }
}
