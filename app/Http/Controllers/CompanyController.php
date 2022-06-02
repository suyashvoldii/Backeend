<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB; 
use Illuminate\Validation\Rule; 


class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $company = Company::all();
        if($company)
            return $company;
        else{
            return response()->json([
                'Failure'=> 'NO Companies Found'
            ],Response::HTTP_NOT_FOUND);
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
        //Validate data
        $data = $request->only( 'name','contact','country','state',
                                'city',
                                'pincode',
                                'department',
                                'branch',
                                'address');
        $validator = Validator::make($data, [
            'name' => 'required|regex:/^[a-zA-ZÑñ\s]+$/|unique:companies|min:2|max:115',
            'contact' => 'required|regex:/^[-0-9\+]+$/|max:10|min:10',
            'country' => 'required|regex:/^[a-zA-ZÑñ\s]+$/|min:2|max:75',
            'state' => 'required|regex:/^[a-zA-ZÑñ\s]+$/|min:2|max:75',
            'city' => 'required|regex:/^[a-zA-ZÑñ\s]+$/|min:2|max:75',
            'pincode' => 'required|regex:/^[-0-9\+]+$/|min:5|max:6',
            'department' => 'required|regex:/^[a-zA-ZÑñ\s]+$/|min:2|max:75',
            'branch' => 'required|regex:/^[a-zA-ZÑñ\s]+$/|min:2|max:75',
            'address' => 'required|regex:/^[a-zA-ZÑñ\s]+$/|min:2|max:200',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        //Request is valid, create new user
        $company = Company::create([
        	'name' => $request->name,
        	'contact' => $request->contact,
        	'country' => $request->country,
            'state' => $request->state,
        	'city' => $request->city,
            'pincode' => $request->pincode,
        	'department' => $request->department,
            'branch' => $request->branch,
        	'address' => $request->address,
        ]);

        //User created, return success response
        return response()->json([
            'success' => true,
            'message' => 'Company created successfully',
            'data' => $company
        ], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
          // $company = Company::find($company_id);
          $company = DB::table('companies')
          ->where('company_id', '=', $id)
          ->first();
          
          if($company)
              return $company;
          else{
              return response()->json([
                  'Failure'=> 'NO Companies Found'
              ],Response::HTTP_NOT_FOUND);
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
    
        //fetch old data 
        if(!$companydetail = Company::find($id)){
            return response()->json([
                'success' => false,
                'message' => 'Company Not Found',
            ], Response::HTTP_NOT_FOUND);  
        }  
        //validate input 
        $data = $request->only( 'name','contact','country','state',
                                'city',
                                'pincode',
                                'department',
                                'branch',
                                'address');
        $validator = Validator::make($data, [
            'name' => ['required',Rule::unique('companies')->ignore($companydetail),'regex:/^[a-zA-ZÑñ\s]+$/','min:2','max:115'],
            'contact' => 'required|regex:/^[-0-9\+]+$/|max:10|min:10',
            'country' => 'required|regex:/^[a-zA-ZÑñ\s]+$/|min:2|max:75',
            'state' => 'required|regex:/^[a-zA-ZÑñ\s]+$/|min:2|max:75',
            'city' => 'required|regex:/^[a-zA-ZÑñ\s]+$/|min:2|max:75',
            'pincode' => 'required|regex:/^[-0-9\+]+$/|min:5|max:6',
            'department' => 'required|regex:/^[a-zA-ZÑñ\s]+$/|min:2|max:75',
            'branch' => 'required|regex:/^[a-zA-ZÑñ\s]+$/|min:2|max:75',
            'address' => 'required|regex:/^[a-zA-ZÑñ\s]+$/|min:2|max:200',
        ]);

        

        //Send failed response if request is not valid
        if ($validator->fails()) {
      
            return response()->json(['error' => $validator->messages()], 400);
        }

        //update company
        $companydetail->name = $request->name;
        $companydetail->contact = $request->contact;
        $companydetail->country = $request->country;
        $companydetail->state = $request->state; 
        $companydetail->city = $request->city; 
        $companydetail->pincode = $request->pincode; 
        $companydetail->department = $request->department; 
        $companydetail->branch = $request->branch; 
        $companydetail->address = $request->address; 
        $companydetail->update();
       
        if($companydetail){
          //company updated, return success response
        return response()->json([
            'success' => true,
            'message' => 'Company updated successfully',
            'data' => $companydetail
        ], Response::HTTP_OK);  
        }
        else{
            return response()->json([
                'Failure'=> 'NO Companies Found'
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
        $company = Company::find($id);
        if(!$company){
            return response()->json([
                'success' => false,
                'message' => 'Company Not Found',
            ], Response::HTTP_NOT_FOUND);  
        }
        $deleted = $company->delete();
        if($deleted){
            return response()->json([
                'success' => true,
                'message' => 'Company Deleted successfully',
            ], Response::HTTP_OK);  
        }
    }

}
