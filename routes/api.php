<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\{AuthController, ExperienceController, UserController, OrderController, WishlistController, ChatController,OtherController,PostController,PaymentController,FollowController};

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

Route::post('register_user', [UserController::class, 'register_user']);
Route::post('login', [AuthController::class, 'login']);
Route::post('verify_otp', [AuthController::class, 'verify_otp']);
Route::post('forgetpassword', [AuthController::class, 'forgetpassword']);
Route::post('resetpassword', [AuthController::class, 'resetpassword']);
Route::post('emailsend', [AuthController::class, 'emailsend']);
Route::any('phonepe-response', [PaymentController::class, 'response'])->name('response');


Route::group(['middleware' => 'auth:api'], function () {
    Route::post('payment_initiate', [PaymentController::class, 'payment_initiate']);

    Route::post('addExperienceType', [ExperienceController::class, 'addExperienceType']);
    Route::post('addExperienceLocation', [ExperienceController::class, 'addExperienceLocation']);
    Route::post('addExperienceCategory', [ExperienceController::class, 'addExperienceCategory']);
    Route::post('addExperienceDetails', [ExperienceController::class, 'addExperienceDetails']);
    Route::post('addExperienceAgeGroup', [ExperienceController::class, 'addExperienceAgeGroup']);
    Route::post('addExperienceMedia', [ExperienceController::class, 'addExperienceMedia']);
    Route::post('addExperienceAgeGroup', [ExperienceController::class, 'addExperienceAgeGroup']);
    Route::post('addExperienceBrindItem', [ExperienceController::class, 'addExperienceBrindItem']);
    Route::post('addExperienceProvideItem', [ExperienceController::class, 'addExperienceProvideItem']);
    Route::post('addExperienceMeetLocation', [ExperienceController::class, 'addExperienceMeetLocation']);
    Route::post('addExperienceMaxGroupSize', [ExperienceController::class, 'addExperienceMaxGroupSize']);
    Route::post('addExperienceScheduleTime', [ExperienceController::class, 'addExperienceScheduleTime']);
    Route::post('addExperiencePrice', [ExperienceController::class, 'addExperiencePrice']);
    Route::post('addDiscountGroup', [ExperienceController::class, 'addDiscountGroup']);
    Route::post('addExperienceCancelletionPolicy', [ExperienceController::class, 'addExperienceCancelletionPolicy']);
    Route::post('addExperienceCategoryAttribute', [ExperienceController::class, 'addExperienceCategoryAttribute']);
    Route::get('removeExperience', [ExperienceController::class, 'removeExperience']);
    Route::get('getExperience', [ExperienceController::class, 'getExperience']);
    Route::get('getExperiences', [ExperienceController::class, 'getExperiences']);
    Route::get('otherlist', [ExperienceController::class, 'otherlist']);
    Route::get('city/{text}', [ExperienceController::class, 'city']);
    Route::get('removeMediaExperience', [ExperienceController::class, 'removeMediaExperience']);

    Route::post('checkorderslot', [OrderController::class, 'checkorderslot']);
    Route::post('availableprivategroupdate', [OrderController::class, 'availableprivategroupdate']);

    Route::post('createorder', [OrderController::class, 'createorder']);
    Route::post('getHostOrders', [OrderController::class, 'getHostOrders']);
    Route::post('getMyOrders', [OrderController::class, 'getMyOrders']);
    Route::get('getOrderDetails/{id}', [OrderController::class, 'getOrderDetails']);

    Route::post('add_review',[OrderController::class,'add_review']);
    

    Route::get('getOrderCalender/{month}/{year}', [OrderController::class, 'getOrderCalender']);

    Route::post('editProfile', [UserController::class, 'editProfile']);

    Route::post('update_wishlist', [WishlistController::class, 'update_wishlist']);
    Route::get('wishlistitem_list', [WishlistController::class, 'wishlistitem_list']);

    Route::post('addEditBank', [UserController::class, 'addEditBank']);
    Route::get('getBank', [UserController::class, 'getBank']);

    // pooja
    Route::post('create-chat', [ChatController::class, 'CreateChat']);
    Route::get('get-all-chat/{id}', [ChatController::class, 'GetAllChat']);
    Route::get('personal-chat/{user_id}/{receiver_id}', [ChatController::class, 'PersonalChat']);
    Route::get('unread-msg-count', [ChatController::class, 'UnreadMessageCount']);
    Route::get('get_all_unread_msg_count', [ChatController::class, 'GetAllUnreadMessageCount']);

    Route::post('createPost', [PostController::class, 'create_post']);
    Route::post('myPost', [PostController::class, 'get_my_posts']);
    Route::post('deletePost', [PostController::class, 'delete_post']);

    Route::post('likePost', [PostController::class, 'like_post']);
    Route::post('likePostUser', [PostController::class, 'like_post_users']);

    Route::post('commantPost', [PostController::class, 'commant_post']);
    Route::post('commantPostUser', [PostController::class, 'commant_post_users']);
   
   
    Route::get('getUsers', [UserController::class, 'getUser']);

    Route::get('paymentHistory', [PaymentController::class, 'paymentHistory']);
    Route::post('nextlastpayment', [PaymentController::class, 'nextlastpayment']);
    Route::post('pastpayment', [PaymentController::class, 'pastpayment']);
    Route::post('upcomingpayment', [PaymentController::class, 'upcomingpayment']);
    Route::post('pastupcomingpayment', [PaymentController::class, 'pastupcomingpayment']);


    Route::post("follow", [FollowController::class, 'follow']);
    Route::post("unfollow", [FollowController::class, 'unfollow']);
    Route::post("follow_request", [FollowController::class, 'follow_request']);
    Route::post("getFollower", [FollowController::class, 'getFollower']);
    Route::post("getRandomUsers", [FollowController::class, 'getRandomUsers']);
});

Route::get('settings', [UserController::class, 'settings']);
Route::get('getHomeExperiences', [ExperienceController::class, 'getHomeExperiences']);
Route::get('experienceDetails/{id}', [ExperienceController::class, 'experienceDetails']);
Route::get('getRelatedExperiences/{id}', [ExperienceController::class, 'getRelatedExperiences']);
Route::get('getReviewExperiences/{id}', [ExperienceController::class, 'getReviewExperiences']);
Route::get('getAvailableTimeExperiences/{id}/{day}', [ExperienceController::class, 'getAvailableTimeExperiences']);
Route::post('getFilterExperiences', [ExperienceController::class, 'getFilterExperiences']);

Route::get('getTeamMember', [OtherController::class, 'getTeamMember']);
Route::get('getTestimonial', [OtherController::class, 'getTestimonial']);
Route::get('infopage', [OtherController::class, 'infopage']);
Route::post('contact', [OtherController::class, 'contact']);
Route::get('getFaq', [OtherController::class, 'getFaq']);
Route::get('getCount', [OtherController::class, 'getCount']);

Route::post('allPost', [PostController::class, 'get_all_posts']);
Route::post('viewProfile', [UserController::class, 'viewProfile']);
Route::post('getMyExperienceReview', [UserController::class, 'getMyExperienceReview']);
Route::post('getMyReview', [UserController::class, 'getMyReview']);


Route::post('getCountry', [UserController::class, 'getCountry']);
Route::post('getState', [UserController::class, 'getState']);
Route::post('getCity', [UserController::class, 'getCity']);

