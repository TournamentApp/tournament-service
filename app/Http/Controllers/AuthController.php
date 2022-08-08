<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Team;
use Validator;

class AuthController extends Controller
{

     /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
    	$validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(["message" => "Invalid data"], 422);
        }

        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(["message" => "Invalid credentials"], 401);
        }

        return $this->createNewToken($token);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
            return response()->json(['message' => $validator->errors()->first()], 400);
        }
       
        do {
            $person_id = "#" . rand(1000, 9999);
            $user = User::query()
                    ->where('person_id', $person_id)
                    ->where('name', $request->name)
                    ->select('*')
                    ->first();
        } while ($user);

        $image = "https://avatars.dicebear.com/api/micah/" . $request->name . ".svg?backgroundColor=%234767f9&height=150&width=150";
        $user = User::create(array_merge(
                    $validator->validated(),
                    ['password' => bcrypt($request->password),
                     'image' => $image,
                     'description' => $request->description,
                     'person_id' => $person_id]
                ));
                // https://avatars.dicebear.com/api/micah/vinicius.svg?backgroundColor=%234767f9&height=250&width=250
        return response()->json([
            'message' => 'User successfully registered',
            'user_id' => $user->id,
            'username' => $user->name
        ], 201);
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        auth()->logout();

        return response()->json(['message' => 'User successfully signed out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh() {
        return $this->createNewToken(auth()->refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile() {
        return response()->json(auth()->user());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token){
        $user = auth()->user();
        $captain = false;
        $team = Team::find($user->team_id);
        if($team){
            if($team->user_id == $user->id) {
                $captain = true;
            }else{
                $captain = false;
            }
            
            $teamId = $team->id;
        }else{
            $teamId = null;
        }
        
        return response()->json([
            'access_token' => $token,
            'user_id' => $user->id,
            'username' => $user->name,
            'captain' => $captain,
            'team_id' => $teamId,
        ]);
    }
}
