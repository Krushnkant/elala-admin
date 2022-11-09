<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends BaseController
{
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:3'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }else{
            $data = $request->all();
            // dd($Datata['password']);
            $user = User::where('email', $data['email'])->first();
            // dd($user->decrypted_password);
            if($user != null){
                if (isset($user) && $data['password'] == $user->decrypted_password) {
                    if (auth()->attempt($data)) {
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
                return $this->sendError("User Not found", "Not registerd user", []);
            }
        }
    }
}
