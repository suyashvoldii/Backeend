<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Role;
use Symfony\Component\HttpFoundation\Response;

class AddRoleController extends Controller
{
    public function addrole(Request $request)
    {
    	//Validate data
        $data = $request->only('role', 'role_name');
        $validator = Validator::make($data, [
            'role' => 'required|string|unique:roles',
            'role_name' => 'required|unique:roles',
           
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is valid, create new user
        $role = Role::create([
        	'role' => $request->role,
        	'role_name' => $request->role_name,
        
        ]);

        //User created, return success response
        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'data' => $role
        ], Response::HTTP_OK);
    }

}
