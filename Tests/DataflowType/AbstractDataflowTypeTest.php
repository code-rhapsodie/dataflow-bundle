<?php

namespace CodeRhapsodie\DataflowBundle\Tests\DataflowType;

use CodeRhapsodie\DataflowBundle\DataflowType\AbstractDataflowType;
use CodeRhapsodie\DataflowBundle\DataflowType\DataflowBuilder;
use PHPUnit\Framework\Constraint\IsIdentical;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AbstractDataflowTypeTest extends TestCase
{
    public function testProcess()
    {
        $label = 'Test label';
        $options = ['testOption' => 'Test value'];
        $values = [1, 2, 3];

        $dataflowType = new class($label, $options, $values) extends AbstractDataflowType
        {
            private $label;
            private $options;
            private $values;

            public function __construct(string $label, array $options, array $values)
            {
                $this->label = $label;
                $this->options = $options;
                $this->values = $values;
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
                (new IsIdentical($this->options))->evaluate($options);
            }
        };

        $result = $dataflowType->process($options);
        $this->assertSame($label, $result->getName());
        $this->assertSame(count($values), $result->getTotalProcessedCount());
    }
}
