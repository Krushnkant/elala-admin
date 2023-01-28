<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\{AuthController,DashboardController,UserController,EndUserController,ProfileController,DesignationController
    ,CategoryController,CategoryAttributeController,SettingsController,LanguageController,AgeGroupController,CancellationPolicyController
    ,OrderController,TeamMemberController,TestimonialController};
use App\Http\Controllers\admin\{ExperienceController,ReviewController,InfopageController,FaqController,PostController,PaymentController};

use Illuminate\Support\Facades\Artisan;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/clear-cache', function() {
    //dd(bcrypt('123456'));
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    return "Cache is cleared";
});



//Admin  Rpute
Route::get('admin',[AuthController::class,'index'])->name('admin.login');
Route::post('adminpostlogin', [AuthController::class, 'postLogin'])->name('admin.postlogin');
Route::get('logout', [AuthController::class, 'logout'])->name('admin.logout');
Route::get('admin/403_page',[AuthController::class,'invalid_page'])->name('admin.403_page');
Route::get('verify/{text}', [AuthController::class, 'verify_email']);

Route::group(['prefix'=>'admin','middleware'=>['auth','userpermission'],'as'=>'admin.'],function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    //admin user route
    Route::get('users',[UserController::class,'index'])->name('users.list');
    Route::post('addorupdateuser',[UserController::class,'addorupdateuser'])->name('users.addorupdate');
    Route::post('alluserslist',[UserController::class,'alluserslist'])->name('alluserslist');
    Route::get('changeuserstatus/{id}',[UserController::class,'changeuserstatus'])->name('users.changeuserstatus');
    Route::get('users/{id}/edit',[UserController::class,'edituser'])->name('users.edit');
    Route::get('users/{id}/delete',[UserController::class,'deleteuser'])->name('users.delete');
    Route::get('users/{id}/permission',[UserController::class,'permissionuser'])->name('users.permission');
    Route::post('savepermission',[UserController::class,'savepermission'])->name('users.savepermission');

    //customer route
    Route::get('end_users',[EndUserController::class,'index'])->name('end_users.list');
    Route::post('addorupdateEnduser',[EndUserController::class,'addorupdateEnduser'])->name('end_users.addorupdate');
    Route::post('allEnduserlist',[EndUserController::class,'allEnduserlist'])->name('allEnduserlist');
    Route::get('changeEnduserstatus/{id}',[EndUserController::class,'changeEnduserstatus'])->name('end_users.changeEnduserstatus');
    Route::get('end_users/{id}/delete',[EndUserController::class,'deleteEnduser'])->name('end_users.delete');
    //Route::get('end_users/{id}/edit',[EndUserController::class,'editenduser'])->name('end_users.edit');

    //designation route
    Route::get('designation',[DesignationController::class,'index'])->name('designation.list');
    Route::post('addorupdatedesignation',[DesignationController::class,'addorupdatedesignation'])->name('designation.addorupdate');
    Route::post('alldesignationslist',[DesignationController::class,'alldesignationslist'])->name('alldesignationslist');
    Route::get('changedesignationstatus/{id}',[DesignationController::class,'changedesignationstatus'])->name('designation.changedesignationstatus');
    Route::get('designation/{id}/edit',[DesignationController::class,'editdesignation'])->name('designation.edit');
    Route::get('designation/{id}/delete',[DesignationController::class,'deletedesignation'])->name('designation.delete');
    Route::get('designation/{id}/permission',[DesignationController::class,'permissiondesignation'])->name('designation.permission');
    Route::post('designation/savepermission',[DesignationController::class,'savepermission'])->name('designation.savepermission');

    //category route
    Route::get('categorieslist/{id?}',[CategoryController::class,'index'])->name('categories.list');
    Route::get('categories/create',[CategoryController::class,'create'])->name('categories.add');
    Route::post('categories/save',[CategoryController::class,'save'])->name('categories.save');
    Route::post('allcategorylist',[CategoryController::class,'allcategorylist'])->name('allcategorylist');
    Route::get('changecategorystatus/{id}',[CategoryController::class,'changecategorystatus'])->name('categories.changecategorystatus');
    Route::get('categories/{id}/delete',[CategoryController::class,'deletecategory'])->name('categories.delete');
    Route::get('categories/{id}/edit',[CategoryController::class,'editcategory'])->name('categories.edit');
    Route::post('categories/uploadfile',[CategoryController::class,'uploadfile'])->name('categories.uploadfile');
    Route::post('categories/removefile',[CategoryController::class,'removefile'])->name('categories.removefile');
    Route::get('categories/checkparentcat/{id}',[CategoryController::class,'checkparentcat'])->name('categories.checkparentcat');

    Route::get('addcategoryattribute/{id}',[CategoryAttributeController::class,'addcategoryattribute'])->name('categories.addcategoryattribute');
    Route::post('categoryattribute/store',[CategoryAttributeController::class,'categoryattributestore'])->name('categoryattribute.store');

    //experience route
    Route::get('experience',[ExperienceController::class,'index'])->name('experience.list');
    Route::post('allexperiencelist',[ExperienceController::class,'allexperiencelist'])->name('allcategorylist');
    Route::post('experience/save',[ExperienceController::class,'save'])->name('experience.save');
    Route::get('experience/{id}/edit',[ExperienceController::class,'editexperience'])->name('experience.edit');
    Route::get('changeexperiencestatus/{id}',[ExperienceController::class,'changeexperiencestatus'])->name('experience.changeexperiencestatus');
    Route::get('experience/{id}/delete',[ExperienceController::class,'deleteexperience'])->name('experience.delete');
    Route::post('experience/removefile',[ExperienceController::class,'removefile'])->name('experience.removefile');
    Route::post('change_experience_status',[ExperienceController::class,'change_experience_status'])->name('experience.change_experience_status');
    Route::post('experience/uploadfile',[ExperienceController::class,'uploadfile'])->name('experience.uploadfile');

    //Language

    Route::get('languages',[LanguageController::class,'index'])->name('languages.list');
    Route::post('addorupdatelanguage',[LanguageController::class,'addorupdatelanguage'])->name('languages.addorupdate');
    Route::post('alllanguageslist',[LanguageController::class,'alllanguageslist'])->name('alllanguageslist');
    Route::get('language/{id}/edit',[LanguageController::class,'editlanguage'])->name('languages.edit');
    Route::get('language/{id}/delete',[LanguageController::class,'deletelanguage'])->name('languages.delete');
    Route::get('chagelanguagestatus/{id}',[LanguageController::class,'chagelanguagestatus'])->name('languages.chagelanguagestatus');

    //Age Group
    Route::get('agegroups',[AgeGroupController::class,'index'])->name('agegroups.list');
    Route::post('addorupdateagegroups',[AgeGroupController::class,'addorupdateagegroups'])->name('agegroups.addorupdate');
    Route::post('allagegroupslist',[AgeGroupController::class,'allagegroupslist'])->name('allagegroupslist');
    Route::get('agegroups/{id}/edit',[AgeGroupController::class,'editagegroups'])->name('agegroups.edit');
    Route::get('agegroups/{id}/delete',[AgeGroupController::class,'deleteagegroup'])->name('agegroups.delete');
    Route::get('changeagegroupstatus/{id}',[AgeGroupController::class,'changeagegroupstatus'])->name('agegroups.chageagegroupstatus');

    //Cancellation Policies

    Route::get('policy',[CancellationPolicyController::class,'index'])->name('policy.list');
    Route::post('addorupdatepolicy',[CancellationPolicyController::class,'addorupdatepolicy'])->name('policy.addorupdate');
    Route::post('allpolicylist',[CancellationPolicyController::class,'allpolicylist'])->name('allpolicylist');
    Route::get('policy/{id}/edit',[CancellationPolicyController::class,'editpolicy'])->name('policy.edit');
    Route::get('policy/{id}/delete',[CancellationPolicyController::class,'deletepolicy'])->name('policy.delete');
    Route::get('chagepolicystatus/{id}',[CancellationPolicyController::class,'chagepolicystatus'])->name('policy.chagepolicystatus'); 

    //Setting 
    Route::get('settings',[SettingsController::class,'index'])->name('settings.list');
    Route::post('updateSetting',[SettingsController::class,'updateSetting'])->name('settings.updateSetting');
    Route::get('settings/edit',[SettingsController::class,'editSettings'])->name('settings.edit');

    //Orders
    Route::get('orders',[OrderController::class,'index'])->name('orders.list');
    Route::post('allOrderlist',[OrderController::class,'allOrderlist'])->name('allOrderlist');

    //team members
    Route::get('teammembers',[TeamMemberController::class,'index'])->name('teammembers.list');
    Route::post('allteamslist',[TeamMemberController::class,'allteamslist'])->name('allteamslist');
    Route::get('changeteamstatus/{id}',[TeamMemberController::class,'changeteamstatus'])->name('teammembers.changeteamstatus');
    Route::post('addorupdateteam',[TeamMemberController::class,'addorupdateteam'])->name('teammembers.addorupdateteam');
    Route::get('teammembers/{id}/edit',[TeamMemberController::class,'editteam'])->name('teammembers.edit');
    Route::get('teammembers/{id}/delete',[TeamMemberController::class,'deleteteam'])->name('teammembers.delete');

    //testimonials
    Route::get('testimonials',[TestimonialController::class,'index'])->name('testimonials.list');
    Route::post('alltestimonialslist',[TestimonialController::class,'alltestimonialslist'])->name('alltestimonialslist');
    Route::get('changetestimonialstatus/{id}',[TestimonialController::class,'changetestimonialstatus'])->name('testimonials.changetestimonialstatus');
    Route::post('addorupdatetestimonial',[TestimonialController::class,'addorupdatetestimonial'])->name('testimonials.addorupdatetestimonial');
    Route::get('testimonials/{id}/edit',[TestimonialController::class,'edittestimonial'])->name('testimonials.edit');
    Route::get('testimonials/{id}/delete',[TestimonialController::class,'deletetestimonial'])->name('testimonials.delete');

    //Review
    Route::get('review',[ReviewController::class,'index'])->name('review.list');
    Route::post('allReviewlist',[ReviewController::class,'allReviewlist'])->name('allReviewlist');
    Route::get('rejectstatus/{id}',[ReviewController::class,'rejectstatus'])->name('review.rejectstatus');
    Route::get('acceptstatus/{id}',[ReviewController::class,'acceptstatus'])->name('review.acceptstatus');

    //Info Page
    Route::get('aboutus',[InfopageController::class,'aboutus'])->name('infopage.about');
    Route::get('infopage/edit',[InfopageController::class,'edit'])->name('infopage.edit');
    Route::post('updateInfopage',[InfopageController::class,'update'])->name('infopage.update');
    Route::get('contactus',[InfopageController::class,'contactus'])->name('infopage.contact');
    Route::get('privacy_policy',[InfopageController::class,'privacy_policy'])->name('infopage.privacy_policy');
    Route::get('terms_condition',[InfopageController::class,'terms_condition'])->name('infopage.terms_condition');

    //Faq
    Route::get('faqs',[FaqController::class,'index'])->name('faqs.list');
    Route::get('faqs/create',[FaqController::class,'create'])->name('faq.add');
    Route::post('faqs/save',[FaqController::class,'save'])->name('faqs.save');
    Route::post('allFaqslist',[FaqController::class,'allFaqslist'])->name('allFaqsformlist');
    Route::get('faq/{id}/edit',[FaqController::class,'editFaq'])->name('faq.edit');
    Route::get('faq/{id}/delete',[FaqController::class,'deleteFaq'])->name('faq.delete');


     //post route
     Route::get('postslist/{id?}',[PostController::class,'index'])->name('posts.list');
     Route::get('posts/create',[PostController::class,'create'])->name('posts.add');
     Route::post('posts/save',[PostController::class,'save'])->name('posts.save');
     Route::post('allpostlist',[PostController::class,'allpostlist'])->name('allpostlist');
     Route::get('changepoststatus/{id}',[PostController::class,'changepoststatus'])->name('posts.changepoststatus');
     Route::get('posts/{id}/delete',[PostController::class,'deletepost'])->name('posts.delete');
     Route::get('posts/{id}/edit',[PostController::class,'editpost'])->name('posts.edit');
     Route::post('posts/uploadfile',[PostController::class,'uploadfile'])->name('posts.uploadfile');
     Route::post('posts/removefile',[PostController::class,'removefile'])->name('posts.removefile');

     //login log route
     Route::get('loginlog',[UserController::class,'loginlog'])->name('loginlog.list');
     Route::post('allloginloglist',[UserController::class,'allloginloglist'])->name('allloginloglist');

     //Payment
    Route::get('payments',[PaymentController::class,'index'])->name('payments.list');
    Route::post('allpaymentslist',[PaymentController::class,'allpaymentslist'])->name('allpaymentslist');
    Route::get('payment/{id}/view',[PaymentController::class,'vieworder'])->name('posts.view');
    Route::post('allpaymentorderslist',[PaymentController::class,'allpaymentorderslist'])->name('allpaymentorderslist');

    Route::post('paymentsuccess',[PaymentController::class,'paymentsuccess'])->name('paymentsuccess');
    
});

Route::group(['middleware'=>['auth']],function (){

    //profile route
    Route::get('profile',[ProfileController::class,'profile'])->name('profile');
    Route::get('profile/{id}/edit',[ProfileController::class,'edit'])->name('profile.edit');
    Route::post('profile/update',[ProfileController::class,'update'])->name('profile.update');
});




