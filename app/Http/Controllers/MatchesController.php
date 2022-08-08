<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GenericMatch;
use App\Models\Team;

class MatchesController extends Controller
{
    public function store(Request $request){

        $dados = $request->all();
        $user = auth()->user();
        if($this->verifyCaptain()){

            if($dados['date'] >= date('Y-m-d')){
                $match = GenericMatch::create([
                    "team_1" => $user->team_id,
                    "status" => 1,
                    "format" => $dados['format'],
                    "date" => $dados['date'],
                    "time" => $dados['time']
                ]);
                if($match->save()) {
                    $return = ['message' => "Match created successfully!"];
                }else{ 
                    $return = ['message' => 'Error creating match!'];
                }
            }else{
                return response()->json(['message' => "It is not possible to schedule a match in the past"], 406);
            }
            
        }else{
            return response()->json(['message' => "You need to be the captain to creating match!"], 403);
        }

        return response()->json($return);
    }

    public function delete(Request $request) {
        
        $match = GenericMatch::find($request->match_id);
        if(empty($match)){ return response()->json(['message' => "Match does not exist!"]); }
        $user = auth()->user();
        
        if($user->team_id !== $match->team_1 && $user->team_id !== $match->team_2){ return response()->json(['message' => "This match could not be deleted because it is not part of your team!"]);}
        if($this->verifyCaptain()){
            
            
            $match->status = 3;
            if($match->save()) {
                $return = ['message' => "Match deleted with successfully!"];
            }else{ 
                $return = ['message' => 'Error deleting match!'];
            }
        }else{
            return response()->json(['message' => "You need to be the captain to deleting match!"], 403);
        }

        return response()->json($return);
    }

    public function index(){

        $this->loading_matches();
        $date = date('Y-m-d');
        $time = date('H:i:s');
        $matches = GenericMatch::query()
                          ->join('teams', 'teams.id', 'matches.team_1')
                          ->where('status', 1)
                          ->where('date' , '>=', $date)
                          ->select('matches.id as match_id' , 'matches.*' , 'teams.*')
                          ->paginate(8);
        if($matches){
            return response()->json($matches, 200);
        }else{
            return response()->json(['message' => "No matches found!"], 404);
        }
        
    }

    public function matchNow(){

        $user = auth()->user();
        $team = $user->team;

        $date = date('Y-m-d');
        $time = date('H:i:s');

        $match = GenericMatch::query()
                        ->where('status', 2)
                        ->where('time' , '>',now()->format('H:i:s'))
                        ->where('date' , '>=', $date)
                        ->where(function($query) use ($team) {
                            $query->where('team_1', $team->id)
                            ->orWhere('team_2', $team->id);
                        })
                        ->orderBy('time', 'ASC')
                        ->first();
        if($match){
            $matchFormatted = $this->getMatchInfo($match);
            return response()->json($matchFormatted, 200);
        }else{
            return response()->json(['message' => "No match found!"], 404);
        }
        
    }

    public function getMatch($id){

        $auth_user = auth()->user();
        $match = GenericMatch::query()
                    ->where('date', '=', date('Y-m-d'))
                    ->where('id', $id)
                    ->first();
        if($match && ($match->team_1 == $auth_user->team_id || $match->team_2 == $auth_user->team_id)){
            $str_time = $match->date.$match->time;
            $match_time = date('Y-m-d H:i:s', strtotime($str_time));
            // return response()->json($match_time);
            $limit_time = new \DateTime($match_time);
            $limit_time->modify('+3 hour');
            $limit_time = $limit_time->format('Y-m-d H:i:s');

            $before_time = new \DateTime($match_time);
            $before_time->modify('-30 minutes');
            $before_time = $before_time->format('H:i:s');
            
            if($before_time < date('Y-m-d H:i:s') && $limit_time > date('Y-m-d H:i:s')){
                
                $players = Team::find($match['team_1'])->players;
                $team = Team::find($match['team_1']);
                $team['players'] = $team->playersAtivos($players);
                $match['team_1'] = $team;

                if ($match['team_2'] != null) {
                    $team2 = Team::find($match['team_2']);
                    $players2 = Team::find($match['team_2'])->players;
                    $team2['players'] = $team->playersAtivos($players2);
                    $match['team_2'] = $team2;
                }
                
                return response()->json($match);

            }else{
                return response()->json(['message' => 'Match not yet available!'], 403);
            }
        }else{
            return response()->json(['message' => "Match not found!"], 404);
        }
    }

    private function getMatchesCreated($matches){

        $arr_matches = array();
        foreach($matches as $match){
            if($match->status !== 1){ continue; }
            
            $objMatch = [
                'id' => $match->id,
                'team_1' => Team::find($match->team_1),
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

    private function getMatchInfo($match){
        $user = auth()->user();
        if($match->team_1 == $user->team_id){

            $team = Team::find($match->team_2);

        }elseif($match->team_2 == $user->team_id){

            $team = Team::find($match->team_1);

        }

        $objMatch = [
            'id' => $match->id,
            'format' => $match->format,
            'date' => $match->date,
            'time' => $match->time,
            'team' => $team
        ];

        return $objMatch;
    }
    private function format_date_db($date){
        $date = explode('/', $date);
        $new_date = $date[2].'-'.$date[1].'-'.$date[0];
        return $new_date;
    }

    private function format_date_br($date){
        $date = explode('-', $date);
        $new_date = $date[2].'/'.$date[1].'/'.$date[0];
        return $new_date;
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
    private function loading_matches(){

        $matches = GenericMatch::all();
        foreach ($matches as $match) {
            if($match->status != 1){ continue; }
            if($match->date == date('Y-m-d') && $match->time < date('H:i:s')) {
                $match->status = 3;
                $match->update();
            }
        }
    }
}
