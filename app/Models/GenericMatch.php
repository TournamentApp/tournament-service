<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GenericMatch extends Model
{
    use HasFactory;

    protected $fillable = ['team_1', 'team_2', 'status', 'format', 'date', 'time'];

    protected $table = 'matches';

    public function challenger(){
        return $this->belongsTo(Team::class, 'foreign_key', 'team_1');
    }

    public function oponnent(){
        return $this->belongsTo(Team::class, 'foreign_key', 'team_2');
    }

    public function inviteMatches(){
        return $this->hasMany(InviteMatches::class);
    }
}
