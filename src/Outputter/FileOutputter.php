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
use Exception;

class FileOutputter extends AbstractOutputter
{
    public const TEXT_FORMAT = 'text';

    public const JSON_FORMAT = 'json';

    /**
     * @throws Exception
     */
    public function getOutput(string $outputFormat): string
    {
        $output = [];

        if ($this->isEmpty()) {
            if (self::TEXT_FORMAT === $outputFormat) {
                $output[] = 'No changelogs summary';
            }
        } else {
            if (self::JSON_FORMAT === $outputFormat) {
                $output = $this->handleJsonOutput($output);
                if (!$output = json_encode($output, JSON_UNESCAPED_SLASHES)) {
                    throw new Exception('The output could not be formatted.');
                }

                return $output;
            } else {
                $output[] = 'Changelogs summary:';
                foreach ($this->operations as $operation) {
                    $this->createOperationOutput($output, $operation);
                }
                $output[] = '';
            }
        }

        return implode("\n", $output);
    }

    protected function createOperationJsonOutput(OperationInterface $operation): ?array
    {
        $operationHandler = $this->getOperationHandler($operation);

        if (!$operationHandler) {
            return [];
        }

        $urlGenerator = $this->getUrlGenerator(
            $operationHandler->extractSourceUrl($operation)
        );

        return $operationHandler->getOutput($operation, $urlGenerator);
    }

    private function handleJsonOutput(array $output): array
    {
        $i = 0;
        foreach ($this->operations as $operation) {
            $output[$i] = $this->createOperationJsonOutput($operation);
            ++$i;
        }

        return $output;
    }
}
