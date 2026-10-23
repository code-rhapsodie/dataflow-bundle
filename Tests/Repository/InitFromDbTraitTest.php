<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Tests\Repository;

use CodeRhapsodie\DataflowBundle\Repository\InitFromDbTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class InitFromDbTraitTest extends TestCase
{
    private string $defaultTimezone;

    protected function setUp(): void
    {
        $this->defaultTimezone = date_default_timezone_get();
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->defaultTimezone);
    }

    public static function timezones(): iterable
    {
        yield ['UTC'];
        yield ['Europe/Paris'];
        yield ['America/New_York'];
    }

    #[DataProvider('timezones')]
    public function testDatesAreWrittenInUtc(string $phpTimezone): void
    {
        date_default_timezone_set($phpTimezone);

        $written = $this->repository()->write(['next' => new \DateTime('2026-10-22 15:00:00', new \DateTimeZone('Europe/Paris')), 'label' => 'a']);

        $this->assertSame('2026-10-22 13:00:00', $written['next']->format('Y-m-d H:i:s'));
        $this->assertSame('a', $written['label']);
    }

    #[DataProvider('timezones')]
    public function testDatesAreReadAsUtc(string $phpTimezone): void
    {
        date_default_timezone_set($phpTimezone);

        $read = $this->repository()->read(['next' => '2026-10-22 13:00:00']);

        $this->assertSame(1792674000, $read['next']->getTimestamp());
    }

    private function repository(): object
    {
        return new class {
            use InitFromDbTrait;

            public function write(array $datas): array
            {
                return $this->convertDateTimeToUtc($datas);
            }

            public function read(array $datas): array
            {
                return $this->initDateTime($datas);
            }

            private function getFields(): array
            {
                return ['next' => 'datetime', 'label' => 'string'];
            }
        };
    }
}
