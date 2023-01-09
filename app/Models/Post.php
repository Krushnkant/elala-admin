<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function user(){
        //return $this->hasOne(User::class,'id','id');
        $instance = $this->hasOne(User::class,'id','user_id');
        $instance->getQuery()->select('full_name','profile_pic','created_at');
        return $instance;
    }

    public function posttags(){
        return $this->hasMany(PostTag::class,'post_id','id');
    }

    public function postmedia(){
        return $this->hasMany(PostMedia::class,'post_id','id');
    }

    public function hosttag(){
        return $this->hasOne(User::class,'id','host_tag');
    }
}
