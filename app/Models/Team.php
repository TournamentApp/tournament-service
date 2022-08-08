<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'tag', 'description', 'image', 'user_id'];

    protected $table = 'teams';

    public function players() {
        return $this->hasMany(User::class);
    }

    public function invites() {
        return $this->hasMany(InviteTeam::class);
    }

    public function matches() {
        return $this->hasMany(GenericMatch::class, 'team_1');
    }

    public function matches_visitors(){
        return $this->hasMany(GenericMatch::class, 'team_2');
    }

    public function invite_matches(){
        return $this->hasMany(InviteMatches::class, 'team_2');
    }

    public function playersAtivos($players){

        $arr_players = array();

        foreach ($players as $player){

            $objPlayer = [
                "id" => $player->id,
                'name' => $player->name,
                'image' => $player->image,
                'person_id' => $player->person_id,
                'description' => $player->description,
                'created_at' => $player->created_at,
            ];
            $arr_players[] = $objPlayer;
        }

        return $arr_players;

    }
    public function getInvitesAtivos($invites){

        $arr_invites = array();
        foreach($invites as $invite){
            $invitado = InviteTeam::find($invite->id);

            $player = $invitado->user;
            $objPlayer = [
                "id" => $player->id,
                'name' => $player->name,
                'image' => $player->image,
                'person_id' => $player->person_id,
                'description' => $player->description,
                'created_at' => $player->created_at,
            ];
            $objInvite = [
                "id" => $invitado->id,
                'type' => $invitado->type,
                'status' => $invite->status,
                'team' => $invitado->team,
                'player' => $objPlayer
            ];
            if($invitado->status === 1 && $invitado->type == "player"){
                $arr_invites[] = $objInvite;
            }
        }

        return $arr_invites;
    }

    public function getInvitesAceitos($invites){

        $arr_invites = array();
        foreach($invites as $invite){
            $invitado = InviteTeam::find($invite->id);
            $player = $invitado->user;
            $objPlayer = [
                "id" => $player->id,
                'name' => $player->name,
                'image' => $player->image,
                'person_id' => $player->person_id,
                'description' => $player->description,
                'created_at' => $player->created_at,
            ];
            $objInvite = [
                "id" => $invitado->id,
                'type' => $invitado->type,
                'status' => $invite->status,
                'team' => $invitado->team,
                'player' => $objPlayer
            ];
            if($invitado->status === 2 && $invitado->type == "player"){
                $arr_invites[] = $objInvite;
            }
        }

        return $arr_invites;
    }
    public function getInvitesRecusados($invites){

        $arr_invites = array();
        foreach($invites as $invite){
            $invitado = InviteTeam::find($invite->id);

            $player = $invitado->user;
            $objPlayer = [
                "id" => $player->id,
                'name' => $player->name,
                'image' => $player->image,
                'person_id' => $player->person_id,
                'description' => $player->description,
                'created_at' => $player->created_at,
            ];
            $objInvite = [
                "id" => $invitado->id,
                'type' => $invitado->type,
                'status' => $invite->status,
                'team' => $invitado->team,
                'player' => $objPlayer
            ];
            if($invitado->status === 3 && $invitado->type == "player"){
                $arr_invites[] = $objInvite;
            }
        }

        return $arr_invites;
    }

    public function getInvitesMatchesRecebidos($matches){
        
        $arr_invites = array();
        foreach($matches as $match){
            if($match->status !== 1){ continue; }
            $invites = $match->inviteMatches;
            foreach($invites as $invite){
                $team = Team::find($invite->team_2);
                $team_1 = Team::find($match->team_1);
                $match['team_1'] = $team_1;
                $objInvite = [
                    "id" => $invite->id,
                    "match" => $match,
                    'team' => $team,
                    'status' => $invite->status,
                    'created_at' => $invite->created_at,
                    'updated_at' => $invite->updated_at
                ];
                if($invite->status === 1){
                    $arr_invites[] = $objInvite;
                }
            }  
        }
        return $arr_invites;
    }

    public function getInvitesMatchesEnviados($invites){

        $arr_invites = array();
        foreach($invites as $invite){
            $match = GenericMatch::find($invite->match_id);
            if($match->status !== 1){ continue; }
            $team = Team::find($match->team_1);
            $match['team_1'] = $team;
            $objInvite = [
                'id' => $invite->id,
                'match' => $match,
                'team_2' => $invite->team_2,
                'status' => $invite->status,
                'created_at' => $invite->created_at,
                'updated_at' => $invite->updated_at
            ];
            if($invite->status === 1){
                $arr_invites[] = $objInvite;
            }
        }

        return $arr_invites;
    }

    public function getMatchesAccepted($matches, $type = null){

        
        $arr_matches = array();
        foreach($matches as $match){
            if($match->status !== 2){ continue; }

            $match_time = date('H:i:s', strtotime($match->time));
            $limit_time = new \DateTime($match_time);
            $limit_time->modify('+3 hour');
            $limit_time = $limit_time->format('H:i:s');

            $before_time = new \DateTime($match_time);
            $before_time->modify('-30 minutes');
            $before_time = $before_time->format('H:i:s');

            if($match->date == date('Y-m-d') && ($limit_time < date('H:i:s') )){
                $match->status = 4;
                $match->update();
                continue;
            }
            if($type === "visitors"){
                $team = Team::find($match->team_1);
            }else{
                $team = Team::find($match->team_2);
            }
            $objMatch = [
                'id' => $match->id,
                'team_1' => Team::find($match->team_1),
                'team_2' => Team::find($match->team_2),
                'status' => $match->status,
                'format' => $match->format,
                'date' => $match->date,
                'time' => $match->time,
                'team_adversary_image' => $team->image,
                'team_adversary_name' => $team->name,
                'created_at' => $match->created_at,
                'updated_at' => $match->updated_at
            ];
            $arr_matches[] = $objMatch;
        }

        return $arr_matches;
    }
    public function getMatchesCreated($matches){

        $arr_matches = array();
        foreach($matches as $match){
            if($match->status !== 1){ continue; }
            
            $objMatch = [
                'id' => $match->id,
                'team_1' => Team::find($match->team_1),
                'team_2' => Team::find($match->team_2),
                'status' => $match->status,
                'format' => $match->format,
                'date' => $match->date,
                'time' => $match->time,
                'created_at' => $match->created_at,
                'updated_at' => $match->updated_at
            ];
            $arr_matches[] = $objMatch;
        }

        return $arr_matches;
    }
}
