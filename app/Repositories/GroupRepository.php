<?php

namespace App\Repositories;

use App\Models\Group;
use App\Enums\Group as GroupEnum;

class GroupRepository extends BaseRepository
{
    public function model()
    {
        return Group::class;
    }
    
    public function index($user)
    {
        if (\Auth::user()->role == 'admin') {
            return $this->model->orderBy('created_at', 'desc')->get();
        }
        return $this->model->where('group_owner', $user)->orderBy('created_at', 'desc')->get();
    }
    
    public function show($id)
    {
        return $this->model
            ->where('id', $id)
            ->with('owner')
            ->first();
    }
    
    public function showGroupDetail($id)
    {
        return $this->model->where('id', $id)->first();
    }

    public function updateGroup($input, $id)
    {
        return $this->model->where('id', $id)->update($input);
    }
    
    public function getActiveGroup()
    {
        return $this->model->where('active', GroupEnum::STATUS_ACTIVE)->get();
    }
    
    public function getLocationWithCount()
    {
        return $this->model
            ->select('location')
            ->selectRaw('COUNT(*) as group_count')
            ->groupBy('location')
            ->get();
    }
    
    public function getGroupByLocationAndStatus($location, $active)
    {
        return $this->model->where('location', $location)->where('active', $active)->get();
    }

    
    public function getGroupById($id)
    {
        return $this->model->with(['group_users' => function($query) {
            $query->where('status_request', GroupEnum::STATUS_ACCEPTED);
        }])->where('id', $id)->first();
    }
    
    public function groupPrivate($id)
    {
        return $this->model->with('group_users')->where('id', $id)->where('status', "private")->first();
    }
    
    
    // public function checkJoinedGroupByName($user_id, $group_id)
    // {
    //     return $this->model->where('group_id', $group_id)->where('user_id', $user_id)->where('status_request', \App\Enums\Group::STATUS_ACCEPTED)->first();
    // }
    
    public function deleteGroup($id)
    {
        return $this->model->where('id', $id)->delete();
    }

    public function checkOwnerGroup($user_id, $group_id)
    {
        return $this->model->where('id', $group_id)->where('group_owner', $user_id)->exists();
    }
}
