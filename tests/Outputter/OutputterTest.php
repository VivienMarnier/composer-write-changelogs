<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\tests\Outputter;

use PHPUnit\Framework\TestCase;
use Spiriit\ComposerWriteChangelogs\Outputter\Outputter;
use Spiriit\ComposerWriteChangelogs\tests\resources\FakeHandler;
use Spiriit\ComposerWriteChangelogs\tests\resources\FakeOperation;
use Spiriit\ComposerWriteChangelogs\tests\resources\FakeUrlGenerator;

class OutputterTest extends TestCase
{
    private Outputter $SUT;

    private array $operationHandlers;

    private array $urlGenerators;

    protected function setUp(): void
    {
        $this->operationHandlers = [
            new FakeHandler(false, 'http://domain1', 'Output handler 1'),
            new FakeHandler(true, 'http://domain2', 'Output handler 2'),
            new FakeHandler(true, 'http://domain3', 'Output handler 3'),
        ];

        $this->urlGenerators = [
            new FakeUrlGenerator(false, '/compare-url1', '/release-url1'),
            new FakeUrlGenerator(true, '/compare-url2', '/release-url2'),
            new FakeUrlGenerator(true, '/compare-url3', '/release-url3'),
        ];

        $this->SUT = new Outputter($this->operationHandlers, $this->urlGenerators);
    }

    /**
     * @test
     */
    public function test_it_adds_operation(): void
    {
        $operation1 = new FakeOperation('');
        $this->SUT->addOperation($operation1);

        $operation2 = new FakeOperation('');
        $this->SUT->addOperation($operation2);

        $expectedOutput = <<<TEXT
<fg=green>Changelogs summary:</fg=green>

 - Output handler 2, 
   /compare-url2
   /release-url2

 - Output handler 2, 
   /compare-url2
   /release-url2

TEXT;

        $this->assertFalse($this->SUT->isEmpty());
        $this->assertSame($expectedOutput, $this->SUT->getOutput());
    }

    /**
     * @test
     */
    public function test_it_outputs_with_no_supported_url_generator(): void
    {
        $this->SUT = new Outputter($this->operationHandlers, [
            new FakeUrlGenerator(false, '', ''),
        ]);

        $this->SUT->addOperation(new FakeOperation('operation 1'));
        $this->SUT->addOperation(new FakeOperation('operation 2'));

        $expectedOutput = <<<TEXT
<fg=green>Changelogs summary:</fg=green>

 - Output handler 2, operation 1

 - Output handler 2, operation 2

TEXT;

        $this->assertFalse($this->SUT->isEmpty());
        $this->assertSame($expectedOutput, $this->SUT->getOutput());
    }

    /**
     * @test
     */
    public function test_it_outputs_with_no_supported_operation_handler(): void
    {
        $this->SUT = new Outputter([
            new FakeHandler(false, '', ''),
        ], $this->urlGenerators);

        $this->SUT->addOperation(new FakeOperation('operation 1'));
        $this->SUT->addOperation(new FakeOperation('operation 2'));

        $expectedOutput = <<<TEXT
<fg=green>Changelogs summary:</fg=green>

TEXT;

        $this->assertFalse($this->SUT->isEmpty());
        $this->assertSame($expectedOutput, $this->SUT->getOutput());
    }

    /**
     * @test
     */
    public function it_outputs_right_text(): void
    {
        $this->SUT->addOperation(new FakeOperation('operation 1'));
        $this->SUT->addOperation(new FakeOperation('operation 2'));

        $expectedOutput = <<<TEXT
<fg=green>Changelogs summary:</fg=green>

 - Output handler 2, operation 1
   /compare-url2
   /release-url2

 - Output handler 2, operation 2
   /compare-url2
   /release-url2

TEXT;

        $this->assertFalse($this->SUT->isEmpty());
        $this->assertSame($expectedOutput, $this->SUT->getOutput());
    }

    /**
     * @test
     */
    public function it_outputs_nothing_without_operation(): void
    {
        $expectedOutput = <<<TEXT
<fg=green>No changelogs summary</fg=green>
TEXT;

        $this->assertTrue($this->SUT->isEmpty());
        $this->assertSame($expectedOutput, $this->SUT->getOutput());
    }
}
