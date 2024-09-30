<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupTrainingUser extends Model
{
    use HasFactory;
    
    use HasFactory;
    
    protected $table = 'group_training_users';
    
    protected $fillable = [
        'group_training_id', 'user_id', 'status_request', 'acconpanion', 'note', 'attendance'
    ];
    
    public function groups()
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }
    
    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function userJoinTraining()
    {
        return $this->belongsTo(GroupTraining::class, 'group_training_id', 'id');
    }
}
