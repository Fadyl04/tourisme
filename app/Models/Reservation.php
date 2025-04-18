<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    
    use HasFactory;

    //
    protected $table = 'reservations';
    protected $fillable = [
        'user_id',
        'site_id', 
        'event_id', 
        'status', 
        'amount_reservation',
        'date_reservation'
    ];
    protected $dates = [
        'date_reservation',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

        public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }


    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function paiement()
    {
        return $this->hasOne(Paiement::class, 'reservation_id');
    }

    protected static function booted()
    {
        static::creating(function ($reservation) {
            if (!$reservation->date_reservation) {
                $reservation->date_reservation = now(); // Définit automatiquement la date à l'heure actuelle
            }
        });
    }
}
