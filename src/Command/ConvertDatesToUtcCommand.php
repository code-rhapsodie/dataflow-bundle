<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Command;

use CodeRhapsodie\DataflowBundle\Factory\ConnectionFactory;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use CodeRhapsodie\DataflowBundle\Repository\ScheduledDataflowRepository;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @codeCoverageIgnore
 */
#[AsCommand('code-rhapsodie:dataflow:convert-dates-to-utc', 'Convert the stored dates to UTC', help: <<<'TXT'
The <info>%command.name%</info> command converts the dates stored in the dataflow tables to UTC,
from the PHP default timezone or from the timezone given with <comment>--timezone</comment>.

The jobs <comment>start_time</comment> and <comment>end_time</comment> are always converted.
The scheduled dataflows <comment>next</comment> and the jobs <comment>requested_date</comment> are converted only
if requested with <comment>--next</comment> and <comment>--requested-date</comment>: they may have been created by
another tool (for example the ibexa-dataflow-bundle forms), which may not store them in the PHP timezone.

Dates are stored without timezone: this command is NOT idempotent, run it only once and make a backup of the tables first.
Use <comment>--dry-run</comment> to see what would be changed.
TXT)]
final readonly class ConvertDatesToUtcCommand
{
    private const BATCH_SIZE = 1000;

    public function __construct(private ConnectionFactory $connectionFactory)
    {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Option('Timezone the dates are currently stored in (default: the PHP default timezone)')] ?string $timezone = null,
        #[Option('Also convert the scheduled dataflows "next" date')] bool $next = false,
        #[Option('Also convert the jobs "requested_date"')] bool $requestedDate = false,
        #[Option('Only display what would be changed')] bool $dryRun = false,
        #[Option('Define the DBAL connection to use')] ?string $connection = null,
    ): int {
        if ($connection !== null) {
            $this->connectionFactory->setConnectionName($connection);
        }

        try {
            $from = new \DateTimeZone($timezone ?? date_default_timezone_get());
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return Command::INVALID;
        }

        if ($from->getName() === 'UTC') {
            $io->warning('The source timezone is UTC, nothing to do.');

            return Command::SUCCESS;
        }

        $jobColumns = ['start_time', 'end_time'];
        if ($requestedDate) {
            $jobColumns[] = 'requested_date';
        }
        $tables = [JobRepository::TABLE_NAME => $jobColumns];
        if ($next) {
            $tables[ScheduledDataflowRepository::TABLE_NAME] = ['next'];
        }

        $io->note(\sprintf('Dates will be converted from "%s" to "UTC"%s.', $from->getName(), $dryRun ? ' (dry run)' : ''));

        if (!$dryRun && !$io->confirm('The conversion is not idempotent: have you made a backup and do you want to continue?', false)) {
            return Command::FAILURE;
        }

        $db = $this->connectionFactory->getConnection();
        foreach ($tables as $table => $columns) {
            $total = $this->convertTable($db, $table, $columns, $from, $dryRun);
            $io->writeln(\sprintf('%s (%s): %d row(s) %s.', $table, implode(', ', $columns), $total, $dryRun ? 'would be updated' : 'updated'));
        }

        return Command::SUCCESS;
    }

    /**
     * @param string[] $columns
     */
    private function convertTable(Connection $db, string $table, array $columns, \DateTimeZone $from, bool $dryRun): int
    {
        $utc = new \DateTimeZone('UTC');
        $select = implode(', ', ['id', ...$columns]);
        $lastId = 0;
        $updated = 0;

        do {
            $rows = $db->fetchAllAssociative(
                \sprintf('SELECT %s FROM %s WHERE id > ? ORDER BY id ASC LIMIT %d', $select, $table, self::BATCH_SIZE),
                [$lastId],
            );

            $db->beginTransaction();
            try {
                foreach ($rows as $row) {
                    $lastId = (int) $row['id'];
                    $changes = [];
                    foreach ($columns as $column) {
                        if ($row[$column] === null) {
                            continue;
                        }
                        $converted = (new \DateTimeImmutable($row[$column], $from))->setTimezone($utc)->format('Y-m-d H:i:s');
                        if ($converted !== $row[$column]) {
                            $changes[$column] = $converted;
                        }
                    }

                    if ($changes === []) {
                        continue;
                    }
                    ++$updated;
                    if (!$dryRun) {
                        $db->update($table, $changes, ['id' => $lastId]);
                    }
                }
                $db->commit();
            } catch (\Throwable $e) {
                $db->rollBack();

                throw $e;
            }
        } while (\count($rows) === self::BATCH_SIZE);

        return $updated;
    }
}
