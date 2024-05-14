<?php

namespace CodeRhapsodie\DataflowBundle\Tests\DataflowType\Writer;

use CodeRhapsodie\DataflowBundle\DataflowType\Writer\CollectionWriter;
use CodeRhapsodie\DataflowBundle\DataflowType\Writer\WriterInterface;
use CodeRhapsodie\DataflowBundle\Exceptions\UnsupportedItemTypeException;
use PHPUnit\Framework\TestCase;

class CollectionWriterTest extends TestCase
{
    public function testNotACollection()
    {
        $this->expectException(UnsupportedItemTypeException::class);

        $writer = new CollectionWriter($this->createMock(WriterInterface::class));
        $writer->write('Not an iterable');
    }

    public function testSupports()
    {
        $writer = new CollectionWriter($this->createMock(WriterInterface::class));

        $this->assertTrue($writer->supports([]));
        $this->assertTrue($writer->supports(new \ArrayIterator([])));
        $this->assertFalse($writer->supports(''));
        $this->assertFalse($writer->supports(0));
    }

    public function testAll()
    {
        $values = ['a', 'b', 'c'];

        $embeddedWriter = $this->createMock(WriterInterface::class);
        $embeddedWriter
            ->expects($this->once())
            ->method('prepare')
        ;
        $embeddedWriter
            ->expects($this->once())
            ->method('finish')
        ;
        $matcher = $this->exactly(count($values));
        $embeddedWriter
            ->expects($matcher)
            ->method('write')
            ->with($this->callback(function ($arg) use ($matcher, $values) {
                return $arg === $values[$matcher->numberOfInvocations() - 1];
            }))
        ;

        $writer = new CollectionWriter($embeddedWriter);
        $writer->prepare();
        $writer->write($values);
        $writer->finish();
    }
}
