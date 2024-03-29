<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFollower extends Model
{
    
    use HasFactory;
    protected $fillable = ['user_id','following_id'];

    public function user(){
        return $this->hasOne(User::class,'id','user_id');
    }

    public function follower(){
        return $this->hasOne(User::class,'id','following_id');
    }
}
