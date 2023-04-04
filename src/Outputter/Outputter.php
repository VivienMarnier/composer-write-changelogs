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

class Outputter extends AbstractOutputter
{
    public function getOutput(): string
    {
        $output = [];

        if ($this->isEmpty()) {
            $output[] = '<fg=green>No changelogs summary</fg=green>';
        } else {
            $output[] = '<fg=green>Changelogs summary:</fg=green>';

            foreach ($this->operations as $operation) {
                $this->createOperationOutput($output, $operation);
            }

            $output[] = '';
        }

        return implode("\n", $output);
    }
}
