<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeUser extends Model
{
    //
    protected $table = 'type_users';

    protected $fillable = [
        'label_type'
    ];
    
    public function users(){
        return $this->hasMany(User::class, 'id_type_user');
    }
    
}
