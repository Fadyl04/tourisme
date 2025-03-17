<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    //
    protected $table = 'sites';
    protected $fillable =[
        'site_picture',
        'site_name',
        'site_description',
        'site_localisation',
        'site_amount'
    ];
}
