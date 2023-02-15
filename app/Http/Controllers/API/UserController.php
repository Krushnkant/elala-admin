<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ {User,Settings,Bank,UserFollower,Post,Review,Country,State,City,Experience};
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;


class UserController extends BaseController
{
    public function register_user(Request $request){
        $messages = [
            'profile_pic.image' =>'Please provide a Valid Extension Image(e.g: .jpg .png)',
            'profile_pic.mimes' =>'Please provide a Valid Extension Image(e.g: .jpg .png)',
            'first_name.required' =>'Please provide a First Name',
            'last_name.required' =>'Please provide a Last Name',
            'mobile_no.required' =>'Please provide a Mobile No.',
            'dob.required' =>'Please provide a Date of Birth.',
            'email.required' =>'Please provide a e-mail address.',
            'password.required' =>'Please provide a password.',
            'gender.required' =>'Please provide a gender.',
        ];

        $validator = Validator::make($request->all(), [
            'profile_pic' => 'image|mimes:jpeg,png,jpg',
            'first_name' => 'required',
            'last_name' => 'required',
            //'mobile_no' => 'required|numeric|digits:10',
            'dob' => 'required',
            //'email' => 'required',
            'password' => 'required',
            'gender' => 'required',
            'email' => ['required', 'string', 'email', 'max:191',Rule::unique('users')->where(function ($query) use ($request) {
                return $query->where('role', 3)->where('estatus','!=',3);
            })],
            'mobile_no' => ['required', 'numeric', 'digits:10',Rule::unique('users')->where(function ($query) use ($request) {
                return $query->where('role', 3)->where('estatus','!=',3);
            })],
        ], $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->full_name = $request->first_name." ".$request->last_name;
        $user->mobile_no = $request->mobile_no;
        $user->gender = $request->gender;
        $user->dob = $request->dob;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->decrypted_password = $request->password;
        $user->role = 3;
        $user->created_at = new \DateTime(null, new \DateTimeZone('Asia/Kolkata'));

        if ($request->hasFile('profile_pic')) {
            $image = $request->file('profile_pic');
            $image_name = 'profilePic_' . rand(111111, 999999) . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('images/profile_pic');
            $image->move($destinationPath, $image_name);
            $user->profile_pic = $image_name;
        }

        $user->save();
        return $this->sendResponseSuccess("User Registered Successfully");
    }
    

    public function editProfile(Request $request){
        $messages = [
            'mobile_no.required' =>'Please provide a Mobile No.',
            'dob.required' =>'Please provide a Date of Birth.',
            'email.required' =>'Please provide a e-mail address.',
            'gender.required' =>'Please provide a gender.',
        ];

        $validator = Validator::make($request->all(), [
            'dob' => 'required',
            'gender' => 'required',
            'email' => 'required',
            'mobile_no' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }
        
        $user = User::find(Auth::user()->id);
        $user->full_name = $request->name;
        $user->mobile_no = $request->mobile_no;
        $user->gender = $request->gender;
        $user->dob = $request->dob;
        $user->email = $request->email;
        $user->bio = isset($request->bio)?$request->bio:"";
        $user->is_completed = 1;
        $user->is_private = isset($request->is_private)?$request->is_private:0;

        if ($request->hasFile('profile_pic')) {
            $image = $request->file('profile_pic');
            $image_name = 'profilePic_' . rand(111111, 999999) . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('images/profile_pic');
            $image->move($destinationPath, $image_name);
            $user->profile_pic = $image_name;
        }

        $user->save();
        return $this->sendResponseWithData($user,"User Profile Updated Successfully");
    }

    public function settings(){
        
        $Setting = Settings::first();
        $data['company_name'] = $Setting->company_name;
        $data['company_logo'] = isset($Setting->company_logo)?url('images/company/'.$Setting->company_logo):"";
        $data['company_favicon'] = isset($Setting->company_favicon)?url('images/company/'.$Setting->company_favicon):"";
          
        return $this->sendResponseWithData($data,"Setting Data Retrieved Successfully.");
    }

    public function addEditBank(Request $request){
        $messages = [
            'bank_name.required' =>'Please provide a Bank Name.',
            'account_no.required' =>'Please provide a Account Number.',
            'account_holder_name.required' =>'Please provide a Account Holder Name.',
            'ifsc_code.required' =>'Please provide a IFSC Code.',
        ];

        $validator = Validator::make($request->all(), [
            'bank_name' => 'required',
            'account_no' => 'required',
            'account_holder_name' => 'required',
            'ifsc_code' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }
        if($request->bank_id > 0){
            $bank = Bank::find($request->bank_id);
        }else{
            $bank = New Bank();
            $bank->user_id = Auth::user()->id;
        }
        
        
        $bank->bank_name = $request->bank_name;
        $bank->account_no = $request->account_no;
        $bank->account_holder_name = $request->account_holder_name;
        $bank->ifsc_code = $request->ifsc_code;
        $bank->save();

        return $this->sendResponseWithData($bank,"Bank Updated Successfully");
    }

    public function getBank(){
        $user_id = Auth::user()->id;
        $bank = Bank::where('user_id',$user_id)->first();
        return $this->sendResponseWithData($bank,"Bank Deatails Retrieved Successfully.");
    }

    public function getUser(){
        $user_id = Auth::user()->id;
        $users = User::where('id','<>',$user_id)->where('role',3)->where('is_completed',1)->get(['id','full_name','profile_pic']);
        return $this->sendResponseWithData($users,"Users Retrieved Successfully.");
    }

    public function viewProfile(Request $request){
        $messages = [
            'profile_id.required' =>'Please provide a profile id',
        ];
        $validator = Validator::make($request->all(), [
            'profile_id' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }
       
        //$user_id = Auth::user()->id;
        $limit = isset($request->limit)?$request->limit:20;
        $profile_id = $request->profile_id;

        $user = User::where('id',$profile_id)->first();


        $myreviews = Review::with('user')->where('customer_id',$profile_id)->where('estatus',1)->paginate($limit);
        $myreviews_arr = array();
        foreach ($myreviews as $review){
            $temp = array();
            $temp['id'] = $review->id;
            $temp['experience_id'] = isset($review->experience)?$review->experience->id:0;
            $temp['experience_title'] = isset($review->experience)?$review->experience->title:"";
            $temp['experience_image'] = isset($review->experience)?$review->experience->image:"";
            $temp['description'] = $review->description;
            $temp['rating'] = $review->rating;
            $temp['full_name'] = $review->user->full_name;
            $temp['profile_image'] = isset($review->user->profile_pic)?$review->user->profile_pic:"";
            $temp['created_at'] = $review->created_at;
            array_push($myreviews_arr,$temp);
        }

        $experiencereviews = Review::with('user')->WhereHas('experience',function ($mainQuery) use($profile_id) {
            $mainQuery->where('user_id',$profile_id);
        })->where('estatus',1)->paginate($limit);
        $experiencereviews_arr = array();
        foreach ($experiencereviews as $review){    
            $temp = array();
            $temp['id'] = $review->id;
            $temp['experience_id'] = isset($review->experience)?$review->experience->id:0;
            $temp['experience_title'] = isset($review->experience)?$review->experience->title:"";
            $temp['experience_image'] = isset($review->experience)?$review->experience->image:"";
            $temp['description'] = $review->description;
            $temp['rating'] = $review->rating;
            $temp['full_name'] = $review->user->full_name;
            $temp['profile_image'] = isset($review->user->profile_pic)?$review->user->profile_pic:"";
            $temp['created_at'] = $review->created_at;
            array_push($experiencereviews_arr,$temp);
        }

        $experiences = Experience::with(['media' => function($q) {
            $q->where('type', '=', 'img'); 
        }])->where('user_id',$profile_id)->where('estatus',1)->get();

        $experiences_arr = array();
        foreach ($experiences as $experience){
            $temp = array();
            $temp['id'] = $experience->id;
            $temp['slug'] = $experience->slug;
            $temp['title'] = $experience->title;
            $temp['location'] = $experience->location;
            $temp['individual_rate'] = $experience->individual_rate;
            $temp['duration'] = $experience->duration;
            $temp['image'] = isset($experience->media)?$experience->media:[];
            $temp['rating'] = $experience->rating;
            $temp['rating_member'] = $experience->review_total_user;
            array_push($experiences_arr,$temp);
        }

        $userdata['full_name'] = $user->full_name;
        $userdata['email'] = $user->email;
        $userdata['mobile_no'] = $user->mobile_no;
        $userdata['profile_pic'] = $user->profile_pic;
        $userdata['gender'] = $user->gender;
        $userdata['dob'] = $user->dob;
        $userdata['bio'] = $user->bio;
        $userdata['is_private'] = $user->is_private;
        $userdata['created_at'] = $user->created_at;
        $userdata['post'] =  Post::where('user_id',$profile_id)->where('estatus',1)->get()->count();
        $userdata['following'] =  UserFollower::where('user_id',$profile_id)->where('estatus',1)->get()->count();
        $userdata['follower'] =  UserFollower::where('following_id',$profile_id)->where('estatus',1)->get()->count();
        if(isset(Auth::user()->id)) {
            $userdata['is_follow'] = is_follower_random(Auth::user()->id,$profile_id);
        }else{
            $userdata['is_follow'] = "";
        }
        $userdata['rating'] = hostRating($profile_id);
        $userdata['rating_member'] = hostReviewMember($profile_id);
        $userdata['my_reviews'] = $myreviews_arr;
        $userdata['experience_reviews'] = $experiencereviews_arr;
        $userdata['experiences'] = $experiences_arr;
        return $this->sendResponseWithData($userdata,"profile Retrieved Successfully.");
        
    }

    public function getMyReview(Request $request){
        $messages = [
            'profile_id.required' =>'Please provide a profile id',
        ];
        $validator = Validator::make($request->all(), [
            'profile_id' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }
        $limit = isset($request->limit)?$request->limit:20;
        $profile_id = $request->profile_id;
        $myreviews = Review::with('user')->where('customer_id',$profile_id)->where('estatus',1)->paginate($limit);
        $myreviews_arr = array();
        foreach ($myreviews as $review){
            $temp = array();
            $temp['id'] = $review->id;
            $temp['experience_id'] = isset($review->experience)?$review->experience->id:0;
            $temp['experience_title'] = isset($review->experience)?$review->experience->title:"";
            $temp['experience_image'] = isset($review->experience)?$review->experience->image:"";
            $temp['description'] = $review->description;
            $temp['rating'] = $review->rating;
            $temp['full_name'] = $review->user->full_name;
            $temp['profile_image'] = isset($review->user->profile_pic)?$review->user->profile_pic:"";
            $temp['created_at'] = $review->created_at;
            array_push($myreviews_arr,$temp);
        }
        $data['myreviews'] = $myreviews_arr;
        return $this->sendResponseWithData($data,"My Review Retrieved Successfully.");
    }

    public function getMyExperienceReview(Request $request){
        $messages = [
            'profile_id.required' =>'Please provide a profile id',
        ];
        $validator = Validator::make($request->all(), [
            'profile_id' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }
        $limit = isset($request->limit)?$request->limit:20;
        $profile_id = $request->profile_id;
        $experiencereviews = Review::with('user')->WhereHas('experience',function ($mainQuery) use($profile_id) {
            $mainQuery->where('user_id',$profile_id);
        })->where('estatus',1)->paginate($limit);
        $experiencereviews_arr = array();
        foreach ($experiencereviews as $review){    
            $temp = array();
            $temp['id'] = $review->id;
            $temp['experience_id'] = isset($review->experience)?$review->experience->id:0;
            $temp['experience_title'] = isset($review->experience)?$review->experience->title:"";
            $temp['experience_image'] = isset($review->experience)?$review->experience->image:"";
            $temp['description'] = $review->description;
            $temp['rating'] = $review->rating;
            $temp['user_id'] = $review->user->id;
            $temp['full_name'] = $review->user->full_name;
            $temp['profile_image'] = isset($review->user->profile_pic)?$review->user->profile_pic:"";
            $temp['created_at'] = $review->created_at;
            array_push($experiencereviews_arr,$temp);
        }
        $data['experiencereviews'] = $experiencereviews_arr;
        return $this->sendResponseWithData($data,"My Review Experience Retrieved Successfully.");
    }



    public function getCountry(){
        $countries = Country::get(['id','name','phonecode']);
        return $this->sendResponseWithData($countries,"Country Retrieved Successfully.");
    }

    public function getState(Request $request){
        $countries = explode(",",$request->country_id);
        $states = State::whereIn('country_id',$countries)->get(['id','name']);
        return $this->sendResponseWithData($states,"Country Retrieved Successfully.");
    }

    public function getCity(Request $request){
        $states = explode(",",$request->state_id);
        $cities = City::whereIn('state_id',$states)->get(['id','name']);
        return $this->sendResponseWithData($cities,"Country Retrieved Successfully.");
    }

}
