<?php

namespace App\Http\Resources;

use App\Models\Category;
use App\Models\Settings;
use App\Models\UserCoverPhotos;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'user_id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'mobile_no' => $this->mobile_no,
            'gender' => $this->gender,
            'dob' => $this->dob,
            'profile_pic' => isset($this->profile_pic) ? $this->profile_pic : asset('images/default_avatar.jpg'),
            'bio' => $this->bio
        ];
    }
}
