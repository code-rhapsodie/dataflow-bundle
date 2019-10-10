<?php

namespace CodeRhapsodie\DataflowBundle\Tests\Registry;

use CodeRhapsodie\DataflowBundle\Exceptions\UnknownDataflowTypeException;
use CodeRhapsodie\DataflowBundle\Registry\DataflowTypeRegistry;
use CodeRhapsodie\DataflowBundle\DataflowType\DataflowTypeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataflowTypeRegistryTest extends TestCase
{
    /** @var DataflowTypeRegistry */
    private $registry;

    protected function setUp(): void
    {
        $this->registry = new DataflowTypeRegistry();
    }

    public function testEverything()
    {
        $alias1 = 'alias1';
        $alias2 = 'alias2';

        /** @var MockObject|DataflowTypeInterface $type */
        $type = $this->createMock(DataflowTypeInterface::class);
        $type
            ->expects($this->once())
            ->method('getAliases')
            ->willReturn([$alias1, $alias2])
        ;

        $this->registry->registerDataflowType($type);

        $this->assertSame($type, $this->registry->getDataflowType(get_class($type)));
        $this->assertSame($type, $this->registry->getDataflowType($alias1));
        $this->assertSame($type, $this->registry->getDataflowType($alias2));
        $this->assertContains($type, $this->registry->listDataflowTypes());
    }

    public function testUnknown()
    {
        $this->expectException(UnknownDataflowTypeException::class);
        $this->registry->getDataflowType('unknown');
    }
}
