<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Repository;

/**
 * @codeCoverageIgnore
 */
trait InitFromDbTrait
{
    private function initDateTime(array $datas): array
    {
        foreach (static::FIELDS_TYPE as $key => $type) {
            if ('datetime' === $type && null !== $datas[$key]) {
                $datas[$key] = new \DateTime($datas[$key]);
            }
        }

        return $datas;
    }

    private function initArray(array $datas): array
    {
        if (!is_array($datas['options'])) {
            $datas['options'] = $this->strToArray($datas['options']);
        }
        if (array_key_exists('exceptions', $datas) && !is_array($datas['exceptions'])) {
            $datas['exceptions'] = $this->strToArray($datas['exceptions']);
        }

        return $datas;
    }

    private function strToArray($value): array
    {
        if ($value === null) {
            return [];
        }

        $array = json_decode($value, true);

        return (false === $array) ? [] : $array;
    }
}
