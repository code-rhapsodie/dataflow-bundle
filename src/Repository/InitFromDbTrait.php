<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Repository;

/**
 * @codeCoverageIgnore
 */
trait InitFromDbTrait
{
    abstract private function getFields(): array;

    private function initDateTime(array $datas): array
    {
        foreach ($this->getFields() as $key => $type) {
            if ($type === 'datetime' && $datas[$key] !== null) {
                $datas[$key] = new \DateTime($datas[$key], new \DateTimeZone('UTC'));
            }
        }

        return $datas;
    }

    /**
     * Dates are stored in UTC, whatever the PHP default timezone is.
     */
    private function convertDateTimeToUtc(array $datas): array
    {
        foreach ($this->getFields() as $key => $type) {
            if ($type === 'datetime' && ($datas[$key] ?? null) instanceof \DateTimeInterface) {
                $datas[$key] = \DateTime::createFromInterface($datas[$key])->setTimezone(new \DateTimeZone('UTC'));
            }
        }

        return $datas;
    }

    private function utcDateTime(string $modifier = 'now'): \DateTime
    {
        return new \DateTime($modifier, new \DateTimeZone('UTC'));
    }

    private function initArray(array $datas): array
    {
        if (!\is_array($datas['options'])) {
            $datas['options'] = $this->strToArray($datas['options']);
        }
        if (\array_key_exists('exceptions', $datas) && !\is_array($datas['exceptions'])) {
            $datas['exceptions'] = $this->strToArray($datas['exceptions']);
        }

        return $datas;
    }

    private function strToArray($value): array
    {
        if ($value === null) {
            return [];
        }

        $array = json_decode($value, true, 512, \JSON_THROW_ON_ERROR);

        return \is_array($array) ? $array : [];
    }
}
