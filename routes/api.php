<?php

use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Middleware\CheckAuth;
use App\Http\Middleware\PublicAndAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LeagueController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UsersController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/feature-leagues', [LeagueController::class, 'listLeagueHomepage']);
Route::get('/leagues', [LeagueController::class, 'getActiveLeague']);
// Route::get('/leagues/{slug}', [LeagueController::class, 'show'])->name('league.show');
Route::get('/league-player-number/{slug}', [LeagueController::class, 'getLeaguePlayerNumberBySlug'])->name('getLeaguePlayerNumberBySlug');

Route::post('/check-full-group/', [GroupController::class, 'checkFullGroup'])->name('checkFullGroup');
Route::get('/count-members-by-group-id/', [GroupController::class, 'countMembersByGroupId'])->name('countMembersByGroupId');
Route::get('/group/location', [GroupController::class, 'getLocation'])->name('getLocation');

Route::get('/leagues/{slug}', [LeagueController::class, 'handleLeagueDetail'])
    ->middleware(CheckAuth::class . ':show,getLeagueBySlug')
    ->name('handleLeagueDetail');

Route::get('/active-feature-group', [GroupController::class, 'handleGetActiveFeatureGroup'])
    ->middleware(CheckAuth::class . ':getActiveFeatureGroup,getActiveFeatureGroupAuthenticated')
    ->name('handleGetActiveGroup');

Route::get('/groups/{id}', [GroupController::class, 'handleShowHomeGroupDetail'])
    ->middleware(CheckAuth::class . ':showHomeGroupDetail,showHomeGroupDetailAuthenticated')
    ->name('handleShowHomeGroupDetail');

Route::get('/group/location/active', [GroupController::class, 'handleGetActiveGroupByLocation'])
    ->middleware(CheckAuth::class . ':getActiveGroupByLocation,getActiveGroupByLocationAuthenticated')
    ->name('handleGetActiveGroupByLocation');



Route::group(["middleware" => ['auth:sanctum']], function () {
    Route::apiResource('schedule', ScheduleController::class);
    Route::apiResource('/league', LeagueController::class);
    Route::apiResource('/group', GroupController::class);

    //group training
    Route::post('/store-group-training/', [GroupController::class, 'groupTraining'])->name('groupTraining.create');
    Route::get('/list-group-training/', [GroupController::class, 'listGroupTraining'])->name('list.groupTraining');
    Route::get('/show-group-training/{id}', [GroupController::class, 'showGroupTraining'])->name('show.groupTraining');
    Route::post('/update-group-training/{id}', [GroupController::class, 'updateGroupTraining'])->name('update.groupTraining');
    Route::get('/delete-group-training/{id}', [GroupController::class, 'deleteGroupTraining'])->name('delete.groupTraining');
    Route::post('/join-group-training', [GroupController::class, 'storeUserJoinGroupTraining'])->name('storeUserJoinGroupTraining');
    Route::get('/get-group-training/{id}', [GroupController::class, 'getGroupTraining'])->name('getGroupTraining');
    Route::get('/get-upcoming-group-training/{id}', [GroupController::class, 'getUpcommingGroupTraining'])->name('getUpcommingGroupTraining');
    Route::get('/list-training/', [GroupController::class, 'listTraining'])->name('list.training');
    Route::get('/get-player-group-training/{id}', [GroupController::class, 'getListPlayerGroupTraining'])->name('getListPlayerGroupTraining');
    Route::post('/active-user-join-training/', [GroupController::class, 'activeUserJoinTraining'])->name('activeUserJoinTraining');
    Route::post('/check-attend-join-training/', [GroupController::class, 'checkAttendUserJoinTraining'])->name('checkAttendUserJoinTraining');
    
    //group
    Route::post('/join-group/', [GroupController::class, 'joinGroup'])->name('join.group');
    Route::post('/active-user-join-group/', [GroupController::class, 'activeUserGroup'])->name('activeUserGroup');
    Route::get('/show-group-private/{id}', [GroupController::class, 'showGroupPrivate'])->name('showGroupPrivate');
    Route::post('/check-join-group/', [GroupController::class, 'checkJoinGroup'])->name('checkJoinGroup');
    Route::get('/group/active/{id}', [GroupController::class, 'activeGroup'])->name('group.active');
    // Route::get('/show-home-group-detail-authenticated/{id}', [GroupController::class, 'ShowHomeGroupDetailAuthenticated']);
    Route::get('/group/{id}/members', [GroupController::class, 'getGroupMembers'])->name('getGroupMembers');
    Route::get('/my-group', [GroupController::class, 'getMyGroup'])->name('getMyGroup');
    Route::post('group/update/{id}', [GroupController::class, 'update'])->name('group.update');

    //league
    Route::post('/league/update/{id}', [LeagueController::class, 'update']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/account/me', [AuthController::class, 'currentUser']);
    Route::post('/account/me', [UsersController::class, 'updateCurrentUser']);
    Route::apiResource('/users', UsersController::class);
    Route::get('/all-leagues', [LeagueController::class, 'getAllLeague']);
    Route::post('/register-league/', [LeagueController::class, 'saveRegisterLeague'])->name('registerLeague');
    Route::get('league/{slug}/users-register/', [LeagueController::class, 'listUserRegister'])->name('listUserRegister');
    Route::post('/active-user-register-league/', [LeagueController::class, 'activeUserRegister'])->name('activeUserRegister');
    Route::get('/delete-user-league/{id}/', [LeagueController::class, 'destroyPlayer'])->name('league.destroyUser');
    Route::get('/league/active/{id}', [LeagueController::class, 'activeLeague'])->name('league.active');

    Route::get('/current-user-register-league/', [LeagueController::class, 'getCurrentUserRegisterLeague'])->name('getCurrentUserRegisterLeague');
    Route::get('/leagues-player-number/', [LeagueController::class, 'getLeaguesPlayerNumber'])->name('getLeaguesPlayerNumber');

    Route::post('/chat/sent', [ChatController::class, 'sendMessage']);
    Route::get('/chat/fetch', [ChatController::class, 'fetchMessages']);

});

