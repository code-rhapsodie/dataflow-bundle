<?php
/**
 * @copy Code-Rhapsodie (c) 2019
 * Added by : cameleon at 06/11/2019 11:06
 */


namespace CodeRhapsodie\DataflowBundle\Repository;


trait InitFromDbTrait
{

    private function initDateTime(array $datas): array
    {
        foreach (static::FIELDS_TYPE as $key => $type) {
            if ($type === 'datetime' && null !== $datas[$key]) {
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
        $array = json_decode($value, true);

        return ($value === null || $array === false) ? [] : $array;
    }
}
