<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Command;

use CodeRhapsodie\DataflowBundle\Entity\ScheduledDataflow;
use CodeRhapsodie\DataflowBundle\Factory\ConnectionFactory;
use CodeRhapsodie\DataflowBundle\Registry\DataflowTypeRegistryInterface;
use CodeRhapsodie\DataflowBundle\Repository\ScheduledDataflowRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @codeCoverageIgnore
 */
#[AsCommand('code-rhapsodie:dataflow:schedule:add', 'Create a scheduled dataflow', help: <<<'TXT'
The <info>%command.name%</info> allows you to create a new scheduled dataflow.
TXT)]
final readonly class AddScheduledDataflowCommand
{
    public function __construct(private DataflowTypeRegistryInterface $registry, private ScheduledDataflowRepository $scheduledDataflowRepository, private ValidatorInterface $validator, private ConnectionFactory $connectionFactory)
    {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Option('Label of the scheduled dataflow')] ?string $label = null,
        #[Option('Type of the scheduled dataflow (FQCN)')] ?string $type = null,
        #[Option('Options of the scheduled dataflow (ex: {"option1": "value1", "option2": "value2"})')] ?string $options = null,
        #[Option('Frequency of the scheduled dataflow')] ?string $frequency = null,
        #[Option('Date for the first run of the scheduled dataflow (Y-m-d H:i:s)')] ?string $firstRun = null,
        #[Option('State of the scheduled dataflow')] ?bool $enabled = null,
        #[Option('Define the DBAL connection to use')] ?string $connection = null,
    ): int {
        if ($connection !== null) {
            $this->connectionFactory->setConnectionName($connection);
        }
        $choices = [];
        $typeMapping = [];
        foreach ($this->registry->listDataflowTypes() as $fqcn => $dataflowType) {
            $choices[] = $dataflowType->getLabel();
            $typeMapping[$dataflowType->getLabel()] = $fqcn;
        }

        if (!$label) {
            $label = $io->ask('What is the scheduled dataflow label?');
        }
        if (!$type) {
            $selectedType = $io->choice('What is the scheduled dataflow type?', $choices);
            $type = $typeMapping[$selectedType];
        }
        if (!$options) {
            $options = $io->ask(
                'What are the launch options for the scheduled dataflow? (ex: {"option1": "value1", "option2": "value2"})',
                json_encode([])
            );
        }
        if (!$frequency) {
            $frequency = $io->choice(
                'What is the frequency for the scheduled dataflow?',
                ScheduledDataflow::AVAILABLE_FREQUENCIES
            );
        }
        if (!$firstRun) {
            $firstRun = $io->ask('When is the first execution of the scheduled dataflow (format: Y-m-d H:i:s)?');
        }
        if ($enabled === null) {
            $enabled = $io->confirm('Enable the scheduled dataflow?');
        }

        $newScheduledDataflow = ScheduledDataflow::createFromArray([
            'id' => null,
            'label' => $label,
            'dataflow_type' => $type,
            'options' => json_decode($options, true, 512, \JSON_THROW_ON_ERROR),
            'frequency' => $frequency,
            'next' => new \DateTime($firstRun),
            'enabled' => $enabled,
        ]);

        $errors = $this->validator->validate($newScheduledDataflow);
        if (\count($errors) > 0) {
            $io->error((string) $errors);

            return 2;
        }

        $this->scheduledDataflowRepository->save($newScheduledDataflow);
        $io->success(\sprintf(
            'New scheduled dataflow "%s" (id:%d) was created successfully.',
            $newScheduledDataflow->getLabel(),
            $newScheduledDataflow->getId()
        ));

        return 0;
    }
}
