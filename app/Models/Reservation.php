<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = ['uid', 'check_in', 'check_out', 'summary'];

    /**
     * @param array $event
     * @return self
     * @throws \Exception
     */
    public static function makeFromIcalEvent(array $event): self
    {
        return new self([
            'uid' => $event['UID'],
            'check_in' => new \DateTime($event['DTSTART;VALUE=DATE']),
            'check_out' => new \DateTime($event['DTEND;VALUE=DATE']),
            'summary' => $event['DESCRIPTION'] ?? $event['SUMMARY']
        ]);
    }
}
