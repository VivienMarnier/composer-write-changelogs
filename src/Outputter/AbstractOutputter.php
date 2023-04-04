<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\Outputter;

use Composer\DependencyResolver\Operation\OperationInterface;
use Spiriit\ComposerWriteChangelogs\OperationHandler\OperationHandler;
use Spiriit\ComposerWriteChangelogs\UrlGenerator\UrlGenerator;

abstract class AbstractOutputter
{
    protected array $operationHandlers;

    protected array $urlGenerators;

    protected array $operations;

    public function __construct(array $operationHandlers, array $urlGenerators)
    {
        $this->urlGenerators = $urlGenerators;
        $this->operationHandlers = $operationHandlers;
        $this->operations = [];
    }

    public function addOperation(OperationInterface $operation): void
    {
        $this->operations[] = $operation;
    }

    public function isEmpty(): bool
    {
        return empty($this->operations);
    }

    protected function createOperationOutput(array &$output, OperationInterface $operation): void
    {
        $operationHandler = $this->getOperationHandler($operation);

        if (!$operationHandler) {
            return;
        }

        $output[] = '';

        $urlGenerator = $this->getUrlGenerator(
            $operationHandler->extractSourceUrl($operation)
        );

        $operationOutput = $operationHandler->getOutput($operation, $urlGenerator);
        $output = $operationOutput ? array_merge($output, $operationOutput) : $output;
    }

    protected function getOperationHandler(OperationInterface $operation): ?OperationHandler
    {
        foreach ($this->operationHandlers as $operationHandler) {
            if ($operationHandler->supports($operation)) {
                return $operationHandler;
            }
        }

        return null;
    }

    protected function getUrlGenerator(?string $sourceUrl): ?UrlGenerator
    {
        foreach ($this->urlGenerators as $urlGenerator) {
            if ($urlGenerator->supports($sourceUrl)) {
                return $urlGenerator;
            }
        }

        return null;
    }
}
