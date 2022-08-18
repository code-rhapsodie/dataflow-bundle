<?php

namespace CodeRhapsodie\DataflowBundle\Tests\DataflowType\Dataflow;

use Amp\Delayed;
use CodeRhapsodie\DataflowBundle\DataflowType\Dataflow\AMPAsyncDataflow;
use CodeRhapsodie\DataflowBundle\DataflowType\Dataflow\Dataflow;
use CodeRhapsodie\DataflowBundle\DataflowType\Writer\WriterInterface;
use PHPUnit\Framework\TestCase;

class AMPAsyncDataflowTest extends TestCase
{
    public function testProcess()
    {
        $reader = [1, 2, 3];
        $result = [];
        $dataflow = new AMPAsyncDataflow($reader, 'simple');
        $dataflow->addStep(static fn($item) => $item + 1);
        $dataflow->addStep(static function($item): \Generator {
            yield new Delayed(10); //delay 10 milliseconds
            return $item * 2;
        });
        $dataflow->addWriter(new class($result) implements WriterInterface {
            private $buffer;

            public function __construct(&$buffer) {
                $this->buffer = &$buffer;
            }

            public function prepare()
            {
            }

            public function write($item)
            {
                $this->buffer[] = $item;
            }

            public function finish()
            {
            }
        });
        $dataflow->process();

        self::assertSame([4, 6, 8], $result);
    }
}
