<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    //
    protected $table = 'paiements';
    protected $fillable = [
        'id_reservation', 
        'amount', 
        'status', 
        'method', 
        'id_transaction'
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'id_reservation');
    }
}
