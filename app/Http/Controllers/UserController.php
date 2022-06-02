<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Validator;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(auth()->user()->role == 3){
            return response()->json([
                'Failure'=> 'You are Not authorised'
            ],Response::HTTP_UNAUTHORIZED);
        }
        elseif(auth()->user()->role == 2){
            $user = DB::table('users')
                    ->where('supervisor', '=', auth()->user()->user_id)
                    ->get();
            return $this->userlistresponse($user);
        }
        elseif(auth()->user()->role == 1){
            $user = DB::table('users')
                        ->where('company_id', '=', auth()->user()->company_id)
                        ->get();
            return $this->userlistresponse($user);
        }
        else{
            $user = User::all();
            return $this->userlistresponse($user);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $userrole = auth()->user()->role;

        if($userrole == 3 || auth()->user() == null ){
            return response()->json([
                'success' => false,
                'message' => $userrole == 3 ? 'You are not Authorised' : 'Please Login',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $data = $request->only('fname','lname','company_id', 'email', 'password','contact','dob','role','supervisor');
        $validator = Validator::make($data, [
            'fname' => 'required|string|regex:/^[a-zA-Z]+$/|max:115',
            'lname' => 'required|string|regex:/^[a-zA-Z]+$/|max:115',
            'company_id' => ($userrole == 0 ? ($request->role != 0 ? 'required|numeric|exists:companies,company_id' : 'nullable') :  'nullable'),
            'email' => 'required|regex:/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/|unique:users|max:100',
            'contact'  => 'required|regex:/^[-0-9\+]+$/|min:10|max:10',
            'password' => 'required|string|min:8|max:45',
            'dob' => 'required|date_format:d-m-Y',
            'role' =>'required|numeric|exists:roles,role',
            'supervisor' => ($userrole == 0 ? ($request->role == 3 ? 'required|numeric|exists:users,user_id' : 'nullable') : ($request->role == 3 && $userrole != 2 ? 'required|numeric|exists:users,user_id' : 'nullable')),
        ]);
      
        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        //SuperAdmin
        if($userrole == 0 ){
            $this->checksupervisor($request->supervisor);
            $company_id = $request->role == 0 ? null : $request->company_id;
            $supervisor = $request->role == 3 ? $request->supervisor : null;}
        //admin
        elseif($userrole == 1 && (!in_array($request->role,[0,1]))){
            $this->checksupervisor($request->supervisor);
            $company_id = auth()->user()->company_id;
            $supervisor = $request->role == 3 ? $request->supervisor : null;
         }
        //supervisor
        elseif($userrole == 2 && (!in_array($request->role,[0,1,2]))){
            $company_id = auth()->user()->company_id;
            $supervisor = auth()->user()->user_id;
        }
        //user
         else{
            return response()->json([
                'Invalid' => 'Not authorised',
            ]);
         }
  
        //store
        $user = User::create([
        'fullname' => $request->fname.' '.$request->lname,
        'company_id' => $company_id,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'contact'  => $request->contact,
        'dob' => $request->dob,
        'role' => $request->role,
        'supervisor'=> $supervisor ,  ]);  


        //User created, return success response
        if($user){
        return response()->json([
            'success' => true,
            'message' => 'User Added successfully',
            'data' => $user
        ], Response::HTTP_OK);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'User not added',
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(auth()->user()->role == 3){
            return response()->json([
                'Failure'=> 'You are Not authorised'
            ],Response::HTTP_UNAUTHORIZED);
        }
        elseif(auth()->user()->role == 2){
            $user = DB::table('users')
                    ->where('user_id', '=', $id)
                    ->where('supervisor', '=', auth()->user()->user_id)
                    ->first();
            return $this->userlistresponse($user);
        }
        elseif(auth()->user()->role == 1){
            $user = DB::table('users')
                         ->where('user_id', '=', $id)
                         ->where('company_id', '=', auth()->user()->company_id)
                         ->first();
            return $this->userlistresponse($user);
        }
        else{
            $user = DB::table('users')
            ->where('user_id', '=', $id)
            ->first();
            return $this->userlistresponse($user);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $userrole = auth()->user()->role;

        if(auth()->user()->role == 3 || auth()->user() == null ){
            return response()->json([
                'success' => false,
                'message' => auth()->user()->role == 3 ? 'You are not Authorised' : 'Please Login',
            ], Response::HTTP_UNAUTHORIZED);
        }

        //fetch old data
        if(!$user = User::find($id)){
            return response()->json([
                'success' => false,
                'message' => 'User Not Found',
            ], Response::HTTP_NOT_FOUND);  
        } 

        $data = $request->only('fname','lname','company_id', 'email','contact','dob','role','supervisor');
        $validator = Validator::make($data, [
            'fname' => 'required|string|regex:/^[a-zA-Z]+$/|max:115',
            'lname' => 'required|string|regex:/^[a-zA-Z]+$/|max:115',
            'company_id' => ($userrole== 0 ? ($request->role != 0 ? 'required|numeric|exists:companies,company_id' : 'nullable') :  'nullable'),
            'email' => ['required',Rule::unique('users')->ignore($user),'regex:/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/','max:100'],
            'contact'  => 'required|regex:/^[-0-9\+]+$/|min:10|max:10',
            'dob' => 'required|date_format:d-m-Y',
            'role' =>'required|numeric|exists:roles,role',
            'supervisor' => ($userrole == 0 ? ($request->role == 3 ? 'required|numeric|exists:users,user_id' : 'nullable') : 
                                ($request->role == 3 && $userrole != 2 ? 'required|numeric|exists:users,user_id' : 'nullable')),
        ]);
      
        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

      
        if($userrole == 0 ){
            $this->checksupervisor($request->supervisor);
            $company_id = $request->role == 0 ? null : $request->company_id;
            $supervisor = $request->role == 3 ? $request->supervisor : null;

        }elseif($userrole == 1 && $request->role != 0 && $request->role != 1){ //admin
            $this->checksupervisor($request->supervisor);
            $company_id = auth()->user()->company_id;
            $supervisor = $request->role == 3 ? $request->supervisor : null;
         
        }elseif($userrole == 2 && $request->role != 0 && $request->role != 1 && $request->role != 2){ //supervisor
            $company_id = auth()->user()->company_id;
            $supervisor = auth()->user()->user_id;
         
        }else{ //user
            return response()->json([
                'Invalid' => 'You are Not authorised',
            ]);
         }

        $user->fullname = $request->fname.' '.$request->lname;
        $user->company_id = $company_id;
        $user->email = $request->email; 
        $user->contact = $request->contact; 
        $user->dob = $request->dob; 
        $user->role = $request->role; 
        $user->supervisor = $supervisor; 
        $user->update();

        if($user){
        //company updated, return success response
        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ], Response::HTTP_OK);  
        }
        else{
            return response()->json([
                'Failure'=> 'NO User Found'
            ],Response::HTTP_NOT_FOUND);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if(!$user){
            return response()->json([
                'success' => false,
                'message' => 'User Not Found',
            ], Response::HTTP_NOT_FOUND);  
        }
        $deleted = $user->delete();
        if($deleted){
            return response()->json([
                'success' => true,
                'message' => 'User Deleted successfully',
            ], Response::HTTP_OK);  
        }
    }

    public function supervisorlist(){
        if(auth()->user()->role > 0){
            $user = DB::table('users')
            ->select('name','fullname','email')
            ->leftJoin('companies', 'users.company_id', '=', 'companies.company_id')
            ->where('role', '=', 2)
            ->orderBy('name', 'desc')
            ->get();
            return $this->userlistresponse($user);
        
        }elseif(auth()->user()->role == 1){
            $user = DB::table('users')
            ->where('company_id', '=', auth()->user()->company_id)
            ->where('role', '=', 2)
            ->first();
            return $this->userlistresponse($user);
        
        }else{
            return response()->json([
                'Failure'=> 'You are Not authorised'
            ],Response::HTTP_UNAUTHORIZED);
        }
    }

    

    public function checksupervisor($supervisor){
        if ($supervisor){
            $user = DB::table('users')
                       ->where('user_id', '=', $supervisor)
                       ->where('role', '=', 2)
                       ->first();
            if(!$user){
                    return response()->json([
                        'Invalid' => 'not a supervisor',
                    ]);
            }
        }
    }

    public function userlistresponse($user){
        if($user)
                return $user;
            else{
                return response()->json([
                    'Failure'=> 'No User Found'
                ],Response::HTTP_NOT_FOUND);
            } 
    }

        
}
