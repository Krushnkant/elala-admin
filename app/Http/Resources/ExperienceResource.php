<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\{ExperienceProvideItem,ExperienceBrindItem,ExperienceMedia,ExperienceDiscountRate,ExperienceScheduleTime};


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

        return [
            'id' => $this->id,
            'type' => $this->type,
            'location' => $this->location,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'category_id' => $this->category_id,
            'title' => $this->category_id,
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
            'estatus' => $this->estatus
        ];
    }
}
