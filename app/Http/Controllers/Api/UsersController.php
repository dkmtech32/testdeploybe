<?php

namespace App\Http\Controllers\Api;

use App\Enums\Utility;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\User as UserResource;

class UsersController extends Controller
{
    protected $utility;
    protected $userRepository;
    public function __construct(UserRepository $userRepository, Utility $utility)
    {
        $this->usersRepository = $userRepository;
        $this->utility = $utility;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();
        return response()->json([
            "users" => UserResource::collection($users),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::find($id);
        return response()->json([UserResource::make($user)]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);
        $user->update($request->all());
        return response()->json([UserResource::make($user)]);
    }


    /**
     * Update the current user.
     */
    public function updateCurrentUser(Request $request): JsonResponse
    {
        $user = Auth::user();
        $input = $request->except(['_token']);
        if (isset($input['avatar'])) {
            $img = $this->utility->saveImageUser($input);
            if ($img) {
                $path = '/images/upload/user/' . $input['avatar']->getClientOriginalName();
                $input['profile_photo_path'] = $path;
            }
        }
        // $this->userRepository->update($input, $user->id);
        $user->update($input);
        return response()->json([UserResource::make($user)]);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        $user->delete();
        return response()->json("Xoa thanh cong");
    }
}
