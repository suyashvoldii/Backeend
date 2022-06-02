<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\User;
use App\Mail\smtpmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;



class LoginController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth:api', ['except' => ['login', 'register']]);
    // }


    // public function register(Request $request)
    // {
    // 	//Validate data
    //     $data = $request->only('name', 'email', 'password');
    //     $validator = Validator::make($data, [
    //         'name' => 'required|string',
    //         'email' => 'required|email|unique:users',
    //         'contact',
    //         'password',
    //         'dob',
    //         'role',
    //         'supervisor',
    //         'is_active',
    
    //     ]);

    //     //Send failed response if request is not valid
    //     if ($validator->fails()) {
    //         return response()->json(['error' => $validator->messages()], 400);
    //     }

    //     //Request is valid, create new user
    //     $user = User::create([
    //     	'fullname' => $request->name,
    //     	'email' => $request->email,
    //     	'password' => bcrypt($request->password),
    //         'contact'  => $request->contact,
    //         'dob' => $request->dob,
    //         'role' => $request->role,
           
    //     ]);

    //     //User created, return success response
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'User created successfully',
    //         'data' => $user
    //     ], Response::HTTP_OK);
    // }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $validator = Validator::make($credentials, [
            'email' => 'required|regex:/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/|exists:users|max:100',
            'password' => 'required|string|min:6|max:45',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        // $email = $request->input('email');
        // $user = DB::table('users')
        //         ->where('email', '=', $email)
        //         ->first();
        // if(!$user){
        //     return response()->json(['Failure'=> 'User not Found'],404);
        // } 

        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Incorrect password'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'User successfully logged out.']);
    }

    public function forgotpassword(Request $request)
    {

        $email = $request->only('email');

        $validator = Validator::make($email, [
            'email' => 'required|regex:/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/|exists:users|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $password = Str::random(8);
        $newpass = bcrypt($password);
        if(!$update = DB::table('users')
            ->where('email', '=', $email)
            ->update(['password' => $newpass])){
           return response()->json(['status' => 'Password Email Not Sent'],);
        }
        $details =[
            'password' =>  $password,
        ];  
        Mail::to($email)-> send(new smtpmail($details));
        return response()->json(['status' => 'Password Email Sent'],);
    }

    public function profile()
    {
        return response()->json(auth()->user());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}


