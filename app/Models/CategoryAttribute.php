<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryAttribute extends Model
{
    use HasFactory;

    public function attr_optioin(){
        //return $this->hasMany(AttributeOption::class,'attribute_id','id');
        $instance = $this->hasMany(AttributeOption::class,'attribute_id','id');
        $instance->getQuery()->select('id','attribute_id','option_value');
        return $instance;
    }
}
