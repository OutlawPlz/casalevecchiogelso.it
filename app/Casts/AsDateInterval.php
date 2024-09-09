<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use function App\Helpers\{iso8601_decode, iso8601_encode};

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
     * @param mixed $value
     * @param array $attributes
     * @return string
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return iso8601_encode($value);
    }
}
