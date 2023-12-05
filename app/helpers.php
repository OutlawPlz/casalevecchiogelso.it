<?php

if (! function_exists('encode_iso86')) {
    /**
     * @param DateTimeInterface|DateInterval|DatePeriod $date
     * @return string
     */
    function iso8601_encode(\DateTimeInterface|\DateInterval|\DatePeriod $date): string
    {
        if ($date instanceof \DateTimeInterface) {
            return $date->format(\DateTimeInterface::ATOM);
        }

        $iso8601 = '';

        if ($date instanceof \DatePeriod) {
            $iso8601 .= "R$date->recurrences/";

            $iso8601 .= $date->getStartDate()->format(\DateTimeInterface::ATOM);

            $date = $date->getDateInterval();
        }

        if ($date instanceof \DateInterval) {
            if ($iso8601) $iso8601 .= '/';

            $P = $date->invert ? '-P' : 'P';

            foreach (['y', 'm', 'd'] as $period) {
                if ($date->$period) $P .= $date->$period . strtoupper($period);
            }

            $T = '';

            foreach (['h', 'i', 's'] as $time) {
                if ($date->$time) $T .= $date->$time . strtoupper($time);
            }

            if ($T) $T = 'T' . $T;

            $iso8601 .= $P . $T;
        }

        return $iso8601;
    }
}

if (! function_exists('decode_iso8601')) {
    /**
     * @param string|null $iso8601
     * @return DateTimeInterface|DateInterval|DatePeriod|null
     * @throws Exception
     */
    function iso8601_decode(?string $iso8601): \DateTimeInterface|\DateInterval|\DatePeriod|null
    {
        if (! $iso8601) return null;

        $date = null;
        $interval = null;
        $recurrences = null;

        $segments = explode('/', $iso8601);

        foreach ($segments as $segment) {
            $firstLetter = substr($segment, 0, 1);

            if ($firstLetter === 'P') {
                $interval = new \DateInterval($segment); continue;
            }

            if ($firstLetter === 'R') {
                $recurrences = (int) substr($segment, 1, null); continue;
            }

            $date = new \DateTime($segment);
        }

        if ($date && $interval) {
            return new \DatePeriod($date, $interval, $recurrences ?? 0);
        }

        return $interval ?? $date;
    }
}
