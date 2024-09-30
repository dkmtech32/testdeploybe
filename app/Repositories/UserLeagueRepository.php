<?php


namespace App\Repositories;

use App\Enums\League;
use App\Models\UserRegisterLeague;

class UserLeagueRepository extends BaseRepository {
    
    public function model()
    {
        return UserRegisterLeague::class;
    }
    
    public function index()
    {
        return $this->model->get();
    }
    public function store($input)
    {
        return $this->model->create($input);
    }
    
    public function destroy($id)
    {
        return $this->model->where('id', $id)->delete();
    }

    public function getLeaguePlayerNumberBySlug($slug)
    {
        return $this->model->whereHas('league', function($query) use ($slug) {
            $query->where('slug', $slug);
        })->where('status', League::STATUS_ACTIVE)->count();
    }

    public function checkJoinedLeagueByName($user_id, $league_id)
    {
        return $this->model->where('user_id', $user_id)->where('league_id', $league_id)->where('status', League::STATUS_ACTIVE)->exists();
    }
    public function checkAppliedLeague($user_id, $league_id)
    {
        return $this->model->where('user_id', $user_id)->where('league_id', $league_id)->where('status', League::STATUS_INACTIVE)->exists();
    }
    public function getCurrentUserRegisterLeague()
    {
        return $this->model->where('user_id', auth()->user()->id)->get();
    }

    public function getLeaguesByOwnerId()
    {
        return $this->model->whereHas('league', function($query) {
            $query->where('owner_id', auth()->user()->id);
        })->get();
    }

    public function countPlayerRegisterLeague($id)
    {
        return $this->model->whereHas('league', function($query) use ($id) {
            $query->where('id', $id);
        })->where('status', League::STATUS_ACTIVE)->count();
    }


}
