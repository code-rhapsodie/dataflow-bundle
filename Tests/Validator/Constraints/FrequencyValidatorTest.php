<?php

namespace CodeRhapsodie\DataflowBundle\Tests\Validator\Constraints;

use CodeRhapsodie\DataflowBundle\Validator\Constraints\Frequency;
use CodeRhapsodie\DataflowBundle\Validator\Constraints\FrequencyValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class FrequencyValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        return new FrequencyValidator();
    }

    /**
     * @dataProvider getValidValues
     */
    public function testValidValues($value)
    {
        $this->validator->validate($value, new Frequency());

        $this->assertNoViolation();
    }

    public function getValidValues()
    {
        return [
            ['3 days'],
            ['2 weeks'],
            ['1 month'],
            ['first sunday'],
        ];
    }

    public function testInvalidValue()
    {
        $constraint = new Frequency([
            'invalidMessage' => 'testMessage',
        ]);

        $this->validator->validate('wrong value', $constraint);

        $this->buildViolation('testMessage')
            ->setParameter('{{ string }}', 'wrong value')
            ->assertRaised()
        ;
    }

    /**
     * @dataProvider getNegativeValues
     */
    public function testNegativeIntervals($value)
    {
        $constraint = new Frequency([
            'negativeIntervalMessage' => 'testMessage',
        ]);

        $this->validator->validate($value, $constraint);

        $this->buildViolation('testMessage')
            ->setParameter('{{ string }}', $value)
            ->assertRaised()
        ;
    }

    public function getNegativeValues()
    {
        return [
            ['now'],
            ['-1 day'],
            ['last month'],
        ];
    }
}
