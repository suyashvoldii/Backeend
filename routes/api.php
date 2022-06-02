<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\AddRoleController;

Route::post('login', [LoginController::class, 'login']);
//Route::post('role',[AddRoleController::class,'addrole']);
 Route::post('forgotpassword', [LoginController::class, 'forgotpassword']);
Route::group(['middleware' => ['jwt.verify']], function() {
    
    //Login controller
    Route::post('logout', [LoginController::class, 'logout']);
   

    //Company controller
    Route::resource('company', CompanyController::class);

    //User controoler
    Route::resource('users', UserController::class);
    Route::get('supervisor', [UserController::class, 'supervisorlist']);

   
});






 // Route::post('company',[CompanyController::class,'add']);
    // Route::get('company',[CompanyController::class,'list']);
    // Route::get('company/{id}',[CompanyController::class,'detail']);  
    // Route::delete('company/{id}',[CompanyController::class,'delete']);  

// Route::post('adduser',[UserController::class,'add']);
    // Route::get('user', [UserController::class, 'userlist']);
    // Route::get('user/{id}',[UserController::class,'detail']);  
    // Route::get('supervisor/{id}',[UserController::class,'supervisorlist']);  