<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    //
    use HasFactory;
    protected $table = 'sites';
    protected $fillable =[
        'picture_site',
        'name_site',
        'localisation_site',
        'description_site'
    ];
    public function visitTouristiques()
    {
        return $this->belongsToMany(VisitTouristique::class);
    }
}
