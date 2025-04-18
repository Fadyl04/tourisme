<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    //
    protected $table = 'events';
    protected $fillable = [
        'picture_event',
        'label_event',
        'description_event',
        'start_date',
        'end_date',
        'localisation',
        'amount_event',
        'number_available_event'
    ];
}
