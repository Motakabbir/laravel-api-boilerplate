<?php

use App\Http\Controllers\Api\TreeEntityController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserRoleAccessController;
use App\Http\Controllers\Api\UserRoleInfoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::prefix('auth')->group(function(){
    Route::controller(UserController::class)->group(function(){
        Route::post('login', 'UserLogin');
        Route::post('register', 'UserSignup');
        Route::post('forgot-password', 'ForgotPassword');
        Route::post('activate-account', 'UserActivation');
        Route::post('code-generate', 'AuthCodeGenerator');
    });
});
Route::middleware('auth:sanctum')->group(function(){
    
    Route::prefix('profile')->group(function(){
        Route::controller(UserController::class)->group(function(){
            Route::get('/', 'UserInfo');
            Route::post('/update', 'updateProfile');
            Route::post('/my-pets', 'showPetInfos');
        });        
    });

    Route::apiResource('user-role-infos', UserRoleInfoController::class);  
    Route::controller(UserRoleInfoController::class)->group(function(){
        Route::post('user-role-infos/permission/update', 'pupdate')->name('user-role-infos.premission.update');
        Route::get('user-role-infos/premission/{user_role_info}/edit', 'permissionShow')->name('user-role-infos.premission.edit');
    });
    Route::apiResource('menu-managements', TreeEntityController::class);

    Route::controller(TreeEntityController::class)->group(function(){
         Route::post('/menu-managements/menu', 'buildmenu')->name('menu-managements.buildmenu');
         Route::post('/menu-managements/save-position', 'updatemenu')->name('menu-managements.updatemenu');      
         Route::post('/menu-managements/treemenu', 'treemenu_new')->name('menu-managements.tree-menu');
    });        
        
});






