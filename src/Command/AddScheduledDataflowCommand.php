<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Command;

use CodeRhapsodie\DataflowBundle\Entity\ScheduledDataflow;
use CodeRhapsodie\DataflowBundle\Factory\ConnectionFactory;
use CodeRhapsodie\DataflowBundle\Registry\DataflowTypeRegistryInterface;
use CodeRhapsodie\DataflowBundle\Repository\ScheduledDataflowRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @codeCoverageIgnore
 */
#[AsCommand('code-rhapsodie:dataflow:schedule:add', 'Create a scheduled dataflow')]
class AddScheduledDataflowCommand extends Command
{
    public function __construct(private DataflowTypeRegistryInterface $registry, private ScheduledDataflowRepository $scheduledDataflowRepository, private ValidatorInterface $validator, private ConnectionFactory $connectionFactory)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setHelp('The <info>%command.name%</info> allows you to create a new scheduled dataflow.')
            ->addOption('label', null, InputOption::VALUE_REQUIRED, 'Label of the scheduled dataflow')
            ->addOption('type', null, InputOption::VALUE_REQUIRED, 'Type of the scheduled dataflow (FQCN)')
            ->addOption('options', null, InputOption::VALUE_OPTIONAL,
                'Options of the scheduled dataflow (ex: {"option1": "value1", "option2": "value2"})')
            ->addOption('frequency', null, InputOption::VALUE_REQUIRED, 'Frequency of the scheduled dataflow')
            ->addOption('first_run', null, InputOption::VALUE_REQUIRED, 'Date for the first run of the scheduled dataflow (Y-m-d H:i:s)')
            ->addOption('enabled', null, InputOption::VALUE_REQUIRED, 'State of the scheduled dataflow')
            ->addOption('connection', null, InputOption::VALUE_REQUIRED, 'Define the DBAL connection to use');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (null !== $input->getOption('connection')) {
            $this->connectionFactory->setConnectionName($input->getOption('connection'));
        }
        $choices = [];
        $typeMapping = [];
        foreach ($this->registry->listDataflowTypes() as $fqcn => $dataflowType) {
            $choices[] = $dataflowType->getLabel();
            $typeMapping[$dataflowType->getLabel()] = $fqcn;
        }

        $io = new SymfonyStyle($input, $output);
        $label = $input->getOption('label');
        if (!$label) {
            $label = $io->ask('What is the scheduled dataflow label?');
        }
        $type = $input->getOption('type');
        if (!$type) {
            $selectedType = $io->choice('What is the scheduled dataflow type?', $choices);
            $type = $typeMapping[$selectedType];
        }
        $options = $input->getOption('options');
        if (!$options) {
            $options = $io->ask('What are the launch options for the scheduled dataflow? (ex: {"option1": "value1", "option2": "value2"})',
                json_encode([]));
        }
        $frequency = $input->getOption('frequency');
        if (!$frequency) {
            $frequency = $io->choice('What is the frequency for the scheduled dataflow?',
                ScheduledDataflow::AVAILABLE_FREQUENCIES);
        }
        $firstRun = $input->getOption('first_run');
        if (!$firstRun) {
            $firstRun = $io->ask('When is the first execution of the scheduled dataflow (format: Y-m-d H:i:s)?');
        }
        $enabled = $input->getOption('enabled');
        if (!$enabled) {
            $enabled = $io->confirm('Enable the scheduled dataflow?');
        }

        $newScheduledDataflow = ScheduledDataflow::createFromArray([
            'id' => null,
            'label' => $label,
            'dataflow_type' => $type,
            'options' => json_decode($options, true, 512, JSON_THROW_ON_ERROR),
            'frequency' => $frequency,
            'next' => new \DateTime($firstRun),
            'enabled' => $enabled,
        ]);

        $errors = $this->validator->validate($newScheduledDataflow);
        if (count($errors) > 0) {
            $io->error((string) $errors);

            return 2;
        }

        $this->scheduledDataflowRepository->save($newScheduledDataflow);
        $io->success(sprintf('New scheduled dataflow "%s" (id:%d) was created successfully.',
            $newScheduledDataflow->getLabel(), $newScheduledDataflow->getId()));

        return 0;
    }
}
