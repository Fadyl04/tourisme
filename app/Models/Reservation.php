<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    //
    protected $table = 'reservations';
    protected $fillable = [
        'id_user',
        'id_site', 
        'id_event', 
        'status', 
        'reservation_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function site()
    {
        return $this->belongsTo(Site::class, 'id_site');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'id_event');
    }
}
