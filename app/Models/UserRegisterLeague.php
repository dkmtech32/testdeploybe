<?php

namespace App\Models;

use App\Models\League;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRegisterLeague extends Model
{
    protected $table = 'user_register_league';
    use HasFactory;
    
    protected $fillable = [
        'league_id','user_id','status',
    ];
    
    public function league()
    {
        return $this->belongsTo(League::class, 'league_id', 'id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
