<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class AsDateInterval implements CastsAttributes
{
    /**
     * @param Model $model
     * @param string $key
     * @param string $value ISO-8601
     * @param array<string, mixed> $attributes
     * @return \DateInterval
     * @throws \Exception
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): \DateInterval
    {
        return new \DateInterval($value);
    }

    /**
     * @param Model $model
     * @param string $key
     * @param \DateInterval $value
     * @param array $attributes
     * @return string
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if (! $value instanceof \DateInterval) throw new \TypeError();

        $P = $value->invert ? '-P' : 'P';

        foreach (['y', 'm', 'd'] as $period) {
            if ($value->$period) $P .= $value->$period . strtoupper($period);
        }

        $T = 'T';

        foreach(['h', 'i', 's', 'f'] as $time) {
            if ($value->$time) $T .= $value->$time . strtoupper($time);
        }

        if ($T === 'T') $T = '';

        return $P . $T;
    }
}
