<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    //
    protected $table = 'notices';
    protected $fillable = [
        'id_user', 
        'id_site', 
        'id_event', 
        'note', 
        'comment', 
        'notice_date'
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
