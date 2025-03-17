<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    //
    protected $table = 'event';
    protected $fillable = [
        'label_event',
        'description_event',
        'start_date',
        'end_date',
        'localisation',
        'amount'
    ];
}
