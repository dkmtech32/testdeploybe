<?php

namespace App\Repositories;

use App\Models\GroupUser;
use App\Enums\Group;
use App\Models\Group as GroupModel;

class GroupUserRepository extends BaseRepository
{
    public function model()
    {
        return GroupUser::class;
    }

    public function create($data)
    {
        return $this->model->create($data);
    }

    public function getGroupByUserId($userId)
    {
        return $this->model->with('groups')->where('user_id', $userId)->get();
    }

    public function checkJoinedGroupByName($user_id, $group_id)
    {
        return $this->model->where('group_id', $group_id)->where('user_id', $user_id)->where('status_request', Group::STATUS_ACCEPTED)->exists();
    }

    public function checkFullGroup($group_id)
    {
        $count = $this->model->where('group_id', $group_id)->count();
        $group = GroupModel::where('id', $group_id)->first();
        return $count >= $group->number_of_members;
    }

    public function getMembersByGroupId($group_id)
    {
        return $this->model->with([
            'users' => function ($query) {
                $query->select('id', 'name', 'email', 'phone', 'profile_photo_path', 'sex');
            }
        ])
            ->where('group_id', $group_id)
            ->where('status_request', Group::STATUS_ACCEPTED)
            ->select('users.id', 'users.name', 'users.email', 'users.phone', 'users.profile_photo_path', 'users.sex')
            ->join('users', 'group_users.user_id', '=', 'users.id')
            ->get();
    }

    public function getAllMembersByGroupId($group_id)
    {
        return $this->model->with([
            'users' => function ($query) {
                $query->select('id', 'name', 'email', 'phone', 'profile_photo_path', 'sex');
            }
        ])
            ->where('group_id', $group_id)
            ->get();
    }

    public function countMembers($group_id)
    {
        return $this->model->where('group_id', $group_id)
            ->where('status_request', Group::STATUS_ACCEPTED)
            ->count();
    }

    public function checkAppliedGroup($user_id, $group_id)
    {
        return $this->model->where('group_id', $group_id)
            ->where('user_id', $user_id)
            ->where('status_request', Group::STATUS_REQUESTED)
            ->exists();
    }

    public function getMyGroup($user_id)
    {
        return $this->model->with('groups')
            ->where('user_id', $user_id)
            ->get()
            ->pluck('groups');
    }
}
