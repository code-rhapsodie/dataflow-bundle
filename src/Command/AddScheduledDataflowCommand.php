<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Command;

use CodeRhapsodie\DataflowBundle\Entity\ScheduledDataflow;
use CodeRhapsodie\DataflowBundle\Registry\DataflowTypeRegistryInterface;
use CodeRhapsodie\DataflowBundle\Repository\ScheduledDataflowRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @codeCoverageIgnore
 */
class AddScheduledDataflowCommand extends Command
{
    protected static $defaultName = 'code-rhapsodie:dataflow:schedule:add';

    /** @var DataflowTypeRegistryInterface */
    private $registry;
    /** @var ScheduledDataflowRepository */
    private $scheduledDataflowRepository;
    /** @var ValidatorInterface */
    private $validator;

    public function __construct(DataflowTypeRegistryInterface $registry, ScheduledDataflowRepository $scheduledDataflowRepository, ValidatorInterface $validator)
    {
        parent::__construct();

        $this->registry = $registry;
        $this->scheduledDataflowRepository = $scheduledDataflowRepository;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Create a scheduled dataflow')
            ->setHelp('The <info>%command.name%</info> allows you to create a new scheduled dataflow.')
            ->addOption('label', null, InputOption::VALUE_REQUIRED, 'Label of the scheduled dataflow')
            ->addOption('type', null, InputOption::VALUE_REQUIRED, 'Type of the scheduled dataflow (FQCN)')
            ->addOption('options', null, InputOption::VALUE_OPTIONAL, 'Options of the scheduled dataflow (ex: {"option1": "value1", "option2": "value2"})')
            ->addOption('frequency', null, InputOption::VALUE_REQUIRED, 'Frequency of the scheduled dataflow')
            ->addOption('first_run', null, InputOption::VALUE_REQUIRED, 'Date for the first run of the scheduled dataflow (Y-m-d H:i:s)')
            ->addOption('enabled', null, InputOption::VALUE_REQUIRED, 'State of the scheduled dataflow');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
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
            $options = $io->ask('What are the launch options for the scheduled dataflow? (ex: {"option1": "value1", "option2": "value2"})', json_encode([]));
        }
        $frequency = $input->getOption('frequency');
        if (!$frequency) {
            $frequency = $io->choice('What is the frequency for the scheduled dataflow?', ScheduledDataflow::AVAILABLE_FREQUENCIES);
        }
        $firstRun = $input->getOption('first_run');
        if (!$firstRun) {
            $firstRun = $io->ask('When is the first execution of the scheduled dataflow (format: Y-m-d H:i:s)?');
        }
        $enabled = $input->getOption('enabled');
        if (!$enabled) {
            $enabled = $io->confirm('Enable the scheduled dataflow?');
        }

        try {
            $newScheduledDataflow = $this->createEntityFromArray([
                'label' => $label,
                'type' => $type,
                'options' => $options,
                'frequency' => $frequency,
                'first_run' => $firstRun,
                'enabled' => $enabled,
            ]);

            $errors = $this->validator->validate($newScheduledDataflow);
            if (count($errors) > 0) {
                $io->error((string) $errors);

                return 2;
            }

            $this->scheduledDataflowRepository->save($newScheduledDataflow);
            $io->success(sprintf('New scheduled dataflow "%s" (id:%d) was created successfully.', $newScheduledDataflow->getLabel(), $newScheduledDataflow->getId()));

            return 0;
        } catch (\Exception $e) {
            $io->error(sprintf('An error occured when creating new scheduled dataflow : "%s".', $e->getMessage()));

            return 1;
        }
    }

    private function createEntityFromArray(array $input): ScheduledDataflow
    {
        $scheduledDataflow = new ScheduledDataflow();
        $scheduledDataflow->setLabel($input['label']);
        $scheduledDataflow->setDataflowType($input['type']);
        $scheduledDataflow->setOptions(json_decode($input['options'], true));
        $scheduledDataflow->setFrequency($input['frequency']);
        $scheduledDataflow->setNext(new \DateTimeImmutable($input['first_run']));
        $scheduledDataflow->setEnabled($input['enabled']);

        return $scheduledDataflow;
    }
}
