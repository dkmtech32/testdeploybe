<?php

namespace App\Models;

use App\Models\GroupUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'image',
        'description',
        'number_of_members',
        'location',
        'note',
        'status',
        'active',
        'group_owner'
    ];
    
    public function group_users()
    {
        return $this->hasMany(GroupUser::class);
    }
    
    public function group_trainings()
    {
        return $this->hasMany(GroupTraining::class);
    }
    
    public function owner()
    {
        return $this->belongsTo(User::class, 'group_owner', 'id');
    }
}
