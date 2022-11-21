<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ {User,Experience,ExperienceMedia,ExperienceBrindItem,ExperienceProvideItem,ExperienceScheduleTime,ExperienceDiscountRate,ExperienceCategoryAttribute};
use App\Http\Resources\ExperienceResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ExperienceController extends BaseController
{
    public function addExperienceType(Request $request){
        $messages = [
            'type.required' =>'Please provide a type.',
        ];

        $validator = Validator::make($request->all(), [
            'type' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        if(isset($request->experience_id) && $request->experience_id != 0){
            $experience = Experience::where('id',$request->experience_id)->where('estatus',1)->first();
            if (!$experience){
                return $this->sendError("Experience Not Exist", "Not Found Error", []);
            } 
            $Experience = Experience::find($request->experience_id);
            $Experience->type = $request->type;
            $Experience->save();
        }else{
            $Experience = new Experience();
            $Experience->user_id = Auth::user()->id;
            $Experience->type = $request->type;
            $Experience->proccess_page = 'TypePage';
            $Experience->estatus = 5;
            $Experience->save();
        }
        return $this->sendResponseSuccess("Added Experience Successfully");
    }

    public function addExperienceLocation(Request $request){
        $messages = [
            'location.required' =>'Please provide a location.',
            'latitude.required' =>'Please provide a latitude.',
            'longitude.required' =>'Please provide a longitude.',
            'language.required' =>'Please provide a language.',
        ];

        $validator = Validator::make($request->all(), [
            'location' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'language' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        $experience = Experience::where('id',$request->experience_id)->where('estatus',1)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
    
        $Experience = Experience::find($request->experience_id);
        $Experience->location = $request->location;
        $Experience->latitude = $request->latitude;
        $Experience->longitude = $request->longitude;
        $Experience->proccess_page = 'LocationPage';
        $Experience->save();
        return $this->sendResponseSuccess("Added Experience Location Successfully");
    }

    public function addExperienceCategory(Request $request){
        $messages = [
            'category_id.required' =>'Please provide a category id.',
        ];

        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }
        $experience = Experience::where('id',$request->experience_id)->where('estatus',1)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
    
        $Experience = Experience::find($request->experience_id);
        $Experience->category_id = $request->category_id;
        $Experience->proccess_page = 'CategoryPage';
        $Experience->save();
        return $this->sendResponseSuccess("Added Experience Category Successfully");
    }

    public function addExperienceDetails(Request $request){
        $messages = [
            'title.required' =>'Please provide a title.',
            'description.required' =>'Please provide a title.',
        ];

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        $experience = Experience::where('id',$request->experience_id)->where('estatus',1)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
    
        $Experience = Experience::find($request->experience_id);
        $Experience->title = $request->title;
        $Experience->description = $request->description;
        $Experience->proccess_page = 'DetailsPage';
        $Experience->save();
        return $this->sendResponseSuccess("Added Experience Details Successfully");
    }

    public function addExperienceMedia(Request $request){
        $validator = Validator::make($request->all(), [
            'images.*' => 'image|mimes:jpeg,png,jpg',
            'video' => 'mimes:mp4,ogx,oga,ogv,ogg,webm',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }
        $experience = Experience::where('id',$request->experience_id)->where('estatus',1)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
    
        if($request->hasFile('images')) {
            $experience_images = array();
            foreach ($request->file('images') as $image) {
                $ExperienceMedia = new ExperienceMedia();
                $ExperienceMedia->experience_id = $request->experience_id;
                $image_name = 'experience_images_' . rand(111111, 999999) . time() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('images/experience_images');
                $image->move($destinationPath, $image_name);
               // array_push($experience_images,'images/experience_images/'.$image_name);
                $ExperienceMedia->thumb = 'images/experience_images/'.$image_name;
                $ExperienceMedia->type = 'img';
                $ExperienceMedia->save();
            }  
        }

        if ($request->hasFile('video')){
            $image = $request->file('video');
            $image_name = 'experience_video_' . rand(111111, 999999) . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('images/experience_videos');
            $image->move($destinationPath, $image_name);
            $ExperienceMedia = new ExperienceMedia();
            $ExperienceMedia->experience_id = $request->experience_id;
            $ExperienceMedia->thumb = 'images/experience_videos/'.$image_name;
            $ExperienceMedia->type = 'video';
            $ExperienceMedia->save();
        }

        $Experience = Experience::find($request->experience_id);
        $Experience->proccess_page = 'MediaPage';
        $Experience->save();
        //$ExperienceMedia->proccess_page = 'MediaPage';
        
        return $this->sendResponseSuccess("Added Experience Media Successfully");
    }

    public function addExperienceAgeGroup(Request $request){
        $messages = [
            'age_id.required' =>'Please provide a age id.',
            'duration.required' =>'Please provide a duration.',
        ];

        $validator = Validator::make($request->all(), [
            'age_id' => 'required',
            'duration' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }
        $experience = Experience::where('id',$request->experience_id)->where('estatus',1)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
    
        $Experience = Experience::find($request->experience_id);
        $Experience->duration = $request->duration;
        $Experience->age_limit = $request->age_id;
        $Experience->proccess_page = 'AgePage';
        $Experience->save();
        return $this->sendResponseSuccess("Added Experience Age Successfully");
    }


    public function addExperienceProvideItem(Request $request){
        $validator = Validator::make($request->all(), [
            'items' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }
        $experience = Experience::where('id',$request->experience_id)->where('estatus',1)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
        
        foreach($request->items as $item) {
            if($item['action'] == 1){
                $BrindItem = new ExperienceProvideItem();
                $BrindItem->experience_id = $request->experience_id;
                $BrindItem->title = $item['title'];
                $BrindItem->save();
            }else if($item['action'] == 2){
                $BrindItem = ExperienceProvideItem::find($item['id']);
                $BrindItem->title = $item['title'];
                $BrindItem->save();
            }else{
                $BrindItem = ExperienceProvideItem::find($item['id']);
                $BrindItem->experience_id = $request->experience_id;
                $BrindItem->delete();
            }
        }  
        
        $Experience = Experience::find($request->experience_id);
        $Experience->proccess_page = 'ProvideItemPage';
        $Experience->save();
        
        return $this->sendResponseSuccess("Added Experience Item Successfully");
    }

    public function addExperienceBrindItem(Request $request){
        
        $validator = Validator::make($request->all(), [
            'items' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }
        $experience = Experience::where('id',$request->experience_id)->where('estatus',1)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
        
        foreach($request->items as $item) {
            if($item['action'] == 1){
                $BrindItem = new ExperienceBrindItem();
                $BrindItem->experience_id = $request->experience_id;
                $BrindItem->title = $item['title'];
                $BrindItem->save();
            }else if($item['action'] == 2){
                $BrindItem = ExperienceBrindItem::find($item['id']);
                $BrindItem->title = $item['title'];
                $BrindItem->save();
            }else{
                $BrindItem = ExperienceBrindItem::find($item['id']);
                $BrindItem->delete();
            }
            
        }  
        
        $Experience = Experience::find($request->experience_id);
        $Experience->is_bring_item = 1;
        $Experience->proccess_page = 'BrindItemPage';
        $Experience->save();
        
        return $this->sendResponseSuccess("Added Experience Brind Item Successfully");
    }

    public function addExperienceMeetLocation(Request $request){
        $messages = [
            'is_meet_address.required' =>'Please provide a is  address.',
            'address.required' =>'Please provide a  address.',
            'city.required' =>'Please provide a city.',
            'state.required' =>'Please provide a state.',
            'country.required' =>'Please provide a country.',
            'pine_code.required' =>'Please provide a pine code.'
        ];

        $validator = Validator::make($request->all(), [
            'is_meet_address' => 'required',
            'address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'country' => 'required',
            'pine_code' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        $experience = Experience::where('id',$request->experience_id)->where('estatus',1)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
    
        $Experience = Experience::find($request->experience_id);
        $Experience->is_meet_address = $request->is_meet_address;
        $Experience->meet_address = $request->address;
        $Experience->meet_address_flat_no = $request->address_flat_no;
        $Experience->meet_city = $request->city;
        $Experience->meet_state = $request->state;
        $Experience->meet_country = $request->country;
        $Experience->pine_code = $request->pine_code;
        $Experience->meet_latitude = $request->latitude;
        $Experience->meet_longitude = $request->longitude;
        $Experience->proccess_page = 'MeetLocationPage';
        $Experience->save();
        return $this->sendResponseSuccess("Added Experience Meet Location Successfully");
    }

    public function addExperienceMaxGroupSize(Request $request){
        $messages = [
            'max_member_public_group_size.required' =>'Please provide a max member public group size.',
            'max_member_private_group_size.required' =>'Please provide a max member private group size.',
        ];

        $validator = Validator::make($request->all(), [
            'max_member_public_group_size' => 'required',
            'max_member_private_group_size' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }
        $experience = Experience::where('id',$request->experience_id)->where('estatus',1)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
    
        $Experience = Experience::find($request->experience_id);
        $Experience->max_member_public_group_size = $request->max_member_public_group_size;
        $Experience->max_member_private_group_size = $request->max_member_private_group_size;
        $Experience->proccess_page = 'GroupSizePage';
        $Experience->save();
        return $this->sendResponseSuccess("Added Experience Group Size Successfully");
    }

    public function addExperienceScheduleTime(Request $request){
        $validator = Validator::make($request->all(), [
            'schedule' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }
        $experience = Experience::where('id',$request->experience_id)->where('estatus',1)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
        //dd($request->schedule);
        foreach($request->schedule as $item) {
           
            if($item['action'] == 1){
                $ScheduleTime = new ExperienceScheduleTime();
                $ScheduleTime->experience_id = $request->experience_id;
                $ScheduleTime->day = $item['day'];
                $ScheduleTime->time = $item['time'];
                $ScheduleTime->save();
            }else if($item['action'] == 2){
                $ScheduleTime = ExperienceScheduleTime::find($item['id']);
                $ScheduleTime->day = $item['day'];
                $ScheduleTime->time = $item['time'];
                $ScheduleTime->save();
            }else{
                $ScheduleTime = ExperienceScheduleTime::find($item['id']);
                $ScheduleTime->delete();
            }
        }  
        
        $Experience = Experience::find($request->experience_id);
        $Experience->proccess_page = 'ScheduleTimePage';
        $Experience->save();
        
        return $this->sendResponseSuccess("Added Experience Schedule Time Successfully");
    }

    public function addExperiencePrice(Request $request){
        $messages = [
            'individual_rate.required' =>'Please provide a individual rate.',
            'min_private_group_rate.required' =>'Please provide a min private group rate.',
        ];

        $validator = Validator::make($request->all(), [
            'individual_rate' => 'required',
            'min_private_group_rate' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }
        $experience = Experience::where('id',$request->experience_id)->where('estatus',1)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
    
        $Experience = Experience::find($request->experience_id);
        $Experience->individual_rate = $request->individual_rate;
        $Experience->min_private_group_rate = $request->min_private_group_rate;
        $Experience->proccess_page = 'PricePage';
        $Experience->save();
        return $this->sendResponseSuccess("Added Experience Price Successfully");
    }

    public function addDiscountGroup(Request $request){
        $validator = Validator::make($request->all(), [
            'discount' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }
        $experience = Experience::where('id',$request->experience_id)->where('estatus',1)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
    
        //dd($request->schedule);
        foreach($request->discount as $dis) {
            if($dis['action'] == 1){
                $ScheduleTime = new ExperienceDiscountRate();
                $ScheduleTime->experience_id = $request->experience_id;
                $ScheduleTime->from_member = $dis['from_member'];
                $ScheduleTime->to_member = $dis['to_member'];
                $ScheduleTime->discount = $dis['discount'];
                $ScheduleTime->save();
            }else if($dis['action'] == 2){
                $ScheduleTime = ExperienceDiscountRate::find($dis['id']);
                $ScheduleTime->from_member = $dis['from_member'];
                $ScheduleTime->to_member = $dis['to_member'];
                $ScheduleTime->discount = $dis['discount'];
                $ScheduleTime->save();
            }else{
                $ScheduleTime = ExperienceDiscountRate::find($dis['id']);
                $ScheduleTime->delete();
            }
        }  
        
        $Experience = Experience::find($request->experience_id);
        $Experience->proccess_page = 'DiscountPage';
        $Experience->save();

        return $this->sendResponseSuccess("Added Experience Discount Price Successfully");
    }

    public function addExperienceCancelletionPolicy(Request $request){
        $validator = Validator::make($request->all(), [
            'cancellation_policy_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }
        $experience = Experience::where('id',$request->experience_id)->where('estatus',1)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
    
        
        $Experience = Experience::find($request->experience_id);
        $Experience->cancellation_policy_id = $request->cancellation_policy_id;
        $Experience->proccess_page = 'CancelletionPolicyPage';
        $Experience->save();

        return $this->sendResponseSuccess("Added Experience Cancelletion Policy Successfully");
    }

    public function addExperienceCategoryAttribute(Request $request){
        $validator = Validator::make($request->all(), [
            'attribute' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }
        $experience = Experience::where('id',$request->experience_id)->where('estatus',1)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }

        foreach($request->attribute as $attr) {
            if($attr['action'] == 1){
                $CategoryAttribute = new ExperienceCategoryAttribute();
                $CategoryAttribute->experience_id = $request->experience_id;
                $CategoryAttribute->cat_attr_id = $attr['cat_attr_id'];
                $CategoryAttribute->value = $attr['value'];
                $CategoryAttribute->type = $attr['type'];
                $CategoryAttribute->save();
            }else if($attr['action'] == 2){
                $CategoryAttribute = ExperienceCategoryAttribute::find($attr['id']);
                $CategoryAttribute->cat_attr_id = $attr['cat_attr_id'];
                $CategoryAttribute->value = $attr['value'];
                $CategoryAttribute->type = $attr['type'];
                $CategoryAttribute->save();
            }else{
                $CategoryAttribute = ExperienceCategoryAttribute::find($attr['id']);
                $CategoryAttribute->delete();

            }
        }  
        
        $Experience = Experience::find($request->experience_id);
        $Experience->proccess_page = 'CategoryAttributePage';
        $Experience->estatus = 4;
        $Experience->save();

        return $this->sendResponseSuccess("Added Experience Category Attribute Successfully");
    }

    public function removeExperience(Request $request){
        $validator = Validator::make($request->all(), [
            'experience_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }
        $experience = Experience::where('id',$request->experience_id)->where('estatus',1)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
    
        $Experience = Experience::find($request->experience_id);
        $Experience->estatus = 3;
        $Experience->save();
        $Experience->delete();

        return $this->sendResponseSuccess("Remove Experience Successfully");
    }

    public function getExperience(Request $request){
        $validator = Validator::make($request->all(), [
            'experience_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }
        $experience = Experience::where('id',$request->experience_id)->where('estatus',1)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
        $data = "";
        $data = new ExperienceResource($experience);
        return $this->sendResponseWithData($data, 'Experience Retrieved successfully.');
    }

    public function getExperiences(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        $user = User::where('id',$request->user_id)->where('estatus',1)->where('role',3)->first();
        if (!$user){
            return $this->sendError("User Not Exist", "Not Found Error", []);
        }

        $experiences = Experience::with('category')->where('user_id',$request->user_id)->orderBy('created_at','DESC')->get();
        $experiences_arr = array();
        foreach ($experiences as $experience){
            $temp = array();
            $temp['id'] = $experience->id;
            $temp['title'] = $experience->title;
            $temp['description'] = $experience->description;
            $temp['category_name'] = isset($experience->category->category_name)?$experience->category->category_name:"";
            $temp['individual_rate'] = $experience->individual_rate;
            $temp['min_private_group_rate'] = $experience->min_private_group_rate;
            $temp['duration'] = $experience->duration;
            array_push($experiences_arr,$temp);
        }

        return $this->sendResponseWithData($experiences_arr,"Experiences Retrieved Successfully.");
    }

    
}
