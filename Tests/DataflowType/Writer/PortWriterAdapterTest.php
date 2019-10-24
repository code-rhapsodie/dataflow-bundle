<?php

namespace CodeRhapsodie\DataflowBundle\Tests\DataflowType\Writer;

use CodeRhapsodie\DataflowBundle\DataflowType\Writer\PortWriterAdapter;
use PHPUnit\Framework\TestCase;

class PortWriterAdapterTest extends TestCase
{
    public function testAll()
    {
        $value = 'not an array';

        $writer = $this->getMockBuilder('\Port\Writer')
            ->setMethods(['prepare', 'finish', 'writeItem'])
            ->getMock()
        ;
        $writer
            ->expects($this->once())
            ->method('prepare')
        ;
        $writer
            ->expects($this->once())
            ->method('finish')
        ;
        $writer
            ->expects($this->once())
            ->method('writeItem')
            ->with([$value])
        ;

        $adapter = new PortWriterAdapter($writer);
        $adapter->prepare();
        $adapter->write($value);
        $adapter->finish();
    }
}
