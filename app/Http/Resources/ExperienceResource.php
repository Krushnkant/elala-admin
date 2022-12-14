<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\{ExperienceProvideItem,ExperienceBrindItem,ExperienceMedia,ExperienceDiscountRate,ExperienceScheduleTime,ExperienceLanguage,ExperienceCategoryAttribute,CategoryAttribute};


class ExperienceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //return parent::toArray($request);
        $ProvideItem = ExperienceProvideItem::where('experience_id',$this->id)->get(['id','title']);
        $BrindItem = ExperienceBrindItem::where('experience_id',$this->id)->get(['id','title']);
        $Images = ExperienceMedia::where('experience_id',$this->id)->where('type','img')->get(['id','thumb']);
        $Videos = ExperienceMedia::where('experience_id',$this->id)->where('type','video')->get(['id','thumb']);
        $DiscountRate = ExperienceDiscountRate::where('experience_id',$this->id)->get(['id','from_member','to_member','discount']);
        $ScheduleTime = ExperienceScheduleTime::where('experience_id',$this->id)->get(['id','day','time']);
        $ExperienceLanguage = ExperienceLanguage::where('experience_id',$this->id)->get(['id','experience_id','language_id']);
        

        $attributes_arr = array();
        if($this->category_id != "" && $this->category_id != 0){
            $categoryAttribute= CategoryAttribute::with('attr_optioin')->where('category_id',$this->category_id)->get();
            foreach ($categoryAttribute as $attribute){
                $ExperienceCategoryAttribute = ExperienceCategoryAttribute::where('experience_id',$this->id)->where('cat_attr_id',$attribute->id)->first(['value']);
                $temp = array();
                $temp['id'] = $attribute->id;
                $temp['field_id'] = $attribute->field_id;
                $temp['title'] = $attribute->title;
                $temp['value'] = isset($ExperienceCategoryAttribute->value)?$ExperienceCategoryAttribute->value:"";
                $temp['option'] = $attribute->attr_optioin;
                array_push($attributes_arr,$temp);
            }
        }

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'type' => $this->type,
            'location' => $this->location,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'languages' => $ExperienceLanguage,
            'category_id' => $this->category_id,
            'title' => $this->title,
            'description' => $this->description,
            'images' => $Images,
            'videos' => $Videos,
            'duration' => $this->duration,
            'age_limit' => explode(',',$this->age_limit),
            'provide_items' => $ProvideItem,
            'is_bring_item' => $this->is_bring_item,
            'brind_items' => $BrindItem,
            'is_meet_address' => $this->is_meet_address,
            'meet_address' => $this->meet_address,
            'meet_address_flat_no' => $this->meet_address_flat_no,
            'meet_city' => $this->meet_city,
            'meet_state' => $this->meet_state,
            'meet_country' => $this->meet_country,
            'pine_code' => $this->pine_code,
            'meet_latitude' => $this->meet_latitude,
            'meet_longitude' => $this->meet_longitude,
            'max_member_public_group_size' => $this->max_member_public_group_size,
            'max_member_private_group_size' => $this->max_member_private_group_size,
            'individual_rate' => $this->individual_rate,
            'min_private_group_rate' => $this->min_private_group_rate,
            'discount_rate' => $DiscountRate,
            'schedule_time' => $ScheduleTime,
            'cancellation_policy_id' => $this->cancellation_policy_id,
            'rating' => $this->rating,
            'rating_member' => $this->review_total_user,
            'estatus' => $this->estatus,
            'proccess_page' => $this->proccess_page,
            'category_attribute' => $attributes_arr,
        ];
    }
}
