<?php

namespace App\Http\Controllers\Api;

use App\Enums\Group;
use App\Enums\Utility;
use App\Http\Controllers\Controller;
use App\Http\Requests\GroupRequest;
use App\Http\Requests\GroupTrainingRequest;
use App\Http\Requests\GroupTrainingUserRequest;
use App\Http\Resources\League as LeagueResource;
use App\Models\GroupTrainingUser;
use App\Models\GroupUser;
use App\Repositories\GroupRepository;
use App\Repositories\GroupTrainingRepository;
use App\Repositories\GroupTrainingUserRepository;
use App\Repositories\GroupUserRepository;
use App\Repositories\LeagueRepository;
use App\Repositories\UserLeagueRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;
use function Symfony\Component\Console\Style\success;

class GroupController extends Controller
{

    protected $utility;
    protected $userRegisterLeagueRepository;
    protected $groupRepository;
    protected $groupTraining;
    protected $groupUserRepository;
    protected $groupTrainingUserRepository;


    public function __construct(
        GroupRepository $groupRepository,
        GroupTrainingUserRepository $groupTrainingUserRepository,
        GroupUserRepository $groupUserRepository,
        Utility $utility,
        UserLeagueRepository $userRegisterLeagueRepository,
        GroupTrainingRepository $groupTraining
    ) {
        $this->groupRepository = $groupRepository;
        $this->groupTrainingUserRepository = $groupTrainingUserRepository;
        $this->groupUserRepository = $groupUserRepository;
        $this->utility = $utility;
        $this->userRegisterLeagueRepository = $userRegisterLeagueRepository;
        $this->groupTraining = $groupTraining;

    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::id();
        $listGroups = $this->groupRepository->index($user);
        $response = [
            "data" => $listGroups,
            "message" => "Success",
        ];
        return response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GroupRequest $request)
    {
        try {
            $input = $request->except(['_token']);
            $input['group_owner'] = Auth::id();
            $input['active'] = 0;
            if (isset($input['image'])) {
                $img = $this->utility->saveImageGroup($input);
                if ($img) {
                    $path = '/images/upload/group/' . $input['image']->getClientOriginalName();
                    $input['image'] = $path;
                }
            }
            $this->groupRepository->create($input);
            return response()->json([
                'success' => true,
                'message' => 'Group successfully created.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create group.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $dataGroup = $this->groupRepository->show($id);
        if (!isset($dataGroup)) {
            return response()->json([
                'success' => false,
                'message' => 'Group is not exit.',
            ], 404);
        }

        $response = [
            "data" => $dataGroup,
            "message" => "Success",
        ];

        return response()->json($response);
    }

    public function handleShowHomeGroupDetail(Request $request, string $id)
    {
        if ($request->input('action') == 'showHomeGroupDetail') {
            return $this->showHomeGroupDetail($id);
        } elseif ($request->input('action') == 'showHomeGroupDetailAuthenticated') {
            // Check if the user is authenticated
            if (Auth::guard('sanctum')->check()) {
                $user = Auth::guard('sanctum')->user();
                // Pass the authenticated user to the method
                return $this->showHomeGroupDetailAuthenticated($id, $user);
            } else {
                // Handle the case when the user is not authenticated
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

    public function showHomeGroupDetail(string $id)
    {
        $dataGroup = $this->groupRepository->showGroupDetail($id);

        if ($dataGroup->status == Group::STATUS_PRIVATE) {
            $dataGroup->members = [];
            $dataGroup->id = null;
            $dataGroup->training = [];
            $dataGroup->is_joined = false;
        } else {
            $members = $this->groupUserRepository->getMembersByGroupId($id);
            $dataGroup->members = $members;
            // Add group training information
            $groupTraining = $this->groupTraining->getGroupTrainingByGroupId($id);
            if ($groupTraining) {
                $dataGroup->training = $groupTraining;
            } else {
                $dataGroup->training = null;
            }

            // Add member count
            $memberCount = $this->groupUserRepository->countMembers($id);
            $dataGroup->member_count = $memberCount;
        }
        $response = [
            "data" => $dataGroup,
            "message" => "Success",
        ];
        return response()->json($response);

    }

    public function showHomeGroupDetailAuthenticated(string $id, $user)
    {
        $dataGroup = $this->groupRepository->showGroupDetail($id);

        if ($user) {
            $isJoined = $this->groupUserRepository->checkJoinedGroupByName($user->id, $id);
            if ($isJoined) {
                $dataGroup->is_joined = true;
            } else {
                $dataGroup->is_joined = false;
                $isApplied = $this->groupUserRepository->checkAppliedGroup($user->id, $id);
                if ($isApplied) {
                    $dataGroup->is_applied = true;
                } else {
                    $dataGroup->is_applied = false;
                }
            }
        }

        $groupTraining = $this->groupTraining->getGroupTrainingByGroupId($id);
        if ($groupTraining) {
            $dataGroup->training = $groupTraining;
        } else {
            $dataGroup->training = null;
        }

        $members = $this->groupUserRepository->getMembersByGroupId($id);
        $dataGroup->members = $members;
        $response = [
            "data" => $dataGroup,
            "message" => "Success",
        ];
        return response()->json($response);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(GroupRequest $request, string $id)
    {
        try {
            $input = $request->except(['_token']);
            $input['group_owner'] = Auth::user()->id;
            if (isset($input['image'])) {
                $img = $this->utility->saveImageGroup($input);
                if ($img) {
                    $path = '/images/upload/group/' . $input['image']->getClientOriginalName();
                    $input['image'] = $path;
                }
            }
            $groupUpdate = $this->groupRepository->updateGroup($input, $id);
            return response()->json([
                'success' => true,
                'message' => 'Group successfully updated.',
                'data' => $groupUpdate
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update group.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $this->groupRepository->deleteGroup($id);

            return response()->json([
                'success' => true,
                'message' => 'Group successfully deleted.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete group.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function activeGroup($id)
    {

        try {
            $group = \App\Models\Group::find($id);
            if ($group->active == 0) {
                $group->active = 1;
                $group->save();
                return response()->json([
                    'success' => true,
                    'message' => 'Group successfully activated.',
                ]);
            } else {
                $group->active = 0;
                $group->save();
                return response()->json([
                    'success' => true,
                    'message' => 'Group successfully deactivated.',
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update group.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function groupTraining(GroupTrainingRequest $request): JsonResponse
    {
        try {
            $input = $request->except(['_token']);
            $input['owner_user'] = Auth::user()->id;

            $this->groupTraining->create($input);
            return response()->json([
                'success' => true,
                'message' => 'Group Training successfully created.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create group training.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function listGroupTraining()
    {
        $user = Auth::user()->id;
        $listGroupTraining = $this->groupTraining->index($user);

        $response = [
            "data" => $listGroupTraining,
            "message" => "Success",
        ];
        return response()->json($response);
    }

    public function showGroupTraining(string $id)
    {
        $dataGroup = $this->groupTraining->showGroupTraining($id);
        if (!isset($dataGroup)) {
            return response()->json([
                'success' => false,
                'message' => 'Group training is not exit.',
            ], 404);
        }

        $response = [
            "data" => $dataGroup,
            "message" => "Success",
        ];

        return response()->json($response);
    }

    public function updateGroupTraining(GroupTrainingRequest $request, $id)
    {
        try {
            $input = $request->except(['_token']);
            $input['owner_user'] = Auth::user()->id;

            $groupUpdate = $this->groupTraining->updateGroup($input, $id);
            return response()->json([
                'success' => true,
                'message' => 'Group Training successfully updated.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update group training.',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    public function deleteGroupTraining($id)
    {
        try {
            $this->groupTraining->destroy($id);

            return response()->json([
                'success' => true,
                'message' => 'Group Training successfully deleted.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete group training.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function handleGetActiveFeatureGroup(Request $request)
    {
        if ($request->input('action') == 'getActiveFeatureGroup') {
            return $this->getActiveFeatureGroup();
        } elseif ($request->input('action') == 'getActiveFeatureGroupAuthenticated') {
            if (Auth::guard('sanctum')->check()) {
                $user = Auth::guard('sanctum')->user();
                return $this->getActiveFeatureGroupAuthenticated($user);
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
    public function getActiveFeatureGroup()
    {
        $dataGroup = $this->groupRepository->getActiveGroup();
        $dataGroupWithMemberCount = $dataGroup->map(function ($group) {
            $memberCount = $this->groupUserRepository->countMembers($group->id);
            $group->member_count = $memberCount;
            return $group;
            // })->filter(function ($group) {
            //     return $group->member_count > ($group->number_of_members * 0.5);
        })->take(20);

        $response = [
            "data" => $dataGroupWithMemberCount->values(),
            "message" => "Success get active feature group ",
        ];
        return response()->json($response);
    }
    public function getActiveFeatureGroupAuthenticated($user)
    {
        $locationWithCount = $this->groupRepository->getLocationWithCount();
        // Get the top 4 locations with the most groups
        $topLocations = $locationWithCount->sortByDesc('group_count')->take(4)->pluck('location');

        // Get active groups for the top 4 locations
        $dataGroup = $this->groupRepository->getActiveGroup()
            ->whereIn('location', $topLocations)
            ->groupBy('location')
            ->map(function ($groups) {
                return $groups->take(5); // Take up to 5 groups per location
            })
            ->flatten(1);

        // Include member count and joined status for each group
        $dataGroupWithDetails = $dataGroup->map(function ($group) use ($user) {
            $memberCount = $this->groupUserRepository->countMembers($group->id);
            $group->member_count = $memberCount;
            if ($user) {
                $isJoined = $this->groupUserRepository->checkJoinedGroupByName($user->id, $group->id);
                if ($isJoined) {
                    $group->is_joined = true;
                } else {
                    $group->is_joined = false;
                    $isApplied = $this->groupUserRepository->checkAppliedGroup($user->id, $group->id);
                    if ($isApplied) {
                        $group->is_applied = true;
                    } else {
                        $group->is_applied = false;
                    }
                }
            }
            return $group;
        })
            // ->filter(function ($group) {
            //     return $group->member_count > ($group->number_of_members * 0.5);
            // })

            ->take(20);

        $response = [
            "data" => $dataGroupWithDetails->values(),
            "message" => "Success get active feature group authenticated",
        ];
        return response()->json($response);
    }

    public function checkJoinGroup(Request $request)
    {

        $groupById = $request->input('group_id');
        $user = Auth::user();

        try {
            $checkJoin = $this->groupUserRepository->checkJoinedGroupByName($user->id, $groupById);
            if (empty($checkJoin)) {
                return response()->json([
                    'success' => false,
                    'message' => 'not joined',
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'joined',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check join group.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkFullGroup(Request $request)
    {
        $groupById = $request->input('group_id');
        $checkFullGroup = $this->groupUserRepository->checkFullGroup($groupById);
        if ($checkFullGroup) {
            return response()->json([
                'success' => true,
                'message' => 'Group is full',
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'Group is not full',
        ]);
    }

    public function countMembersByGroupId(Request $request)
    {
        $groupById = $request->input('group_id');
        $countMembers = $this->groupUserRepository->countMembers($groupById);
        return response()->json([
            'success' => true,
            'message' => 'Count members by group id',
            'data' => $countMembers
        ]);
    }
    public function joinGroup(Request $request)
    {
        $groupById = $request->input('group_id');
        //        $groupById = 1;

        $getGroup = $this->groupRepository->getGroupById($groupById);
        if (empty($getGroup)) {
            return response()->json([
                'success' => false,
                'message' => 'Group does not exist',
            ], 404);
        }
        $getGroup->member_count = $this->groupUserRepository->countMembers($getGroup->id);

        $user = Auth::user();
        if ($getGroup->status == Group::STATUS_PRIVATE) {
            $group_users = $this->groupUserRepository->checkJoinedGroupByName($user->id, $getGroup->id);
            if (empty($group_users)) {
                $data = [
                    'group_id' => $getGroup->id,
                    'user_id' => $user->id,
                    'status_request' => Group::STATUS_REQUESTED
                ];
                $this->groupUserRepository->create($data);

                return response()->json([
                    'success' => true,
                    'message' => 'Thank you for join my group, We will respond immediately',
                ]);
            } else {
                if ($group_users->status_request == Group::STATUS_REJECTED) {
                    return response()->json([
                        'success' => true,
                        'message' => 'You can not join group',
                    ]);
                }
            }
        } else {
            if ($getGroup->member_count >= $getGroup->number_of_members) {
                return response()->json([
                    'success' => false,
                    'message' => 'Group is full',
                ]);
            } else {
                $data = [
                    'group_id' => $getGroup->id,
                    'user_id' => $user->id,
                    'status_request' => Group::STATUS_ACCEPTED
                ];
                $this->groupUserRepository->create($data);
                return response()->json([
                    'success' => true,
                    'message' => 'Thank you for join group',
                ]);
            }
        }

    }

    public function showGroupPrivate($id)
    {
        $group = $this->groupRepository->groupPrivate($id);
        if (!isset($group)) {
            return response()->json([
                'success' => false,
                'message' => 'Group is not exit.',
            ], 404);
        }

        $response = [
            "data" => $group,
            "message" => "Success",
        ];

        return response()->json($response);
    }

    //Assuming admin active a list of user in the SAME GROUP
    public function activeUserGroup(Request $request)
    {
        $request->validate([
            'ids' => 'required|array', // Array of IDs to update
            'status_request' => 'required|string', // New status value
            'group_id' => 'required|string', // New status value
        ]);

        $group_id = $request->input('group_id');
        $getGroup = $this->groupRepository->getGroupById($group_id);
        if (empty($getGroup)) {
            return response()->json([
                'success' => false,
                'message' => 'Group does not exist',
            ]);
        }

        $getGroup->member_count = $this->groupUserRepository->countMembers($getGroup->id);
        // if ($getGroup->member_count >= $getGroup->number_of_members) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Group is full',
        //     ]);
        // }
        if ($request->input('status_request') == Group::STATUS_ACCEPTED) {
            $existingIds = $getGroup->group_users->pluck('id')->toArray();
            $newIds = array_diff_key($request->input('ids'), $existingIds);
            $totalNewMembers = count($newIds);
            if ($getGroup->member_count + $totalNewMembers > $getGroup->number_of_members) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected users will exceed the group member limit',
                ]);
            }
        }

        try {
            $ids = $request->input('ids');
            $status = $request->input('status_request');

            // Update the status for all records with the given IDs
            GroupUser::whereIn('id', $ids)->update(['status_request' => $status]);

            return response()->json([
                'success' => true,
                'message' => 'You change status user join group success',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to change status user join group ',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    public function getGroupMembers($id)
    {

        $members = $this->groupUserRepository->getAllMembersByGroupId($id);
        return response()->json([
            'success' => true,
            'message' => 'Group members',
            'data' => $members
        ]);
    }

    public function getLocation()
    {
        $locations = $this->groupRepository->getLocationWithCount();
        return response()->json([
            'success' => true,
            'message' => 'Group locations with count',
            'data' => $locations
        ]);
    }

    public function handleGetActiveGroupByLocation(Request $request)
    {
        if ($request->input('action') == 'getActiveGroupByLocation') {
            return $this->getActiveGroupByLocation($request);
        } elseif ($request->input('action') == 'getActiveGroupByLocationAuthenticated') {
            if (Auth::guard('sanctum')->check()) {
                $user = Auth::guard('sanctum')->user();
                return $this->getActiveGroupByLocationAuthenticated($request, $user);
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
    public function getActiveGroupByLocation(Request $request)
    {
        $location = $request->input('location');
        $status = Group::STATUS_ACTIVE;
        $groups = $this->groupRepository->getGroupByLocationAndStatus($location, $status);
        $groups = $groups->map(function ($group) {
            $group->member_count = $this->groupUserRepository->countMembers($group->id);
            return $group;
        });
        return response()->json([
            'success' => true,
            'message' => 'Group in ' . $location . ' and status ' . $status,
            'data' => $groups
        ]);
    }
    public function getActiveGroupByLocationAuthenticated(Request $request, $user)
    {
        $location = $request->input('location');
        $status = Group::STATUS_ACTIVE;
        $groups = $this->groupRepository->getGroupByLocationAndStatus($location, $status);
        $dataGroupWithDetails = $groups->map(function ($group) use ($user) {
            $memberCount = $this->groupUserRepository->countMembers($group->id);
            $group->member_count = $memberCount;
            if ($user) {
                $isJoined = $this->groupUserRepository->checkJoinedGroupByName($user->id, $group->id);
                if ($isJoined) {
                    $group->is_joined = true;
                } else {
                    $group->is_joined = false;
                    $isApplied = $this->groupUserRepository->checkAppliedGroup($user->id, $group->id);
                    if ($isApplied) {
                        $group->is_applied = true;
                    } else {
                        $group->is_applied = false;
                    }
                }
            }
            return $group;
        });

        return response()->json([
            'success' => true,
            'message' => 'authen Group in ' . $location . ' and status ' . $status . ' authenticated',
            'data' => $dataGroupWithDetails->values()
        ]);
    }
    
    public function storeUserJoinGroupTraining(GroupTrainingUserRequest $request)
    {
        $groupTrainingById = $request->input('group_training_id');
        
        $groupTraining = $this->groupTraining->showGroupTraining($groupTrainingById);
        //        $groupTraining = $this->groupTraining->showGroupTraining($groupTraining);
        if (empty($groupTraining)) {
            return response()->json([
                'success' => false,
                'message' => 'Group Training is not exit',
            ], 200);
        }
        $user = Auth::user();
    
        if (!$this->groupTraining->checkMemberInGroupByGroupTrainingId($groupTrainingById, $user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not in this group',
            ], 200);
        }
        
        //check number acconpanion
        $getAcconpanion = $request['acconpanion'];
        if($getAcconpanion > 4 ) {
            return response()->json([
                'success' => false,
                'message' => 'Acconpanion limit is only 4 member',
            ], 200);
        }
    
        //check full member
        $getTotalPlayerRegister = $getAcconpanion + 1;
        $checkFullUser = $this->groupTrainingUserRepository->checkCountPlayerJoin($groupTraining->id ,$getTotalPlayerRegister );
        if ($checkFullUser) {
            return response()->json([
                'success' => false,
                'message' => 'Total number of people has exceeded the group training member limit',
            ], 200);
        }
    
        //check member joined
        $checkJoin = $this->groupTrainingUserRepository->checkJoinedGroupTraining($user->id, $groupTrainingById);
        if ($checkJoin) {
            return response()->json([
                'success' => false,
                'message' => 'You are joined',
            ]);
        }
    
        //check payment required
        if($groupTraining->payment == Group::STATUS_PAYMENT_REQUIRED) {
            $data = [
                'group_training_id' => $request['group_training_id'],
                'acconpanion' => $request['acconpanion'],
                'note' => $request['note'],
                'attendance' => 0,
                'user_id' => $user->id,
                'status_request' => 0,
            ];

            $this->groupTrainingUserRepository->create($data);

            return response()->json([
                'success' => true,
                'message' => 'Please pay fee to join group training',
            ]);
            
        }
        //check payment later
        elseif($groupTraining->payment == Group::STATUS_PAYMENT_LATER || $groupTraining->payment == "") {
            $data = [
                'group_training_id' => $request['group_training_id'],
                'acconpanion' => $request['acconpanion'],
                'note' => $request['note'],
                'attendance' => 0,
                'user_id' => $user->id,
                'status_request' => 1,
            ];

            $this->groupTrainingUserRepository->create($data);
            return response()->json([
                'success' => true,
                'message' => 'Thank you for join my group training',
            ]);
        }

    }

    public function getMyGroup()
    {
        $user = Auth::user();
        $myGroup = $this->groupUserRepository->getMyGroup($user->id);

        $myGroup = $myGroup->map(function ($group) use ($user) {
            $memberCount = $this->groupUserRepository->countMembers($group->id);
            $group->member_count = $memberCount;
            $isJoined = $this->groupUserRepository->checkJoinedGroupByName($user->id, $group->id);
            if ($isJoined) {
                $group->is_joined = true;
            } else {
                $group->is_joined = false;
                $isApplied = $this->groupUserRepository->checkAppliedGroup($user->id, $group->id);
                if ($isApplied) {
                    $group->is_applied = true;
                } else {
                    $group->is_applied = false;
                }
            }

            $isOwner = $this->groupRepository->checkOwnerGroup($user->id, $group->id);
            if ($isOwner) {
                $group->is_owner = true;
            } else {
                $group->is_owner = false;
            }
            return $group;
        });

        return response()->json([
            'success' => true,
            'message' => 'My group',
            'data' => $myGroup
        ]);
    }

    public function getGroupTraining($id)
    {
        $user = Auth::user();
        $isMember = $this->groupUserRepository->checkJoinedGroupByName($user->id, $id);
        
        if (!$isMember) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a member of this group',
            ], 403);
        }
        
        $groupTrainings = $this->groupTraining->getGroupTrainingByGroupId($id);
        $groupTrainings = $groupTrainings->map(function ($groupTraining) use ($user) {
            $memberCount = $this->groupTrainingUserRepository->countAcceptedPlayer($groupTraining->id);
            $groupTraining->member_count = $memberCount;
            $isJoined = $this->groupTrainingUserRepository->checkJoinedGroupTraining($user->id, $groupTraining->id);
            if ($isJoined) {
                $groupTraining->is_joined = true;
            } else {
                $groupTraining->is_joined = false;
                $isApplied = $this->groupTrainingUserRepository->checkAppliedGroup($user->id, $groupTraining->id);
                if ($isApplied) {
                    $groupTraining->is_applied = true;
                } else {
                    $groupTraining->is_applied = false;
                }
            }
            return $groupTraining;
        });
        return response()->json([
            'success' => true,
            'message' => 'Group Training',
            'data' => $groupTrainings
        ]);
    }

    public function getUpcommingGroupTraining($id){
        {
            $user = Auth::user();
            $isMember = $this->groupUserRepository->checkJoinedGroupByName($user->id, $id);
            
            if (!$isMember) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a member of this group',
                ], 403);
            }
            
            $groupTrainings = $this->groupTraining->getUpcomingGroupTrainingByGroupId($id);
            $groupTrainings = $groupTrainings->map(function ($groupTraining) use ($user) {
                $memberCount = $this->groupTrainingUserRepository->countAcceptedPlayer($groupTraining->id);
                $groupTraining->member_count = $memberCount;
                $isJoined = $this->groupTrainingUserRepository->checkJoinedGroupTraining($user->id, $groupTraining->id);
                if ($isJoined) {
                    $groupTraining->is_joined = true;
                } else {
                    $groupTraining->is_joined = false;
                    $isApplied = $this->groupTrainingUserRepository->checkAppliedGroup($user->id, $groupTraining->id);
                    if ($isApplied) {
                        $groupTraining->is_applied = true;
                    } else {
                        $groupTraining->is_applied = false;
                    }
                }
                return $groupTraining;
            });
            return response()->json([
                'success' => true,
                'message' => 'Upcomming Group Training',
                'data' => $groupTrainings
            ]);
        }
    }
    
    public function getListPlayerGroupTraining($id)
    {
        $user = Auth::user();
        $groupTraining = $this->groupTraining->showGroupTraining($id);
        $group = $this->groupRepository->getGroupById($groupTraining->group_id);
        Log::info('id'. $id);
        Log::info('user'. $user);
        Log::info('groupTraining'. $groupTraining);
        Log::info('group'. $group);
        $isMember = $this->groupUserRepository->checkJoinedGroupByName($user->id, $group->id);
        Log::info('isMember'. $isMember);
        if (!$isMember) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a member of this group',
            ], 403);
        }
        
        $players = $this->groupTrainingUserRepository->getListPlayerGroupTraining($id);
        
        return response()->json([
            'success' => true,
            'message' => 'List player group training',
            'data' => $players
        ]);
    }
    
    public function listTraining()
    {
        $listTrainingGroup = $this->groupTraining->listGroupTrainingNextTime();
        $listTrainingGroup = $listTrainingGroup->map(function ($groupTraining) {
            $memberCount = $this->groupTrainingUserRepository->countAllPlayer($groupTraining->id);
            $groupTraining->member_count = $memberCount;
            return $groupTraining;
        });
        $response = [
            "data" => $listTrainingGroup,
            "message" => "Success",
        ];
        return response()->json($response);
    }
    
    public function activeUserJoinTraining(Request $request)
    {
        $request->validate([
        'ids' => 'required', // Array of IDs to update
        'status_request' => 'required', // New status value
        'group_training_id' => 'required',
    ]);
        $groupTrainingId = $request->input('group_training_id');
        $getGroupTraining = $this->groupTraining->groupTrainingId($groupTrainingId);
        if (empty($getGroupTraining)) {
            return response()->json([
                'success' => false,
                'message' => 'Group Training does not exist',
            ]);
        }
    
        try {
            $ids = $request->input('ids');
            $status_request = $request->input('status_request');
            // Update the status for all records with the given IDs
            GroupTrainingUser::whereIn('user_id', $ids)->update(['status_request' => $status_request]);
        
            return response()->json([
                'success' => true,
                'message' => 'Change status player register group training successfully',
            ]);
        
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to change active player register group training ',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function checkAttendUserJoinTraining(Request $request)
    {
        $request->validate([
            'ids' => 'required', // Array of IDs to update
            'attendance' => 'required', // New status value
            'group_training_id' => 'required',
        ]);
        $groupTrainingId = $request->input('group_training_id');
        $getGroupTraining = $this->groupTraining->groupTrainingId($groupTrainingId);
        if (empty($getGroupTraining)) {
            return response()->json([
                'success' => false,
                'message' => 'Group Training does not exist',
            ]);
        }
        
        try {
            $ids = $request->input('ids');
            $checkAttend = $request->input('attendance');
            // Update the status for all records with the given IDs
            GroupTrainingUser::whereIn('user_id', $ids)->update(['attendance' => $checkAttend]);
            
            return response()->json([
                'success' => true,
                'message' => 'Change status player attend to group training successfully',
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to change status player attend to group training ',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
}
