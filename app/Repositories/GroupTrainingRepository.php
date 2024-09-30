<?php

namespace App\Repositories;

use App\Models\GroupTraining;
use App\Enums\Group;
use Log;

class GroupTrainingRepository extends BaseRepository
{
    public function model()
    {
        return GroupTraining::class;
    }
    
    public function getGroupTrainByName($nameGroupTraining)
    {
        return $this->model->with('groups')->where('name', $nameGroupTraining)->first();
    }
    
    public function index($user)
    {
        if (\Auth::user()->role == 'admin') {
            return $this->model->orderBy('created_at', 'desc')->get();
        }
        
        return $this->model->where('owner_user', $user)->orderBy('created_at', 'desc')->get();
    }
    
    public function create($input)
    {
        return $this->model->create($input);
    }
    
    public function update($input, $id)
    {
        return $this->model->where('id', $id)->update($input);
    }
    
    public function destroy($id)
    {
        return $this->model->where('id', $id)->delete();
    }
    
    
    public function getMembersById($id)
    {
        return $this->model->where('id', $id)->first();
    }
    
    public function updateMembers($id, $data)
    {
        return $this->model->where('id', $id)->update($data);
    }
    
    public function showGroupTraining($id)
    {
        return $this->model->where('id', $id)->first();
    }
    
    public function updateGroup($input, $id)
    {
        return $this->model->where('id', $id)->update($input);
    }

    public function getGroupTrainingByGroupId($id)
    {
        return $this->model->where('group_id', $id)->get();
    }

    public function getUpcomingGroupTrainingByGroupId($id)
    {
        return $this->model->where('group_id', $id)
            ->whereDate('date', '>=', now()->startOfDay())
            ->orderBy('date', 'asc')
            ->get();
    }

    public function checkMemberInGroupByGroupTrainingId($groupTrainingId, $userId)
    {
        $groupTraining = $this->model->where('id', $groupTrainingId)->first();
        if (!$groupTraining) {
            return false;
        }
        
        $groupId = $groupTraining->group_id;
        $checkMember = \DB::table('group_users')
            ->where('group_id', $groupId)
            ->where('user_id', $userId)
            ->where('status_request', Group::STATUS_ACCEPTED)
            ->exists();

        return $checkMember;
    }
    
    public function listGroupTrainingNextTime()
    {
        $currentDate = date('Y-m-d ');
        return $this->model->where('date', '>=', $currentDate)->orderBy('created_at', 'desc')->get();
    }
    
    public function groupTrainingId($id)
    {
        return $this->model->with(['userJoinTraining' => function($query) {
            $query->where('status_request', '0');
        }])->where('id', $id)->first();
    }
}
