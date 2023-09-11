<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Location\Facades\Location;
use App\Models\User;
use App\Models\UserLogin;

class AuthController extends Controller
{
    private $page = "Elala Admin";

    public function index()
    {
        return view('admin.auth.login')->with('page',$this->page);
    }

    public function invalid_page()
    {
        return view('admin.403_page');
    }

    public function postLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(),'status'=>'failed']);
        }
        $user = User::where('email',$request->email)->where('decrypted_password',$request->password)->whereNotIn('role',['3'])->first();
        if ($user) {
        if($user->estatus == 1){    
            $credentials = $request->only('email', 'password');
            if (Auth::attempt($credentials)) {
                $position = Location::get($request->ip());
                $user->last_login_date = new \DateTime(null, new \DateTimeZone('Asia/Kolkata'));
                $user->save();

                $userlogin = New UserLogin();
                $userlogin->user_id =  $user->id;
                $userlogin->ip_address =  $request->ip();
                $userlogin->country =  isset($position->countryName)?$position->countryName:"";
                $userlogin->state =  isset($position->regionName)?$position->regionName:"";
                $userlogin->city =  isset($position->cityName)?$position->cityName:"";
                $userlogin->browser =  isset($browser)?$browser:"";
                $userlogin->created_at = new \DateTime(null, new \DateTimeZone('Asia/Kolkata'));
                $userlogin->save();
    //            dd(Auth::user()->toArray());
                return response()->json(['status'=>200]);
                /*return redirect()->intended('admin/dashboard')
                    ->withSuccess('You have Successfully loggedin');*/
            }
        }else{
            return response()->json(['status'=>300]);
        }    
        }
        return response()->json(['status'=>400]);
//        return redirect("admin")->withSuccess('Oppes! You have entered invalid credentials');
    }

    /*public function dashboard()
    {
        if(Auth::check()){
            return view('admin.dashboard');
        }

        return redirect("admin")->withSuccess('Opps! You do not have access');
    }*/

    public function logout() {
        Session::flush();
        Auth::logout();

        return Redirect('admin');
    }

    public function verify_email($text){
        $id= decrypt($text);
        $user = User::where('id',$id)->where('estatus',1)->first();
        if ($user){
            $user->is_verify = 1;
            $user->save();
            return redirect("https://elala.madnessmart.com/login-email");
            //return response()->json(['status'=>200]);
        }
        else{
            return response()->json(['status'=>400]);
        }
    }
    
}
