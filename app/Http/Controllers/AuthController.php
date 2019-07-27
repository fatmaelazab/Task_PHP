<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Validator;
use App\User;
use App\Http\Requests;
use JWTAuth;
use JWTAuthException;
use Socialite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    /**
     * @var User
     */
    private $user;
    /**
     * UserController constructor.
     * @param User $user
     */


    //     /**
    //  * Create a new AuthController instance.
    //  *
    //  * @return void
    //  */

    public function __construct(User $user){
        $this->middleware('auth:api', ['except' => ['login','register']]);
        $this->user = $user;
    }


    /**
     * registers a new user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request){
        $validator = Validator::make($request->all(),[
            'name'=>'required|string|max:255',
            'email'=>'required|string|max:255|email|unique:users',
            'password'=>'required|string|min:6',
            'is_admin'=>'required|boolean'
        ]);
        if($validator->fails()){
            return response()->json(['errors'=>$validator->errors()],400);
        }
        $user =User::where('email',$request->get('email'))->first();
        if($user){
            return response()->json(['status'=>false,'message'=>'Email Already Exists'],400);
        }
        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => bcrypt($request->get('password')),
            'is_admin' => $request->get('is_admin')
        ]);
        $user->save();
        return response()->json(['status'=>true,'message'=>'User created successfully','data'=>$user],200);
    }




    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if ($token = $this->guard()->attempt($credentials)) {
            return $this->respondWithToken($token);
        }

        return response()->json(['error' => 'Invalid username or password'], 401);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        if($this->guard()->user()){
            return response()->json($this->guard()->user());
        }
        return response()->json(['message' => 'No User']);
        
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->guard()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60
        ]);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
    }


}
