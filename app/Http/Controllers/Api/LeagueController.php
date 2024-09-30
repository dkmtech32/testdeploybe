<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\LeagueRequest;
use App\Models\League;
use App\Http\Controllers\Controller;
use App\Models\UserRegisterLeague;
use App\Repositories\LeagueRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Enums\Utility;
use Illuminate\Http\Response;
use App\Http\Resources\League as LeagueResource;
use Log;
use PhpParser\Node\Expr\ArrayItem;
use Str;
use App\Repositories\UserLeagueRepository;
use Illuminate\Http\Exceptions\HttpResponseException;

class LeagueController extends Controller
{
    protected $utility;
    protected $userRegisterLeagueRepository;
    protected $leagueRepository;
    public function __construct(LeagueRepository $leagueRepository, Utility $utility, UserLeagueRepository $userRegisterLeagueRepository)
    {
        $this->leagueRepository = $leagueRepository;
        $this->utility = $utility;
        $this->userRegisterLeagueRepository = $userRegisterLeagueRepository;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::id();
        $league = $this->leagueRepository->index($user);
        $response = [
            "data" => LeagueResource::collection($league),
            "message" => "Success",
        ];
        return response()->json($response);
    }

    public function listLeagueHomepage()
    {
        $league = $this->leagueRepository->listLeagueHomePage();

        $response = [
            "data" => LeagueResource::collection($league),
            "message" => "Success",
        ];

        return response()->json($response);


    }
    public function getActiveLeague()
    {
        $league = $this->leagueRepository->getListLeagues();
     
        $response = [
            "data" => LeagueResource::collection($league),
            "message" => "success",
        ];
        return response()->json($response);
    }
    /**
     * Store a newly created resource in storage.
     */

