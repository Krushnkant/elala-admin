<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;

    protected $table = 'users';

    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'parent_user_id','email','password','username','first_name','last_name','full_name','mobile_no','gst_no','pickup_location','profile_pic','gender',
        'role','estatus','dob','otp','otp_created_at','referral_id','decrypted_password','category_ids'
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getProfilePicAttribute(){
        if($this->attributes['profile_pic'] != null){
            return asset('images/profile_pic/'.$this->attributes['profile_pic']);
        }else{
            return null;
        }
    }

    public function designation(){
        return $this->hasOne(Designation::class,'id','designation_id');
    }

    public function cover_photos(){
        return $this->hasMany(UserCoverPhotos::class,'user_id','id');
    }

    public function getCountryAttribute(){
        $country = Country::where('id',$this->attributes['country'])->pluck('name')->first();
        return $country;
    }

    public function getStateAttribute(){
        $State = State::where('id',$this->attributes['state'])->pluck('name')->first();
        return $State;
    }

    public function getCityAttribute(){
        $City = City::where('id',$this->attributes['city'])->pluck('name')->first();
        return $City;
    }

    public function follow(User $user) {
        
        if(!$this->isFollowing($user)) {
             
            $UserFollower = New UserFollower();
            $UserFollower->user_id = auth()->id();
            $UserFollower->following_id = $user->id;
            $UserFollower->estatus = ($user->is_private == 1)?0:1;
            $UserFollower->save();
          
            if(!$this->isFollowers($user)) {
                if($user->is_private == 0){
                    $UserFollowerBack = New UserFollower();
                    $UserFollowerBack->user_id = $user->id;
                    $UserFollowerBack->following_id = auth()->id();
                    $UserFollowerBack->estatus = 2;
                    $UserFollowerBack->save();
                }
            }
            if($UserFollower){
                return $status = $UserFollower->estatus;
            }else{
                return $status = "";
            }
        }else{

            $UserFollower =UserFollower::where('user_id',auth()->id())->where('following_id',$user->id)->first();
            $UserFollower->estatus = ($user->is_private == 1)?0:1;
            $UserFollower->save();

            if($UserFollower){
                return $status = $UserFollower->estatus;
            }else{
                return $status = "";
            }

        }
    }
    
    public function unfollow(User $user) {
        $check =UserFollower::where('following_id',auth()->id())->where('user_id',$user->id)->where('estatus',1)->first();
        if(!$check){
            UserFollower::where('user_id',auth()->id())->where('following_id',$user->id)->delete();
        }else{
            $UserFollstatus = UserFollower::where('user_id',auth()->id())->where('following_id',$user->id)->first();
            if($UserFollstatus){
                $UserFollstatus->estatus = 2;
                $UserFollstatus->save();
            }
        }
        $checkstatus = UserFollower::where('user_id',auth()->id())->where('following_id',$user->id)->first();
        if($checkstatus){
            return $status = $checkstatus->estatus;
         }else{
            return $status = "";
         }
    }
    
    public function isFollowing(User $user) {
        return $this->following()->where('users.id', $user->id)->exists();
    }

    public function isFollowers(User $user) {
        return $this->followers()->where('users.id', $user->id)->exists();
    }
    
    public function following() {
        return $this->hasManyThrough(User::class, UserFollower::class, 'user_id', 'id', 'id', 'following_id');
    }
    
    public function followers() {
        return $this->hasManyThrough(User::class, UserFollower::class, 'following_id', 'id', 'id', 'user_id');
    }
}
