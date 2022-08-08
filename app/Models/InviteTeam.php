<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InviteTeam extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'status', 'team_id', 'user_id'];

    protected $table = 'invite_teams';

    public function team(){
        return $this->belongsTo(Team::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }

}
