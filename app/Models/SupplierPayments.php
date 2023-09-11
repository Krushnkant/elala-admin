<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierPayments extends Model
{
    use HasFactory;
    public function user(){
        return $this->hasOne(order::class,'id','host_id');
    }
}
