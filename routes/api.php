<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\InviteTeamController;
use App\Http\Controllers\MatchesController;
use App\Http\Controllers\InviteMatchesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);    
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'team'
], function($router){ 
    Route::get('/', [TeamController::class, 'getTeamUser']);
    Route::post('/', [TeamController::class, 'store']);
    Route::post('/update', [TeamController::class, 'update']);
    Route::post('/remove', [TeamController::class, 'removeTeam']);
    
    //Route::get('/user/{id}', [TeamController::class, 'getTeamUser']);
    // Player
    Route::delete('/player/{id}', [TeamController::class, 'removePlayer']);
    Route::get('/player/add/{id}', [TeamController::class, 'addPlayer']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'player'
], function($router){ 
    Route::get('/', [PlayerController::class, 'getPlayer']);
    Route::post('/update', [PlayerController::class, 'update']);
});
Route::group([
    'middleware' => 'api',
    'prefix' => 'invite'
], function($router){ 
    //Invite
    Route::post('/player', [InviteTeamController::class, 'invitePlayer']);
    Route::post('/team', [InviteTeamController::class, 'inviteTeam']);
    Route::post('/player/accept', [InviteTeamController::class, 'acceptInvitePlayer']);
    Route::post('/player/decline', [InviteTeamController::class, 'declineInvitePlayer']);
    Route::post('/team/accept', [InviteTeamController::class, 'acceptInviteTeam']);
    Route::post('/team/decline', [InviteTeamController::class, 'declineInviteTeam']);

    Route::post('/match' , [InviteMatchesController::class, 'createInvite']);
    Route::post('/match/accept' , [InviteMatchesController::class, 'acceptInvite']);
    Route::post('/match/decline' , [InviteMatchesController::class, 'declineInvite']);
});

Route::group([
   'middleware' => 'api',
   'prefix' => 'match' 
], function ($router){

    Route::post('/', [MatchesController::class, 'store']);
    Route::get('/', [MatchesController::class, 'matchNow']);
    Route::post('/delete' , [MatchesController::class, 'delete']);
    Route::get('/{id}', [MatchesController::class, 'getMatch']);
});

Route::get('/', [MatchesController::class, 'index']);
Route::get('teams', [TeamController::class, 'index']);
Route::get('team/{id}', [TeamController::class, 'getTeam']);
Route::get('players', [PlayerController::class, 'getPlayers']);
Route::get('player/{id}', [PlayerController::class, 'getPlayerId']);
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
