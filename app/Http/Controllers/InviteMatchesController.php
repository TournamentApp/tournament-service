<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GenericMatch;
use App\Models\Team;
use App\Models\InviteMatches;

class InviteMatchesController extends Controller
{
    public function createInvite(Request $request){

        $data = $request->all();

        $auth_user = auth()->user();
        $match = GenericMatch::find($data['match_id']);
        if($match){
            if($auth_user->team_id == $match['team_1']){ return response()->json(['message' => "It is not possible to invite the departure of your own team!"], 406); }
            if($this->verifyInviteExist($match->id, $auth_user->team_id)){
                return response()->json(['message' => "It was not possible to send the invitation, you already have an existing one for this match!"], 406);
            }else{
                if($this->verifyCaptain()){
                    $inviteMatch = InviteMatches::create([
                        "generic_match_id" => $data['match_id'],
                        "team_2" => $auth_user->team_id,
                        "status" => 1
                    ]);

                    if($inviteMatch->save()) {
                        $return = ['message' => "Invite match created successfully!"];
                    }else{ 
                        $return = ['message' => 'Error creating match inviting!'];
                    }
                }else{
                    return response()->json(['message' => "You need to be the captain to creating match inviting!"], 403);
                }
            }
        }else{
            return response()->json(['message' => "Match not found!"], 404);
        }
        return response()->json($return);
    }

    public function acceptInvite(Request $request){
        $data = $request->all();

        $auth_user = auth()->user();

        if($this->verifyCaptain()){
            $inviteMatch = InviteMatches::find($data['invite_id']);
            $inviteMatch->status = 2;

            $match = GenericMatch::find($inviteMatch->generic_match_id);
            $match->team_2 = $inviteMatch->team_2;
            $match->status = 2;
            if($match->save()) {
                $inviteMatch->update();
                return response()->json(['message' => "Invite accepted with successfully!"]);
            }else{
                return response()->json(['message' => "Invite not accepted!"]);
            }
        }else{
            return response()->json(['message' => "You need to be the captain to creating match!"], 403);
        }
    }

    public function declineInvite(Request $request){
        
        $user_logado = auth()->user();
        if($this->verifyCaptain()){
            $invite = InviteMatches::find($request->invite_id);
            $invite->status = 3;
            if($invite->save()) {
                return response()->json(['message' => "Invite declined with successfully!"]);
            }else{
                return response()->json(['message' => "Invite not declined!"]);
            }
        }else{
            return response()->json(['message' => "You need to be the captain to decline a matches invitation!"], 403);
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

    private function verifyInviteExist($match_id, $team_id){

        $invites = InviteMatches::all();

        foreach ($invites as $invite) {
            if($invite->match_id == $match_id && $invite->team_2 == $team_id) { return true;}
        }
        return false;
    }
}
