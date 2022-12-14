<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    use HasFactory;

    public function experience(){
        return $this->hasOne(Experience::class,'id','experience_id');
    }
}
