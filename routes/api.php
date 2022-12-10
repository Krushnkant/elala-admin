<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ExperienceController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\OrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('register_user',[UserController::class,'register_user']);
Route::post('login', [AuthController::class, 'login']);
Route::post('verify_otp', [AuthController::class, 'verify_otp']);
Route::post('forgetpassword', [AuthController::class, 'forgetpassword']);
Route::post('resetpassword', [AuthController::class, 'resetpassword']);



Route::group(['middleware' => 'auth:api'], function(){

    Route::post('addExperienceType', [ExperienceController::class,'addExperienceType']);
    Route::post('addExperienceLocation', [ExperienceController::class,'addExperienceLocation']);
    Route::post('addExperienceCategory', [ExperienceController::class,'addExperienceCategory']);
    Route::post('addExperienceDetails', [ExperienceController::class,'addExperienceDetails']);
    Route::post('addExperienceAgeGroup', [ExperienceController::class,'addExperienceAgeGroup']);
    Route::post('addExperienceMedia', [ExperienceController::class,'addExperienceMedia']);
    Route::post('addExperienceAgeGroup', [ExperienceController::class,'addExperienceAgeGroup']);
    Route::post('addExperienceBrindItem', [ExperienceController::class,'addExperienceBrindItem']);
    Route::post('addExperienceProvideItem', [ExperienceController::class,'addExperienceProvideItem']);
    Route::post('addExperienceMeetLocation', [ExperienceController::class,'addExperienceMeetLocation']);
    Route::post('addExperienceMaxGroupSize', [ExperienceController::class,'addExperienceMaxGroupSize']);
    Route::post('addExperienceScheduleTime', [ExperienceController::class,'addExperienceScheduleTime']);
    Route::post('addExperiencePrice', [ExperienceController::class,'addExperiencePrice']);
    Route::post('addDiscountGroup', [ExperienceController::class,'addDiscountGroup']);
    Route::post('addExperienceCancelletionPolicy', [ExperienceController::class,'addExperienceCancelletionPolicy']);
    Route::post('addExperienceCategoryAttribute', [ExperienceController::class,'addExperienceCategoryAttribute']);
    Route::get('removeExperience', [ExperienceController::class,'removeExperience']);
    Route::get('getExperience', [ExperienceController::class,'getExperience']);
    Route::get('getExperiences', [ExperienceController::class,'getExperiences']);
    Route::get('otherlist',[ExperienceController::class,'otherlist']);
    Route::get('city/{text}',[ExperienceController::class,'city']);

    Route::post('createorder', [OrderController::class,'createorder']);
    Route::post('getHostOrders', [OrderController::class,'getHostOrders']);
    Route::post('getMyOrders', [OrderController::class,'getMyOrders']);

    Route::get('getOrderCalender/{month}/{year}', [OrderController::class,'getOrderCalender']);

    Route::post('EditProfile', [ExperienceController::class,'EditProfile']);
    
});


Route::get('getHomeExperiences', [ExperienceController::class,'getHomeExperiences']);
Route::get('experienceDetails/{id}', [ExperienceController::class,'experienceDetails']);
Route::get('getRelatedExperiences/{id}', [ExperienceController::class,'getRelatedExperiences']);
Route::get('getReviewExperiences/{id}', [ExperienceController::class,'getReviewExperiences']);
Route::get('getAvailableTimeExperiences/{id}/{day}', [ExperienceController::class,'getAvailableTimeExperiences']);







