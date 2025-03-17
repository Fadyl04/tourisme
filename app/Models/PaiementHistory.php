<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaiementHistory extends Model
{
    //
    protected $table = 'paiement_histories';
    protected $fillable = [
        'id_paiement', 
        'label', 
        'amount', 
        'method', 
        'status'
    ];
    public function paiement()
    {
        return $this->belongsTo(Paiement::class, 'id_paiement');
    }
}
