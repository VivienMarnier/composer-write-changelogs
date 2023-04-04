<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\tests\resources;

use Composer\DependencyResolver\Operation\OperationInterface;

class FakeOperation implements OperationInterface
{
    private string $text;

    public function __construct(string $text)
    {
        $this->text = $text;
    }

    public function getOperationType(): string
    {
        return '';
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getJobType(): string
    {
        return '';
    }

    public function getReason(): string
    {
        return '';
    }

    public function show($lock): string
    {
        return '';
    }

    public function __toString(): string
    {
        return '';
    }
}
