<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Experience extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function user(){
        return $this->hasOne(User::class,'id','user_id');
    }
    public function category(){
        return $this->hasOne(Category::class,'id','category_id');
    }

    public function media(){
        return $this->hasMany(ExperienceMedia::class,'experience_id','id');
    }
    
    public function scheduletime(){
        return $this->hasMany(ExperienceScheduleTime::class,'experience_id','id');
    }

    public function discountrate(){
        return $this->hasMany(ExperienceDiscountRate::class,'experience_id','id');
    }

    public function categoryattribute(){
        return $this->hasMany(ExperienceCategoryAttribute::class,'experience_id','id');
    }

    public function experiencelanguage(){
        return $this->hasOne(ExperienceLanguage::class,'experience_id','id');
    }

    public function experiencecategory(){
        return $this->hasMany(ExperienceCategor::class,'experience_id','id');
    }

    

    // public function getCountryAttribute(){
    //     $country = Country::where('id',$this->attributes['country'])->pluck('name')->first();
    //     return $country;
    // }

    // public function getStateAttribute(){
    //     $State = State::where('id',$this->attributes['state'])->pluck('name')->first();
    //     return $State;
    // }

    // public function getCityAttribute(){
    //     $City = City::where('id',$this->attributes['city'])->pluck('name')->first();
    //     return $City;
    // }
  
}
