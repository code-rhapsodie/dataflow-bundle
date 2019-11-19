<?php

namespace CodeRhapsodie\DataflowBundle\Tests\DataflowType\Writer;

use CodeRhapsodie\DataflowBundle\DataflowType\Writer\DelegateWriterInterface;
use CodeRhapsodie\DataflowBundle\DataflowType\Writer\DelegatorWriter;
use CodeRhapsodie\DataflowBundle\Exceptions\UnsupportedItemTypeException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DelegatorWriterTest extends TestCase
{
    /** @var DelegatorWriter */
    private $delegatorWriter;

    /** @var DelegateWriterInterface|MockObject */
    private $delegateInt;

    /** @var DelegateWriterInterface|MockObject */
    private $delegateString;

    /** @var DelegateWriterInterface|MockObject */
    private $delegateArray;

    protected function setUp(): void
    {
        $this->delegateInt = $this->createMock(DelegateWriterInterface::class);
        $this->delegateInt->method('supports')->willReturnCallback(function ($argument) {
            return is_int($argument);
        });

        $this->delegateString = $this->createMock(DelegateWriterInterface::class);
        $this->delegateString->method('supports')->willReturnCallback(function ($argument) {
            return is_string($argument);
        });

        $this->delegateArray = $this->createMock(DelegateWriterInterface::class);
        $this->delegateArray->method('supports')->willReturnCallback(function ($argument) {
            return is_array($argument);
        });

        $this->delegatorWriter = new DelegatorWriter();
        $this->delegatorWriter->addDelegates([
            $this->delegateInt,
            $this->delegateString,
            $this->delegateArray,
        ]);
    }

    public function testUnsupported()
    {
        $this->expectException(UnsupportedItemTypeException::class);

        $this->delegatorWriter->write(new \stdClass());
    }

    public function testStopAtFirstSupportingDelegate()
    {
        $value = 0;

        $this->delegateInt->expects($this->once())->method('supports');
        $this->delegateInt
            ->expects($this->once())
            ->method('write')
            ->with($value)
        ;
        $this->delegateString->expects($this->never())->method('supports');
        $this->delegateArray->expects($this->never())->method('supports');
        $this->delegateString->expects($this->never())->method('write');
        $this->delegateArray->expects($this->never())->method('write');

        $this->delegatorWriter->write($value);
    }

    public function testNotSupported()
    {
        $value = new \stdClass();

        $this->delegateInt
            ->expects($this->once())
            ->method('supports')
            ->with($value)
        ;
        $this->delegateString
            ->expects($this->once())
            ->method('supports')
            ->with($value)
        ;
        $this->delegateArray
            ->expects($this->once())
            ->method('supports')
            ->with($value)
        ;

        $this->assertFalse($this->delegatorWriter->supports($value));
    }

    public function testSupported()
    {
        $value = '';

        $this->delegateInt
            ->expects($this->once())
            ->method('supports')
            ->with($value)
        ;
        $this->delegateString
            ->expects($this->once())
            ->method('supports')
            ->with($value)
        ;
        $this->delegateArray
            ->expects($this->never())
            ->method('supports')
        ;

        $this->assertTrue($this->delegatorWriter->supports($value));
    }

    public function testAll()
    {
        $value = ['a'];

        $this->delegateInt
            ->expects($this->once())
            ->method('supports')
            ->with($value)
        ;
        $this->delegateString
            ->expects($this->once())
            ->method('supports')
            ->with($value)
        ;
        $this->delegateArray
            ->expects($this->once())
            ->method('supports')
            ->with($value)
        ;

        $this->delegateInt->expects($this->once())->method('prepare');
        $this->delegateString->expects($this->once())->method('prepare');
        $this->delegateArray->expects($this->once())->method('prepare');

        $this->delegateInt->expects($this->once())->method('finish');
        $this->delegateString->expects($this->once())->method('finish');
        $this->delegateArray->expects($this->once())->method('finish');

        $this->delegateInt->expects($this->never())->method('write');
        $this->delegateString->expects($this->never())->method('write');
        $this->delegateArray
            ->expects($this->once())
            ->method('write')
            ->with($value)
        ;

        $this->delegatorWriter->prepare();
        $this->delegatorWriter->write($value);
        $this->delegatorWriter->finish();
    }
}
