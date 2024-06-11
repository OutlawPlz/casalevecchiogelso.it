<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class AsDateInterval implements CastsAttributes
{
    /**
     * @param Model $model
     * @param string $key
     * @param string $value
     * @param array $attributes
     * @return \DateInterval
     * @throws \Exception
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): \DateInterval
    {
        return iso8601_decode($value);
    }

    /**
     * @param Model $model
     * @param string $key
     * @param \DateInterval $value
     * @param array $attributes
     * @return mixed
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return iso8601_encode($value);
    }
}
