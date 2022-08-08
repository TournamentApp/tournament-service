<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\User;
use App\Models\InviteTeam;

class InviteTeamController extends Controller
{
    public function index(){

        return response()->json(['message' => 'teste']);
    }

    public function invitePlayer(Request $request){
        
        $user_logado = auth()->user();
        $user_invitado = User::find($request->user_id);
        if($user_invitado->team_id != null){ return response()->json(['message' => "This player is already on a team!"], 406);}
        if($user_invitado){    
            if($this->verifyCaptain()){
                $team = Team::find($user_logado->team_id);
                if($this->verifyInviteExist($team->id, $request->user_id, $request->type)){
                    return response()->json(['message' => "It was not possible to send the invitation, you already have an existing one for this player!"], 406);
                }else{
                    $invite = InviteTeam::create([
                        "type" => $request->type,
                        "status" => 1,
                        "team_id" => $user_logado->team_id,
                        "user_id" => $request->user_id
                    ]);
                    if($invite->save()) {
                        $return = ['message' => "Successfully invited player!"];
                    }else{ 
                        $return = ['message' => 'Error inviting the player to the team!'];
                    }
                }
                return response()->json($return);
            }else{
                return response()->json(['message' => "You need to be the captain to invite a player!"], 403);
            }
        }
    }

    public function inviteTeam(Request $request){

        $user_logado = auth()->user();

        if($user_logado->team_id != null){ return response()->json(['message' => "You cannot join another team, if you are already in one!"], 406); }
        if($this->verifyInviteExist($request->team_id, $user_logado->id, $request->type)){
            return response()->json(['message' => "It was not possible to send the invitation, you already have an existing one for this team!"], 406);
        }else{
            $invite = InviteTeam::create([
                "type" => $request->type,
                "status" => 1,
                "team_id" => $request->team_id,
                "user_id" => $user_logado->id
            ]);
            if($invite->save()) {
                $return = ['message' => "Success in sending team invitations!"];
            }else{ 
                $return = ['message' => 'Error sending team invitation!'];
            }
        }
        
        return response()->json($return);
    }

    public function acceptInviteTeam(Request $request){
        
        $user_logado = auth()->user();
        $invite = InviteTeam::find($request->invite_id);

        if($invite->user_id == $user_logado->id){
            $team = Team::find($request->team_id);
            $invite->status = 2;
            
            $user_logado->team_id = $team->id;
            if($user_logado->save()) {
                $invite->save();
                $return = ['message' => 'Invitation successfully accepted!', 'team_id' => $team->id];
            }else{ 
                $return = ['message' => 'Error accepting invitation!'];
            }
        }
        
        return response()->json($return);
        
    }

    public function declineInviteTeam(Request $request){
        
        $user_logado = auth()->user();
        $invite = InviteTeam::find($request->invite_id);
        $invite->status = 2;
        $invite->save();
        $return = ['message' => "Invite declined with successfully!"];
        return response()->json($return);
    }

    public function acceptInvitePlayer(Request $request){
        
        $user_logado = auth()->user();
        if($this->verifyCaptain()){
            $team = Team::find($user_logado->team_id);
            $user = User::find($request->user_id);
            $invite = InviteTeam::find($request->invite_id);
            if($invite->user_id == $user->id && $invite->team_id == $team->id){
                $user->team_id = $team->id;
                $user->update();
                $invite->status = 2;
                $invite->update();
                return response()->json(['message' => "Invite accepted with successfully!"]);
            }else{
                return response()->json(['message' => "Invite not accepted!"]);
            }  
        }else{
            return response()->json(['message' => "You need to be the captain to accept a player's invitation!"], 403);
        }

    }

    public function declineInvitePlayer(Request $request){
        
        $user_logado = auth()->user();
        if($this->verifyCaptain()){
            $invite = InviteTeam::find($request->invite_id);
            $invite->status = 3;
            $invite->save();

            return response()->json(['message' => "Invite declined with successfully!"]);
        }else{
            return response()->json(['message' => "You need to be the captain to decline a player's invitation!"], 403);
        }

    }

    private function verifyCaptain(){
        $auth_user = auth()->user();
        $team = Team::find($auth_user->team_id);
        if($team){
            if($team->user_id == $auth_user->id){
                return true;
            }else{
                return false;
            }
        }
    }

    public function verifyInviteExist($team_id, $user_id, $type){

        $invites = InviteTeam::all();

        foreach ($invites as $invite) {

            if($invite->type == $type) {

                if($invite->team_id == $team_id && $invite->user_id == $user_id) { return true;}

            }
        }
        return false;
    }
}
