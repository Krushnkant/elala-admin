<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Helpers;
use App\Http\Resources\UserResource;

class AuthController extends BaseController
{
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'register_by' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }else{
            $data = $request->all();
            // dd($Datata['password']);
            if($data['register_by'] == 2 || $data['register_by'] == 1){
                $validator = Validator::make($request->all(), [
                    'register_by' => 'required',
                ]);
                if($validator->fails()){
                    return $this->sendError($validator->errors(), "Validation Errors", []);
                }else{
                    $user = User::where('email', $data['email'])->where('is_verify', 1)->first();
                    // dd($user->decrypted_password);
                    if($user != null){
                        $token = $user->createToken('P00j@13579WebV#d@n%p')->accessToken;
                        $user['token'] = $token;
                        return $this->sendResponseWithData($user, "User successfully login");
                        
                    }else{
                        $user = new User();
                        $user->email = $request->email;
                        $user->register_by = $request->register_by;
                        $user->role = 3;
                        $user->is_verify = 1;
                        $user->save();

                        $token = $user->createToken('P00j@13579WebV#d@n%p')->accessToken;
                        $user['token'] = $token;
                        return $this->sendResponseWithData($user, "User successfully login");
                    }
                }
            }else if($data['register_by'] == 3){
                $user = User::where('email', $data['email'])->first();
                // dd($user->decrypted_password);
                
                    if($user != null){
                        if($user->is_verify == 1){
                            if (isset($user) && $data['password'] == $user->decrypted_password) {
                                $data1 = array();
                                $data1['email'] = $data['email'];
                                $data1['password'] = $data['password'];
                                if (auth()->attempt($data1)) {
                                    $token = auth()->user()->createToken('P00j@13579WebV#d@n%')->accessToken;
                                    $user['token'] = $token;
                                    // dump("user");
                                    // dd($user);
                                    return $this->sendResponseWithData($user, "User successfully login");
                                } else {
                                    return $this->sendError("User credentials invalid", "Unautherized user", []);
                                }
                            }else{
                                return $this->sendError("User password invalid", "Invalid Password", []);
                            }
                        }else{
                            $id =  encrypt($user->id);
                            $data2 = [
                                'message1' => url('verify/'.$id)
                            ]; 
                            $templateName = 'email.mailVerify';
                            $subject = 'Verify User Link';
                            Helpers::MailSending($templateName, $data2, $request->email, $subject);
                            return $this->sendResponseWithData($user, "User Registered Successfully");
                        }
                    }else{

                        $user = new User();
                        $user->email = $request->email;
                        $user->register_by = $request->register_by;
                        $user->role = 3;
                        $user->password = Hash::make($request->password);
                        $user->decrypted_password = $request->password;
                        $user->save();
                        $id =  encrypt($user->id);
                        $data2 = [
                            'message1' => url('verify/'.$id)
                        ]; 
                        $templateName = 'email.mailVerify';
                        $subject = 'Verify User Link';
                        Helpers::MailSending($templateName, $data2, $request->email, $subject);

                        // $token = auth()->user()->createToken('P00j@13579WebV#d@n%')->accessToken;
                        // $user['token'] = $token;
                        return $this->sendResponseWithData($user, "User Registered Successfully");
                    }
                
            }else{
                $user = User::where('mobile_no', $data['mobile_no'])->first();
                
                
                if($user != null){
                    if($user->is_verify == 1){
                        if (isset($user) && $data['password'] == $user->decrypted_password) {
                                $data1 = array();
                                $data1['mobile_no'] = $data['mobile_no'];
                                $data1['password'] = $data['password'];
                            if (auth()->attempt($data1)) {
                                $token = auth()->user()->createToken('P00j@13579WebV#d@n%')->accessToken;
                                $user['token'] = $token;
                                // dump("user");
                                // dd($user);
                                return $this->sendResponseWithData($user, "User successfully login");
                            } else {
                                return $this->sendError("User credentials invalid", "Unautherized user", []);
                            }
                        }else{
                            return $this->sendError("User password invalid", "Invalid Password", []);
                        }
                    }else{
                        $data['otp'] =  mt_rand(100000,999999);
                        $user = User::find($user->id);
                        $user->otp = $data['otp'];
                        $user->otp_created_at = Carbon::now();
                        $user->save();

                        send_sms($request->mobile_no, $data['otp']);
                        return $this->sendResponseWithData($user, "User Registered Successfully");
                    }
                }else{

                    $data['otp'] =  mt_rand(100000,999999);
                    $user = new User();
                    $user->mobile_no = $request->mobile_no;
                    $user->otp = $data['otp'];
                    $user->otp_created_at = Carbon::now();
                    $user->register_by = $request->register_by;
                    $user->role = 3;
                    $user->password = Hash::make($request->password);
                    $user->decrypted_password = $request->password;
                    $user->save();

                    send_sms($request->mobile_no, $data['otp']);
                    return $this->sendResponseWithData($user, "User Registered Successfully");

                }
            }
        }
    }

    public function verify_otp(Request $request){
        $validator = Validator::make($request->all(), [
            'mobile_no' => 'required',
            'otp' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        $user = User::where('mobile_no',$request->mobile_no)->where('otp',$request->otp)->where('estatus',1)->first();

        if ($user && isset($user['otp_created_at'])){
            
            $user->otp = null;
            $user->otp_created_at = null;
            $user->is_verify = 1;
            $user->save();
            $user['token'] =  $user->createToken('MyApp')-> accessToken;
            $data =  new UserResource($user);
            $final_data = array();
            array_push($final_data,$data);
            return $this->sendResponseWithData($final_data,'OTP verified successfully.');
        }
        else{
            return $this->sendError('OTP verification Failed.', "verification Failed", []);
        }
    }

    public function forgetpassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(),'status'=>'failed']);
        }

        $user = User::where('email',$request->email)->where('role',3)->where('estatus',1)->where('is_verify',1)->first();
        if (!$user){
            return $this->sendError("Email Not Exist", "Not Found Error", []);
        }
        $string = str_random(15);
        $user = User::where('email',$request->email)->first();
        $user->forget_token = $string;
        $user->save();

        $data2 = [
            'message1' => 'https://elala.matoresell.com/resetpassword/'.$string
        ]; 
        $templateName = 'email.mailDataforgetpassword';
        $subject = 'Forget Password';
        Helpers::MailSending($templateName, $data2, $request->email, $subject);

        return $this->sendResponseWithData($user,'send mail verified link successfully.'); 
        
    }

    public function resetpassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required',
            'forget_token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(),'status'=>'failed']);
        }

        $user = User::where('forget_token',$request->forget_token)->where('role',3)->where('estatus',1)->first();
        if ($user){
           
            //$user = User::where('id',$request->user_id)->first();
            $user->forget_token = "";
            $user->password = Hash::make($request->password);
            $user->decrypted_password = $request->password;
            $user->save();

            return $this->sendResponseWithData($user,'password reset successfully.'); 
        }    
        return $this->sendError("User Not Exist", "Not Found Error", []);
    }

    

}
