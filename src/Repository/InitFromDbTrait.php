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
        if (null === $value) {
            return [];
        }

        $array = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

        return (false === $array) ? [] : $array;
    }
}
