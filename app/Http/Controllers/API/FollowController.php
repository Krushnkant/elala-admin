<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserFollower;

class FollowController extends BaseController
{
   
    public function follow(Request $request) {
        $userToFollow = User::findOrFail(request('user_id'));
        $data = auth()->user()->follow($userToFollow);
        return $this->sendResponseWithData($data,"User Follow Successfully");
    }

    public function unfollow(Request $request) {
        $userToUnfollow = User::findOrFail(request('user_id'));
        $data = auth()->user()->unfollow($userToUnfollow);
        return $this->sendResponseWithData($data,"User Unfollow Successfully");
    }

    public function follow_request(Request $request) {
        $userToFollow = User::findOrFail(request('user_id'));
        //$check = auth()->user()->followers()->where('users.id', $userToFollow->id)->exists();
        if($request->follow_status == 4){
            $check = UserFollower::where('user_id',auth()->id())->where('following_id',$userToFollow->id)->first();
            if(!$check){
                UserFollower::where('following_id',auth()->id())->where('user_id',$userToFollow->id)->delete();
            }else{
                $UserFollstatus = UserFollower::where('following_id',auth()->id())->where('user_id',$userToFollow->id)->first();
                if($UserFollstatus){
                    $UserFollstatus->estatus = 2;
                    $UserFollstatus->save();
                }
            }
        }else{
            if($userToFollow){
                $UserFollower =  UserFollower::where('following_id',auth()->id())->where('user_id',$userToFollow->id)->first();
                if($UserFollower){
                    $UserFollower->estatus = 1;
                    $check = UserFollower::where('user_id',auth()->id())->where('following_id',$userToFollow->id)->first();
                    if(!$check){
                        $UserFollowers = New UserFollower();
                        $UserFollowers->user_id = auth()->id();
                        $UserFollowers->following_id = $userToFollow->id;
                        $UserFollowers->estatus = 2;
                        $UserFollowers->save();
                    }else{
                        $check->follow_each_other = 1;
                        $check->save();
                        $UserFollower->follow_each_other = 1;
                    }
                    $UserFollower->save();
                } 
            }
        }

        $checkstatus = UserFollower::where('following_id',auth()->id())->where('user_id',$userToFollow->id)->first();
        if($checkstatus){
             $status = $checkstatus->estatus;
         }else{
             $status = "";
         }
        return $this->sendResponseWithData($status,"User Status Chnage Successfully");
    }

    public function getFollower(Request $request){
       
        $userfollowers = UserFollower::with('user');
        if(isset($request->type) && $request->type == "follower"){
           $userfollowers = $userfollowers->where('following_id',auth()->id())->where('estatus',1);
        }elseif(isset($request->type) && $request->type == "following"){
           $userfollowers = $userfollowers->where('user_id',auth()->id())->where('estatus',1);
        }else{
           $userfollowers = $userfollowers->where('following_id',auth()->id())->where('estatus',0); 
        }
        $userfollowers = $userfollowers->get();
        $userfollowers_arr = array();
        if(isset($request->type) && $request->type == "following"){
            foreach ($userfollowers as $userfollower){
                $temp = array();
                $temp['id'] = $userfollower->id;
                $temp['user_id'] = $userfollower->following_id;
                $temp['full_name'] = $userfollower->follower->full_name;
                $temp['profile_pic'] = $userfollower->follower->profile_pic;
                $temp['is_follow'] = is_follower(auth()->id(),$userfollower->following_id);
                array_push($userfollowers_arr,$temp);
            }
        }else{
            foreach ($userfollowers as $userfollower){
                $temp = array();
                $temp['id'] = $userfollower->id;
                $temp['user_id'] = $userfollower->user_id;
                $temp['full_name'] = $userfollower->user->full_name;
                $temp['profile_pic'] = $userfollower->user->profile_pic;
                $temp['is_follow'] = is_follower(auth()->id(),$userfollower->user_id);
                array_push($userfollowers_arr,$temp);
            }
        }
        return $this->sendResponseWithData($userfollowers_arr,"Follow Retrieved Successfully.");
    }

    public function getRandomUsers(Request $request){
        
        $users = User::where('estatus',1)->where('is_completed',1)->inRandomOrder()->limit(10)->get();
        $users_arr = array();
        foreach ($users as $user){
            $temp = array();
            $temp['id'] = $user->id;
            $temp['full_name'] = $user->full_name;
            $temp['profile_pic'] = $user->profile_pic;
            $temp['is_follow'] = is_follower(auth()->id(),$user->id);
            array_push($users_arr,$temp);
        }
        return $this->sendResponseWithData($users_arr,"Random Users Retrieved Successfully.");
    }

    
}
