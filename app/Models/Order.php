<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public function experience(){
        return $this->hasOne(Experience::class,'id','experience_id');
    }

    public function orderslot(){
        return $this->hasOne(ExperienceScheduleTime::class,'id','schedule_time_id');
    }
}
