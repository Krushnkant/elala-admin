<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SingleOrdPayment extends Model
{
    use HasFactory;

    public function order(){
        return $this->hasOne(order::class,'id','order_id');
    }
}
