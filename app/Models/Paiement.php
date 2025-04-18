<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    //
    protected $table = 'paiements';
    protected $fillable = [
        'reservation_id',
        'user_id', 
        'amount_paiement',
        'date_paiement',
        'status', 
        'method', 
        'transaction_id'
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
