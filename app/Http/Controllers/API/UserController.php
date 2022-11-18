<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ {User,City,State,Country};
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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

    public function city(){
        $cities = City::select('cities.*','states.name as stete_name','countries.name as country_name')->leftJoin('states', function($join) {
            $join->on('states.id', '=', 'cities.state_id');
          })->leftJoin('countries', function($join) {
            $join->on('countries.id', '=', 'states.country_id');
          }) ->get();
        $cities_arr = array();
        foreach ($cities as $city){
            $temp = array();
            $temp['id'] = $city->id;
            $temp['name'] = $city->name.','.$city->stete_name.','.$city->country_name;
       
            array_push($cities_arr,$temp);
        }
        return $this->sendResponseWithData($cities_arr,"City Retrieved Successfully.");
    }

    public function city1(){
        $data = array();
        $cities = City::get();
        $cities_arr = array();
        foreach ($cities as $city){
            $temp = array();
            $temp['id'] = $city->id;
            $temp['name'] = $city->name;
            array_push($cities_arr,$temp);
        }

        $states = State::get();
        $state_arr = array();
        foreach ($states as $state){
            $temp = array();
            $temp['id'] = $state->id;
            $temp['name'] = $state->name;
            array_push($state_arr,$temp);
        }

        $countries = State::get();
        $countries_arr = array();
        foreach ($countries as $country){
            $temp = array();
            $temp['id'] = $country->id;
            $temp['name'] = $country->name;
            array_push($countries_arr,$temp);
        }

        $data = array('city' => $cities_arr,'state' => $state_arr,'country' => $countries_arr);
        return $this->sendResponseWithData($data,"City Retrieved Successfully.");
    }


}
