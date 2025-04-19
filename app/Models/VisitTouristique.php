<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitTouristique extends Model
{
    //
    use HasFactory;
    protected $table = 'visit_touristiques';
    protected $fillable = [
        'user_id',
        'depart_date',
        'lieu',
        'amount_visit',
        'sites',
        'number_place'
    ];

    protected $casts = [
        'sites' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sites()
    {
        return $this->belongsToMany(Site::class);
    }
}