    public function getAllLeague()
    {
        $league = $this->leagueRepository->getAllLeagues();
        $response = [
            "data" => LeagueResource::collection($league),
            "message" => "Success",
        ];
        return response()->json($response);
    }
    public function store(LeagueRequest $request): JsonResponse
    {
        try {
            $input = $request->except(['_token']);
            $input['slug'] = Str::slug($request->name);
            $input['owner_id'] = Auth::id();
            $input['status'] = 0;

            if ($request->hasFile('images')) {
                $img = $this->utility->saveImageLeague($input);
                if ($img) {
                    $path = '/images/upload/league/' . $input['images']->getClientOriginalName();
                    $input['images'] = $path;
                }
            }

            $league = $this->leagueRepository->store($input);

            return response()->json([
                'success' => true,
                'message' => 'League successfully created.',
                'data' => LeagueResource::make($league),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create league.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function handleLeagueDetail(Request $request, $slug)
    {
        if ($request->input('action') == 'show') {
            return $this->show($slug);
        } elseif ($request->input('action') == 'getLeagueBySlug') {
            // Check if the user is authenticated
            if (Auth::guard('sanctum')->check()) {
                $user = Auth::guard('sanctum')->user();
                // Pass the authenticated user to the method
                return $this->getLeagueBySlug($slug, $user);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.',
                ], 401);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid action.',
            ], 400);
        }
    }
    public function show($slug)
    {
        $dataLeague = $this->leagueRepository->show($slug);
        $dataLeague->player_number = $this->userRegisterLeagueRepository->getLeaguePlayerNumberBySlug($slug);
        $dataLeague->is_full = $dataLeague->player_number >= $dataLeague->number_of_athletes;
        if (!isset($dataLeague)) {
            return response()->json([
                'success' => false,
                'message' => 'League is not exit.',
            ], 404);
        }

        $response = [
            "data" => $dataLeague,
            "message" => "Success",
        ];

        return response()->json($response);

    }

    public function getLeagueBySlug($slug, $user)
    {
        $league = $this->leagueRepository->getLeagueBySlug($slug);
        $league->player_number = $this->userRegisterLeagueRepository->getLeaguePlayerNumberBySlug($slug);
        $league->is_full = $league->player_number >= $league->number_of_athletes;
        if ($user) {
            $isJoined = $this->userRegisterLeagueRepository->checkJoinedLeagueByName($user->id, $league->id);
            if ($isJoined) {
                $league->is_joined = true;
            } else {
                $league->is_joined = false;
                $isApplied = $this->userRegisterLeagueRepository->checkAppliedLeague($user->id, $league->id);
                if ($isApplied) {
                    $league->is_applied = true;
                } else {
                    $league->is_applied = false;
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $league,
        ]);
    }

    public function update(LeagueRequest $request, $id): JsonResponse
    {
        try {
            $input = $request->except(['_token']);
            $input['slug'] = Str::slug($request->name);

            if ($request->hasFile('images')) {
                $img = $this->utility->saveImageLeague($input);
                if ($img) {
                    $path = '/images/upload/league/' . $input['images']->getClientOriginalName();
                    $input['images'] = $path;
                }
            }
            $this->leagueRepository->updateLeague($input, $id);

            $updatedLeague = $this->leagueRepository->getLeagueById($id);
            return response()->json([
                'success' => true,
                'message' => 'League successfully updated.',
                'data' => LeagueResource::make($updatedLeague),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update league.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(League $league)
    {
        try {
            // $league->delete();
            $this->leagueRepository->destroy($league->id);
            return response()->json([
                'success' => true,
                'message' => 'League "' . $league->name . '" successfully deleted.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete league.',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    public function activeLeague($id)
    {
        try {
            $league = $this->leagueRepository->getLeagueById($id);
            if ($league->status == 0) {
                $league->status = 1;
                $league->save();
                return response()->json([
                    'success' => true,
                    'message' => 'League successfully activated.',
                ]);
            } else {
                $league->status = 0;
                $league->save();
                return response()->json([
                    'success' => true,
                    'message' => 'League successfully deactivated.',
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update league.',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function saveRegisterLeague(Request $request)
    {
        $leagueStartDate = $this->leagueRepository->getLeagueById($request['league_id'])->start_date;
        // em có sửa lại phía BE tự lấy start_date từ db
        $startDate = strtotime($leagueStartDate);
        $dateCurrent = strtotime(date("Y-m-d"));
        $league = $this->leagueRepository->getLeagueById($request['league_id']);
        $player_count = $this->userRegisterLeagueRepository->countPlayerRegisterLeague($league->id);
        if ($player_count >= $league->number_of_athletes) {
            return response()->json([
                'success' => false,
                'message' => 'League is full',
            ], 404);
        }

        //    $leagueStartDate = strtotime('2024-08-26');
        //    $dateCurrent =  strtotime('2024-08-27');
        if ($dateCurrent >= $startDate) {
            return response()->json([
                'success' => false,
                'message' => 'Registration deadline for the tournament has expired',
            ], 404);
        }
        try {
            $userRegisterLeague = $request->except(['_token']);
            $userRegisterLeague['user_id'] = Auth::user()->id;
            $userRegisterLeague['league_id'] = $request['league_id'];
            $userRegisterLeague['status'] = "inactive";
            $this->userRegisterLeagueRepository->store($userRegisterLeague);
            return response()->json([
                'success' => true,
                'message' => 'You are register league success',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to register league',
                'error' => $e->getMessage()
            ], 500);
        }

    }



    public function listUserRegister($slug)
    {
        //get league
        $userRegisterLeague = $this->leagueRepository->show($slug);
        if (!isset($userRegisterLeague)) {
            return response()->json([
                'success' => false,
                'message' => 'League is not exit',
            ], 404);
        }
        //get user register league
        $usersLeague = $userRegisterLeague->userLeagues;
        if (count($usersLeague) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'There are no registered users yet',
            ], 404);
        }
        $response = [
            "data" => $usersLeague,
            "message" => "Success",
        ];

        return response()->json($response);

    }

    public function getCurrentUserRegisterLeague()
    {
        $userRegisterLeagues = $this->userRegisterLeagueRepository->getCurrentUserRegisterLeague();
        if (empty($userRegisterLeagues)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not registered for any league',
            ], 404);
        }

        $leaguesWithInfo = [];
        foreach ($userRegisterLeagues as $registration) {
            $league = $this->leagueRepository->getLeagueById($registration->league_id);
            $leaguesWithInfo[] = [
                'registration' => $registration,
                'league' => new LeagueResource($league),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $leaguesWithInfo,
        ]);
    }

    //Assuming admin active a list of user in the SAME LEAGUE
    public function activeUserRegister(Request $request)
    {
        $request->validate([
            'ids' => 'required|array', // Array of IDs to update
            'status' => 'required|string', // New status value
            'league_id' => 'required|string', // League ID
        ]);
        $league_id = $request->input('league_id');
        $getLeague = $this->leagueRepository->leagueId($league_id);

        if (empty($getLeague)) {
            return response()->json([
                'success' => false,
                'message' => 'League does not exist',
            ]);
        }

        if ($request->status == 'active') {
            $existingIds = $getLeague->userLeagues->pluck('id')->toArray();
            $newIds = array_diff_key($request->input('ids'), $existingIds);
            $totalNewPlayers = count($newIds);
            $player_count = $this->userRegisterLeagueRepository->countPlayerRegisterLeague($getLeague->id);
            if ($player_count + $totalNewPlayers > $getLeague->number_of_athletes) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected users will exceed the league player limit',
                ]);
            }
        }

        try {
            $ids = $request->input('ids');
            $status = $request->input('status');
            // Update the status for all records with the given IDs
            UserRegisterLeague::whereIn('id', $ids)->update(['status' => $status]);

            return response()->json([
                'success' => true,
                'message' => 'You change status user register league success',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to change status user register league ',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    public function getLeaguePlayerNumberBySlug($slug)
    {

        $leaguePlayerNumber = $this->userRegisterLeagueRepository->getLeaguePlayerNumberBySlug($slug);

        return response()->json([
            'success' => true,
            'data' => $leaguePlayerNumber,
        ]);
    }

    public function getLeaguesPlayerNumber()
    {
        $user = Auth::user();
        $leaguePlayerCounts = [];

        if ($user->role == 'user') {
            $userRegisterLeagues = $this->userRegisterLeagueRepository->getLeaguesByOwnerId();
        } elseif ($user->role == 'admin') {
            $userRegisterLeagues = $this->userRegisterLeagueRepository->index();
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        foreach ($userRegisterLeagues as $league) {
            $leagueId = $league->league_id;
            if (!isset($leaguePlayerCounts[$leagueId])) {
                $leaguePlayerCounts[$leagueId] = [
                    'league_id' => $leagueId,
                    'player_count' => 0,
                ];
            }
            $leaguePlayerCounts[$leagueId]['player_count']++;
        }
        if ($userRegisterLeagues->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not registered for any league',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $leaguePlayerCounts,
        ]);
    }
    public function destroyPlayer($id)
    {
        try {
            // $league->deleteUser();
            $this->userRegisterLeagueRepository->destroy($id);

            return response()->json([
                'success' => true,
                'message' => 'League user successfully deleted.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete league user.',
                'error' => $e->getMessage()
            ], 500);
        }

    }

}
