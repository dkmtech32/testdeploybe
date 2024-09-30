<?php

namespace App\Repositories;

use App\Models\GroupTrainingUser;
use App\Models\GroupUser;
use App\Enums\Group;
use App\Models\GroupTraining as GroupModel;
use DB;

class GroupTrainingUserRepository extends BaseRepository
{
    public function model()
    {
        return GroupTrainingUser::class;
    }
    
    public function create($data)
    {
        return $this->model->create($data);
    }
    
    
    public function checkCountPlayerJoin($id, $getTotalPlayerRegister)
    {
        $count = $this->model->where('group_training_id', $id)->count();
        $groupTraining = GroupModel::where('id', $id)->first();
        $totalCount = $count + $getTotalPlayerRegister;
        
        return $totalCount > $groupTraining->members;
    }
    
    public function countAcceptedPlayer($id)
    {
        $count = $this->model->where('group_training_id', $id)->where('status_request', 1)->count();
        return $count;
    }

    public function countAllPlayer($id)
    {
        $count = $this->model->where('group_training_id', $id)->sum(DB::raw('1 + COALESCE(acconpanion, 0)'));
        return $count;
    }
    
    public function checkJoinedGroupTraining($user_id, $groupTrainingById)
    {
        return $this->model->where('group_training_id', $groupTrainingById)->where('user_id', $user_id)->where('status_request', 1)->exists();
    }

    public function checkAppliedGroup($user_id, $groupTrainingById)
    {
        return $this->model->where('group_training_id', $groupTrainingById)->where('user_id', $user_id)->where('status_request', 0)->exists();
    }

    public function getListPlayerGroupTraining($id)
    {
        return $this->model->where('group_training_id', $id)
            ->with('users:id,name,profile_photo_path')
            ->get();
    }
}
