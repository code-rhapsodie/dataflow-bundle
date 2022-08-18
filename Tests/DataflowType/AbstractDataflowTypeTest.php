<?php

namespace CodeRhapsodie\DataflowBundle\Tests\DataflowType;

use CodeRhapsodie\DataflowBundle\DataflowType\AbstractDataflowType;
use CodeRhapsodie\DataflowBundle\DataflowType\DataflowBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AbstractDataflowTypeTest extends TestCase
{
    public function testProcess()
    {
        $label = 'Test label';
        $options = ['testOption' => 'Test value'];
        $values = [1, 2, 3];
        $testCase = $this;

        $dataflowType = new class($label, $options, $values, $testCase) extends AbstractDataflowType
        {
            public function __construct(private string $label, private array $options, private array $values, private TestCase $testCase)
            {
            }

            public function getLabel(): string
            {
                return $this->label;
            }

            protected function configureOptions(OptionsResolver $optionsResolver): void
            {
                $optionsResolver->setDefined('testOption');
            }

            protected function buildDataflow(DataflowBuilder $builder, array $options): void
            {
                $builder->setReader($this->values);
                $this->testCase->assertSame($this->options, $options);
            }
        };

        $result = $dataflowType->process($options);
        $this->assertSame($label, $result->getName());
        $this->assertSame(count($values), $result->getTotalProcessedCount());
    }
}
