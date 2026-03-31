<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Command;

use CodeRhapsodie\DataflowBundle\DataflowType\AutoUpdateCountInterface;
use CodeRhapsodie\DataflowBundle\Factory\ConnectionFactory;
use CodeRhapsodie\DataflowBundle\Registry\DataflowTypeRegistryInterface;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Runs one dataflow.
 *
 * @codeCoverageIgnore
 */
#[AsCommand('code-rhapsodie:dataflow:execute', 'Runs one dataflow type with provided options', help: <<<'TXT'
The <info>%command.name%</info> command runs one dataflow with the provided options.

  <info>php %command.full_name% App\Dataflow\MyDataflow '{"option1": "value1", "option2": "value2"}'</info>
TXT)]
final class ExecuteDataflowCommand implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly DataflowTypeRegistryInterface $registry, private readonly ConnectionFactory $connectionFactory, private readonly JobRepository $jobRepository)
    {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument('FQCN or alias of the dataflow type')] string $fqcn,
        #[Argument('Options for the dataflow type as a json string')] string $options = '[]',
        #[Option('Define the DBAL connection to use')] ?string $connection = null,
    ): int {
        if ($connection !== null) {
            $this->connectionFactory->setConnectionName($connection);
        }

        $options = json_decode($options, true, 512, \JSON_THROW_ON_ERROR);

        $dataflowType = $this->registry->getDataflowType($fqcn);
        if ($dataflowType instanceof AutoUpdateCountInterface) {
            $dataflowType->setRepository($this->jobRepository);
        }

        if ($dataflowType instanceof LoggerAwareInterface && isset($this->logger)) {
            $dataflowType->setLogger($this->logger);
        }

        $result = $dataflowType->process($options);

        $io->writeln('Executed: '.$result->getName());
        $io->writeln('Start time: '.$result->getStartTime()->format('Y/m/d H:i:s'));
        $io->writeln('End time: '.$result->getEndTime()->format('Y/m/d H:i:s'));
        $io->writeln('Success: '.$result->getSuccessCount());

        if ($result->hasErrors()) {
            $io->error("Errors: {$result->getErrorCount()}\nExceptions traces are available in the logs.");

            return 1;
        }

        return 0;
    }
}
