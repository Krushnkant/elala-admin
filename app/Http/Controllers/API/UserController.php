<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ {User,Settings,Bank};
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

    

}
