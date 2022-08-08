<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'email',
        'password',
        'team_id',
        'description',
        'image',
        'person_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function team(){
        return $this->belongsTo(Team::class);
    }

    public function invites() {
        return $this->hasMany(InviteTeam::class);
    }

    public function getInvitesAtivos($invites){

        $arr_invites = array();
        foreach($invites as $invite){
            $invitado = InviteTeam::find($invite->id);
            $team = Team::find($invitado->team_id);
            $user = User::find($invitado->user_id);
            $objInvite = [
                "id" => $invitado->id,
                'type' => $invitado->type,
                'status' => $invite->status,
                'team' => $team
            ];
            if($invitado->status === 1 && $invitado->type == "team"){
                $arr_invites[] = $objInvite;
            }
        }

        return $arr_invites;
    }
}
