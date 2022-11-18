<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use HasFactory;
   // use SoftDeletes;

    public function state(){
        return $this->hasOne(State::class,'id','state_id');
    }
}
