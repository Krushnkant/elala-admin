<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ {User,Experience,CategoryAttribute,ExperienceMedia,ExperienceBrindItem,ExperienceProvideItem,ExperienceScheduleTime,ExperienceDiscountRate,ExperienceCategoryAttribute,City,State,Country,Category,Language,AgeGroup,ExperienceCancellationPolicy,Review,ExperienceLanguage,ExperienceCategory};
use App\Http\Resources\ExperienceResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
            $experience = Experience::where('id',$request->experience_id)->first();
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
        $data = new ExperienceResource($Experience);
        return $this->sendResponseWithData($data,"Added Experience Successfully");
    }

    public function addExperienceLocation(Request $request){
        $messages = [
            'location.required' =>'Please provide a location.',
            'latitude.required' =>'Please provide a latitude.',
            'longitude.required' =>'Please provide a longitude.',
            'language.required' =>'Please provide a language.',
            'city.required' =>'Please provide a city.',
            'state.required' =>'Please provide a state.',
            'country.required' =>'Please provide a country.',
        ];

        $validator = Validator::make($request->all(), [
            'location' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'language' => 'required',
            'city' => 'required',
            'state' => 'required',
            'country' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        $experience = Experience::where('id',$request->experience_id)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
    
        $Experience = Experience::find($request->experience_id);
        $Experience->location = $request->location;
        $Experience->latitude = $request->latitude;
        $Experience->longitude = $request->longitude;
        $country = Country::where('name',$request->country)->first();
        if(!$country){
            $coun = New Country();
            $coun->name = $request->country;
            $coun->save(); 
            $country_id = $coun->id;

        }else{
            $country_id = $country->id;
        }

        $state = State::where('name',$request->state)->first();
        if(!$state){
            $sta = New State();
            $sta->name = $request->state;
            $sta->country_id = $country_id;
            $sta->save(); 
            $state_id = $sta->id;

        }else{
            $state_id = $state->id;
        }

        $city = City::where('name',$request->city)->first();
        if(!$city){
            $cit = New City();
            $cit->name = $request->city;
            $cit->state_id = $state_id;
            $cit->save(); 
            $city_id = $cit->id;

        }else{
            $city_id = $city->id;
        }

        
        $Experience->city = ($city_id)?$city_id:1041;
        $Experience->state = ($state_id)?$state_id:12;
        $Experience->country = ($country_id)?$country_id:101;
        if(checkExperienceStatus('LocationPage',$Experience->proccess_page)){
            $Experience->proccess_page = 'LocationPage';
        }
        $Experience->save();

        $languages = explode(',',$request->language);
        $ExperienceLanguageOld = ExperienceLanguage::where('experience_id',$request->experience_id)->get()->pluck('language_id')->toArray();
     
        $deleteids = array();
        foreach($ExperienceLanguageOld as $LanguageOld){
            if(!in_array($LanguageOld,$languages)){
                $deleteids[] = $LanguageOld;
            }
        }
      
        foreach($languages as $lans){
            if(!in_array($lans,$ExperienceLanguageOld)){  
                $ExperienceLanguage = ExperienceLanguage::where('experience_id',$request->experience_id)->where('language_id',$lans)->first();
                if($ExperienceLanguage == ""){
                    $Language = New ExperienceLanguage();
                    $Language->experience_id = $request->experience_id;
                    $Language->language_id = $lans;
                    $Language->save();
                }
            }
        }

        foreach($deleteids as $deleteid){
            $LanguageDelete = ExperienceLanguage::where('experience_id',$request->experience_id)->where('language_id',$deleteid)->first();
            $LanguageDelete->delete();
        }
        

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
        $experience = Experience::where('id',$request->experience_id)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
    
        $Experience = Experience::find($request->experience_id);
        $Experience->category_id = $request->category_id;
        if(checkExperienceStatus('CategoryPage',$Experience->proccess_page)){
            $Experience->proccess_page = 'CategoryPage';
        }
        $Experience->save();

       


        $attributes_arr = array();
        if($Experience){

            ExperienceCategory::where('experience_id',$request->experience_id)->delete();
            $maincategories = $this->getMainCategory($request->category_id);
            foreach($maincategories as $maincategory){
                $ExperienceCategory = New ExperienceCategory();
                $ExperienceCategory->experience_id = $request->experience_id;
                $ExperienceCategory->category_id = $maincategory;
                $ExperienceCategory->save();
            }

            $categoryAttribute= CategoryAttribute::with('attr_optioin')->where('category_id',$request->category_id)->get();
            foreach ($categoryAttribute as $attribute){
                $temp = array();
                $temp['id'] = $attribute->id;
                $temp['field_id'] = $attribute->field_id;
                $temp['title'] = $attribute->title;
                $temp['option'] = $attribute->attr_optioin;
                array_push($attributes_arr,$temp);
            }

            $categoryAttribute= CategoryAttribute::with('attr_optioin')->where('category_id',$request->category_id)->get();
            foreach ($categoryAttribute as $attribute){
                $temp = array();
                $temp['id'] = $attribute->id;
                $temp['field_id'] = $attribute->field_id;
                $temp['title'] = $attribute->title;
                $temp['option'] = $attribute->attr_optioin;
                array_push($attributes_arr,$temp);
            }
        }
        return $this->sendResponseWithData($attributes_arr,"Added Experience Category Successfully");
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

        $experience = Experience::where('id',$request->experience_id)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
    
        $Experience = Experience::find($request->experience_id);
        $Experience->title = $request->title;
        $Experience->slug = createSlug($request->title);
        $Experience->description = $request->description;
        if(checkExperienceStatus('DetailsPage',$Experience->proccess_page)){
            $Experience->proccess_page = 'DetailsPage';
        }
        $Experience->save();
        return $this->sendResponseSuccess("Added Experience Details Successfully");
    }

    public function addExperienceMedia(Request $request){
        $validator = Validator::make($request->all(), [
            'image.*' => 'image|mimes:jpeg,png,jpg',
            'images.*' => 'image|mimes:jpeg,png,jpg',
            //'video' => 'mimes:mp4,ogx,oga,ogv,ogg,webm',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }
        $experience = Experience::where('id',$request->experience_id)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
    
        if($request->hasFile('images')) {
            $experience_images = array();
            foreach ($request->file('images') as $key => $image) {
                $ExperienceMedia = new ExperienceMedia();
                $ExperienceMedia->experience_id = $request->experience_id;
                $image_name = 'experience_images_' . rand(111111, 999999) . time() . '.' . $image->getClientOriginalExtension();
                // $destinationPath = public_path('images/experience_images');
                // $image->move($destinationPath, $image_name);

                $destinationPath = public_path('images/experience_images/'.$image_name);
                $imageTemp = $_FILES["images"]["tmp_name"][$key];

                $destinationPaththumb = public_path('images/experience_images_thumb/'.$image_name);
                $imageTempthumb = $_FILES["images"]["tmp_name"][$key];

                compressImage($imageTemp, $destinationPath, 80);
                compressImage($imageTempthumb, $destinationPaththumb, 40);
                
               // array_push($experience_images,'images/experience_images/'.$image_name);
                //$ExperienceMedia->thumb = 'images/experience_images/'.$image_name;
                $ExperienceMedia->thumb = $image_name;
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

        if($request->hasFile('image')) {
            $image = $request->file('image');
            $image_name = 'experience_images_' . rand(111111, 999999) . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('images/experience_images/'.$image_name);
            $imageTemp = $_FILES["image"]["tmp_name"];
            compressImage($imageTemp, $destinationPath, 80);
            $destinationPaththumb = public_path('images/experience_images_thumb/'.$image_name);
            $imageTempthumb = $_FILES["image"]["tmp_name"];
            compressImage($imageTempthumb, $destinationPaththumb, 40);
            // $destinationPath = public_path('images/experience_images');
            // $image->move($destinationPath, $image_name);
            //$Experience->image = 'images/experience_images/'.$image_name;
            $Experience->image = $image_name;
        }
        
        if(checkExperienceStatus('MediaPage',$Experience->proccess_page)){
            $Experience->proccess_page = 'MediaPage';
        }
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
        $experience = Experience::where('id',$request->experience_id)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
    
        $Experience = Experience::find($request->experience_id);
        $Experience->duration = $request->duration;
        $Experience->age_limit = $request->age_id;
        if(checkExperienceStatus('AgePage',$Experience->proccess_page)){
            $Experience->proccess_page = 'AgePage';
        }
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
        $experience = Experience::where('id',$request->experience_id)->first();
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
        
        if(checkExperienceStatus('ProvideItemPage',$Experience->proccess_page)){
            $Experience->proccess_page = 'ProvideItemPage';
        }
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
        $experience = Experience::where('id',$request->experience_id)->first();
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
        if(checkExperienceStatus('BrindItemPage',$Experience->proccess_page)){
            $Experience->proccess_page = 'BrindItemPage';
        }
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

        $experience = Experience::where('id',$request->experience_id)->first();
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
        
        if(checkExperienceStatus('MeetLocationPage',$Experience->proccess_page)){
            $Experience->proccess_page = 'MeetLocationPage';
        }
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
        $experience = Experience::where('id',$request->experience_id)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
    
        $Experience = Experience::find($request->experience_id);
        $Experience->max_member_public_group_size = $request->max_member_public_group_size;
        $Experience->max_member_private_group_size = $request->max_member_private_group_size;
        
        if(checkExperienceStatus('GroupSizePage',$Experience->proccess_page)){
            $Experience->proccess_page = 'GroupSizePage';
        }
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
        $experience = Experience::where('id',$request->experience_id)->first();
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
        
        if(checkExperienceStatus('ScheduleTimePage',$Experience->proccess_page)){
            $Experience->proccess_page = 'ScheduleTimePage';
        }
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
        $experience = Experience::where('id',$request->experience_id)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
    
        $Experience = Experience::find($request->experience_id);
        $Experience->individual_rate = $request->individual_rate;
        $Experience->min_private_group_rate = $request->min_private_group_rate;
        
        if(checkExperienceStatus('PricePage',$Experience->proccess_page)){
            $Experience->proccess_page = 'PricePage';
        }
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
        $experience = Experience::where('id',$request->experience_id)->first();
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
        
        if(checkExperienceStatus('DiscountPage',$Experience->proccess_page)){
            $Experience->proccess_page = 'DiscountPage';
        }
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
        $experience = Experience::where('id',$request->experience_id)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
    
        
        $Experience = Experience::find($request->experience_id);
        $Experience->cancellation_policy_id = $request->cancellation_policy_id;
        
        if(checkExperienceStatus('CancelletionPolicyPage',$Experience->proccess_page)){
            $Experience->proccess_page = 'CancelletionPolicyPage';
        }
        $Experience->estatus = 4;
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
        $experience = Experience::where('id',$request->experience_id)->first();
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
        if(checkExperienceStatus('CategoryAttributePage',$Experience->proccess_page)){
            $Experience->proccess_page = 'CategoryAttributePage';
        }
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
        $experience = Experience::where('id',$request->experience_id)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
    
        $Experience = Experience::find($request->experience_id);
        $Experience->estatus = 3;
        $Experience->save();
        $Experience->delete();

        return $this->sendResponseSuccess("Remove Experience Successfully");
    }

    public function removeMediaExperience(Request $request){
        $validator = Validator::make($request->all(), [
            'media_id' => 'required',
        ]);
        

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }
        $ExperienceMedia = ExperienceMedia::where('id',$request->media_id)->first();
        if (!$ExperienceMedia){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }

        $ExperienceMedia = ExperienceMedia::find($request->media_id);
        $ExperienceMedia->delete();

        return $this->sendResponseSuccess("Remove Media Experience Successfully");
    }

    public function getExperience(Request $request){
        $validator = Validator::make($request->all(), [
            'experience_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }
        $experience = Experience::where('id',$request->experience_id)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
        $data = "";
        $data = new ExperienceResource($experience);
        return $this->sendResponseWithData($data, 'Experience Retrieved successfully.');
    }

    public function getExperiences(Request $request){
       
        $user = User::where('id',Auth::user()->id)->where('estatus',1)->where('role',3)->first();
        if (!$user){
            return $this->sendError("User Not Exist", "Not Found Error", []);
        }

        $experiences = Experience::with('category')->where('user_id',Auth::user()->id)->orderBy('created_at','DESC')->get();
        $experiences_arr = array();
        foreach ($experiences as $experience){
            $status = getExperienceStatus($experience->estatus);
            $temp = array();
            $temp['id'] = $experience->id;
            $temp['title'] = $experience->title;
            $temp['description'] = $experience->description;
            $temp['category_name'] = isset($experience->category->category_name)?$experience->category->category_name:"";
            $temp['individual_rate'] = $experience->individual_rate;
            $temp['min_private_group_rate'] = $experience->min_private_group_rate;
            $temp['duration'] = $experience->duration;
            $temp['status'] = $status['experience_status'];
            array_push($experiences_arr,$temp);
        }

        return $this->sendResponseWithData($experiences_arr,"Experiences Retrieved Successfully.");
    }


    public function city($text){
        $cities = City::select('cities.*','states.name as stete_name','countries.name as country_name')->leftJoin('states', function($join) {
            $join->on('states.id', '=', 'cities.state_id');
          })->leftJoin('countries', function($join) {
            $join->on('countries.id', '=', 'states.country_id');
          });

          if ($text) {
            $cities = $cities->where('cities.name', 'LIKE', "$text%");
        }

          
        $cities = $cities->get();
        $cities_arr = array();
        foreach ($cities as $city){
            $temp = array();
            $temp['id'] = $city->id;
            $temp['name'] = $city->name.','.$city->stete_name.','.$city->country_name;
       
            array_push($cities_arr,$temp);
        }
        return $this->sendResponseWithData($cities_arr,"City Retrieved Successfully.");
    }

    public function otherlist()
    {
        $data = array();
        $categories = Category::where('parent_category_id',0)->where('estatus',1)->orderBy('sr_no','asc')->get();
        $categories_arr = array();
        foreach ($categories as $category){
            $temp = array();
            $temp['id'] = $category->id;
            $temp['name'] = $category->category_name;
            $temp['category_thumb'] = $category->category_thumb;
            $temp['child_category'] = getSubCategories($category->id);
            array_push($categories_arr,$temp);
        }

        $languages = Language::where('estatus',1)->orderBy('id','asc')->get();
        $languages_arr = array();
        foreach ($languages as $language){
            $temp = array();
            $temp['id'] = $language->id;
            $temp['name'] = $language->title;
            array_push($languages_arr,$temp);
        }

        $agegroups = AgeGroup::where('estatus',1)->orderBy('id','asc')->get();
        $agegroups_arr = array();
        foreach ($agegroups as $agegroup){
            $temp = array();
            $temp['id'] = $agegroup->id;
            $temp['name'] = $agegroup->from_age .' to '.$agegroup->to_age;
            array_push($agegroups_arr,$temp);
        }

        $policies = ExperienceCancellationPolicy::orderBy('id','asc')->get();

        $policies_arr = array();
        foreach ($policies as $policie){
            $temp = array();
            $temp['id'] = $policie->id;
            $temp['title'] = $policie->title;
            $temp['description'] = $policie->description;
            array_push($policies_arr,$temp);
        }
        $data = array('categories' => $categories_arr,'languages' => $languages_arr,'agegroups_arr'=>$agegroups_arr,'policies_arr'=>$policies_arr);
        return $this->sendResponseWithData($data,"Other List Retrieved Successfully.");
    }


    //////////////////


    public function getHomeExperiences(Request $request){

        $limit = isset($request->limit)?$request->limit:20;
        $treding_experiences = Experience::with(['media' => function($q) {
                $q->where('type', '=', 'img')->get(['id','thumb'])->toArray(); 
            }])->where('estatus',1)->get();
           
        
        $treding_experiences_arr = array();
        foreach ($treding_experiences as $experience){
            $media_array = array();
            if($experience['image'] != ""){
                $media_array[0]['id'] = 0;
                $media_array[0]['thumb'] = 'images/experience_images_thumb/'.$experience['image'];
                $media_array[0]['type'] = 'img';
            }
            
            foreach($experience->media as $media){
                $temp = array();
                $temp['id'] = $media['id'];
                $temp['thumb'] = 'images/experience_images_thumb/'.$media['thumb'];
                $temp['type'] = $media['type'];
                array_push($media_array,$temp);
            }
           // dd($experience->media);
            // $coverimage = array('id'=>'0','thumb'=> $experience->image);
            // array_unshift($experience->media, $coverimage);
            //$experience->nedia->push((object)['id' => 'Game1','thumb' => 'sdsdd']);
           // dd($experience->media);
            // if($experience->category_id > 0){
            //     $maincategories = $this->getMainCategory($experience->category_id);
            //     foreach($maincategories as $maincategory){
            //         $ExperienceCategory = New ExperienceCategory();
            //         $ExperienceCategory->experience_id = $experience->id;
            //         $ExperienceCategory->category_id = $maincategory;
            //         $ExperienceCategory->save();
            //     }
            // }

            $temp = array();
            $temp['id'] = $experience->id;
            $temp['slug'] = $experience->slug;
            $temp['title'] = $experience->title;
            $temp['location'] = $experience->location;
            $temp['individual_rate'] = $experience->individual_rate;
            $temp['duration'] = $experience->duration;
            $temp['image'] = isset($media_array)?$media_array:[];
            $temp['rating'] = $experience->rating;
            $temp['rating_member'] = $experience->review_total_user;
            array_push($treding_experiences_arr,$temp);
        }


        $experiences_near_you = Experience::with(['media' => function($q) {
            $q->where('type', '=', 'img'); 
        }])->where('estatus',1)->paginate($limit);
    
        $experiences_near_you_arr = array();
        foreach ($experiences_near_you as $experience){
            $media_array = array();
            if($experience['image'] != ""){
                $media_array[0]['id'] = 0;
                $media_array[0]['thumb'] = 'images/experience_images_thumb/'.$experience['image'];
                $media_array[0]['type'] = 'img';
            }
            foreach($experience->media as $media){
                $temp = array();
                $temp['id'] = $media['id'];
                $temp['thumb'] = 'images/experience_images_thumb/'.$media['thumb'];
                $temp['type'] = $media['type'];
                array_push($media_array,$temp);
            }
            $temp = array();
            $temp['id'] = $experience->id;
            $temp['slug'] = $experience->slug;
            $temp['title'] = $experience->title;
            $temp['location'] = $experience->location;
            $temp['individual_rate'] = $experience->individual_rate;
            $temp['duration'] = $experience->duration;
            $temp['image'] = isset($media_array)?$media_array:[];
            $temp['rating'] = $experience->rating;
            $temp['rating_member'] = $experience->review_total_user;
            array_push($experiences_near_you_arr,$temp);
        }

        $data['treding_places'] = $treding_experiences_arr;
        $data['experiences_near_you'] = $experiences_near_you_arr;
        return $this->sendResponseWithData($data,"Experiences Retrieved Successfully.");
    }

    public function ExperienceDetails(Request $request,$slug){
       
        $experience = Experience::where('slug',$slug)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
        $id = $experience->id;
        $ProvideItem = ExperienceProvideItem::where('experience_id',$id)->get(['id','title']);
        $BrindItem = ExperienceBrindItem::where('experience_id',$id)->get(['id','title']);
        $Images = ExperienceMedia::where('experience_id',$id)->where('type','img')->get(['id','thumb']);
        $Videos = ExperienceMedia::where('experience_id',$id)->where('type','video')->get(['id','thumb']);
        $DiscountRate = ExperienceDiscountRate::where('experience_id',$id)->get(['id','from_member','to_member','discount']);
        $ScheduleTime = ExperienceScheduleTime::where('experience_id',$id)->get(['id','day','time']);
        $ExperienceLanguage = ExperienceLanguage::with('language')->where('experience_id',$id)->get();
        $lan_titles = array();
        foreach($ExperienceLanguage as $ExLanguage){
             $lan_titles[] = $ExLanguage->language->title;
        }
        $lan_string = implode(',',$lan_titles);
        $is_in_wishlist = false;
        if(isset($request->user_id) && $request->user_id!=0 && $request->user_id!="") {
            $wishlist = \App\Models\Wishlist::where('user_id',$request->user_id)->where('experience_id',$id)->first();
            if ($wishlist){
                $is_in_wishlist = true;
            }
        }
        $hostUsers = User::where('id',$experience->user_id)->first();
        if($hostUsers){
            $host['id'] = $hostUsers->id;
            $host['full_name'] = $hostUsers->full_name;
            $host['bio'] = $hostUsers->bio;
            $host['profile_pic'] = $hostUsers->profile_pic;
            $host['rating'] = hostRating($hostUsers->id);
            $host['rating_member'] = hostReviewMember($hostUsers->id);
        }else{
            $host = "";
        }

        $media_array = array();
        $media_array[0]['id'] = 0;
        $media_array[0]['thumb'] = 'images/experience_images/'.$experience['image'];
        $media_array[0]['type'] = 'img';
        foreach($Images as $media){
            $temp = array();
            $temp['id'] = $media['id'];
            $temp['thumb'] = 'images/experience_images/'.$media['thumb'];
            $temp['type'] = $media['type'];
            array_push($media_array,$temp);
        }

        $video_array = array();
        foreach($Videos as $media){
            $temp = array();
            $temp['id'] = $media['id'];
            $temp['thumb'] = 'images/experience_videos/'.$media['thumb'];
            $temp['type'] = $media['type'];
            array_push($video_array,$temp);
        }
        $data =  [
            'id' => $experience->id,
            'slug' => $experience->slug,
            'type' => $experience->type,
            'location' => $experience->location,
            'latitude' => $experience->latitude,
            'longitude' => $experience->longitude,
            'category_id' => $experience->category_id,
            'title' => $experience->title,
            'description' => $experience->description,
            'images' => $media_array,
            'videos' => $video_array,
            'duration' => $experience->duration,
            'age_limit' => explode(',',$experience->age_limit),
            'provide_items' => $ProvideItem,
            'is_bring_item' => $experience->is_bring_item,
            'brind_items' => $BrindItem,
            'is_meet_address' => $experience->is_meet_address,
            'meet_address' => $experience->meet_address,
            'meet_address_flat_no' => $experience->meet_address_flat_no,
            'meet_city' => $experience->meet_city,
            'meet_state' => $experience->meet_state,
            'meet_country' => $experience->meet_country,
            'pine_code' => $experience->pine_code,
            'meet_latitude' => $experience->meet_latitude,
            'meet_longitude' => $experience->meet_longitude,
            'max_member_public_group_size' => $experience->max_member_public_group_size,
            'max_member_private_group_size' => $experience->max_member_private_group_size,
            'individual_rate' => $experience->individual_rate,
            'min_private_group_rate' => $experience->min_private_group_rate,
            'discount_rate' => $DiscountRate,
            'schedule_time' => $ScheduleTime,
            'experience_language' => $lan_string,
            'cancellation_policy_id' => $experience->cancellation_policy_id,
            'rating' => $experience->rating,
            'rating_member' => $experience->review_total_user,
            'estatus' => $experience->estatus,
            'host' => $host,
            'is_in_wishlist' => $is_in_wishlist
            
        ];
        
        return $this->sendResponseWithData($data, 'Experience Details Retrieved successfully.');
    }

    public function getRelatedExperiences($slug){
         
        $experience = Experience::where('slug',$slug)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
        $id = $experience->id;
        $experiences = Experience::with(['media' => function($q) {
                $q->where('type', '=', 'img'); 
            }])->where('category_id',$experience->category_id)->where('id', '!=',$id)->where('estatus',1)->limit('10')->get();

        $experiences_arr = array();
        $experiences_ids = array();
        foreach ($experiences as $experience){
            $experiences_ids[] = $experience->id;
            $media_array = array();
            if($experience['image'] != ""){
                $media_array[0]['id'] = 0;
                $media_array[0]['thumb'] = 'images/experience_images_thumb/'.$experience['image'];
                $media_array[0]['type'] = 'img';
            }
            foreach($experience->media as $media){
                $temp = array();
                $temp['id'] = $media['id'];
                $temp['thumb'] = 'images/experience_images_thumb/'.$media['thumb'];
                $temp['type'] = $media['type'];
                array_push($media_array,$temp);
            }
            $temp = array();
            $temp['id'] = $experience->id;
            $temp['slug'] = $experience->slug;
            $temp['title'] = $experience->title;
            $temp['description'] = $experience->description;
            $temp['location'] = $experience->location;
            $temp['individual_rate'] = $experience->individual_rate;
            $temp['duration'] = $experience->duration;
            $temp['image'] = isset($media_array)?$media_array:"";
            $temp['rating'] = $experience->rating;
            $temp['rating_member'] = 1;
            array_push($experiences_arr,$temp);
        }

        if(count($experiences_arr) < 10){
            $num = 10 - count($experiences_arr);
            $experiences = Experience::with(['media' => function($q) {
                $q->where('type', '=', 'img'); 
            }])->where('id', '!=',$id)->whereNotIn('id',$experiences_ids)->where('estatus',1)->limit($num)->get();

            $experiences_arr = array();
            foreach ($experiences as $experience){
                
                $media_array = array();
                if($experience['image'] != ""){
                    $media_array[0]['id'] = 0;
                    $media_array[0]['thumb'] = 'images/experience_images_thumb/'.$experience['image'];
                    $media_array[0]['type'] = 'img';
                }
                foreach($experience->media as $media){
                    $temp = array();
                    $temp['id'] = $media['id'];
                    $temp['thumb'] = 'images/experience_images_thumb/'.$media['thumb'];
                    $temp['type'] = $media['type'];
                    array_push($media_array,$temp);
                }
                $temp = array();
                $temp['id'] = $experience->id;
                $temp['slug'] = $experience->slug;
                $temp['title'] = $experience->title;
                $temp['description'] = $experience->description;
                $temp['location'] = $experience->location;
                $temp['individual_rate'] = $experience->individual_rate;
                $temp['duration'] = $experience->duration;
                $temp['image'] = isset($media_array)?$media_array:"";
                $temp['rating'] = $experience->rating;
                $temp['rating_member'] = 1;
                array_push($experiences_arr,$temp);
            }

        }
        
        return $this->sendResponseWithData($experiences_arr,"Related Experiences Retrieved Successfully.");
    }

    public function getReviewExperiences($slug){
        $experience = Experience::where('slug',$slug)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
        $id = $experience->id;
        $reviews = Review::with('user')->where('experience_id',$id)->where('estatus',1)->get();
        $reviews_arr = array();
        foreach ($reviews as $review){
            $temp = array();
            $temp['id'] = $review->id;
            $temp['description'] = $review->description;
            $temp['rating'] = $review->rating;
            $temp['user_id'] = $review->customer_id;
            $temp['full_name'] = $review->user->full_name;
            $temp['profile_image'] = isset($review->user->profile_pic)?$review->user->profile_pic:"";
            $temp['created_at'] = $review->created_at;
            array_push($reviews_arr,$temp);
        }

        return $this->sendResponseWithData($reviews_arr,"Review Experiences Retrieved Successfully.");
    }

    public function getAvailableTimeExperiences($id,$day){
        $times = ExperienceScheduleTime::where('experience_id',$id)->where('day',$day)->get();
        $times_arr = array();
        foreach ($times as $time){
            $experience = Experience::where('id',$id)->first();
            $temp = array();
            $temp['id'] = $time->id;
            $temp['experience_id'] = $time->experience_id;
            $temp['day'] = $time->day;
            $temp['time'] = $time->time;
            $time1 = Carbon::parse($time->time);
            $endTime = $time1->addMinutes($experience->duration);
            $temp['end_time'] = $endTime->format('H:i:s');
            array_push($times_arr,$temp);
        }

        return $this->sendResponseWithData($times_arr,"Available Time Experience Retrieved Successfully.");
    }

    public function getFilterExperiences(Request $request){
        
        $limit = isset($request->limit)?$request->limit:20;
        $experiences = Experience::with(['media' => function($q) {
                $q->where('type', '=', 'img'); 
            }]);
            if (isset($request->min_price) && isset($request->max_price)){
                $experiences = $experiences->whereBetween('individual_rate',[$request->min_price,$request->max_price]);
            }
            if (isset($request->language) && $request->language!=""){
                $language = explode(",",$request->language);
                $experiences = $experiences->whereHas('experiencelanguage',function ($query) use($request, $language) {
                    $query->whereIn('language_id',$language);
                });
            }
            if (isset($request->days) && $request->days!=""){
                $days = explode(",",$request->days);
                $experiences = $experiences->whereHas('scheduletime',function ($query) use($request, $days) {
                    $query->whereIn('day',$days);
                });
            }
            if (isset($request->categories) && $request->categories!=""){
                $category_ids = explode(",",$request->categories);
                //$experiences = $experiences->whereIn('category_id',$category_ids);
                $experiences = $experiences->whereHas('experiencecategory',function ($query) use($request, $category_ids) {
                    $query->whereIn('category_id',$category_ids);
                });
            }
            if (isset($request->activity_type) && $request->activity_type!=""){
                $experiences = $experiences->where('type',$request->activity_type);
            }
            if (isset($request->city) && $request->city!=""){
                $city_ids = explode(",",$request->city);
                $experiences = $experiences->whereIn('city',$city_ids);
            }
            if (isset($request->state) && $request->state!=""){
                $state_ids = explode(",",$request->state);
                $experiences = $experiences->whereIn('state',$state_ids);
            }
            if (isset($request->country) && $request->country!=""){
                $country_ids = explode(",",$request->country);
                $experiences = $experiences->whereIn('country',$country_ids);
            }
            if (isset($request->sort_order) && $request->sort_order=="asc"){
                $experiences = $experiences->orderBy('individual_rate','ASC');
            }
    
            if (isset($request->sort_order) && $request->sort_order=="desc"){
                $experiences = $experiences->orderBy('individual_rate','DESC');
            }
    
            $experiences = $experiences->where('estatus',1)->paginate($limit);
        
        $experiences_arr = array();
        foreach ($experiences as $experience){
            $temp = array();
            $temp['id'] = $experience->id;
            $temp['slug'] = $experience->slug;
            $temp['title'] = $experience->title;
            $temp['location'] = $experience->location;
            $temp['individual_rate'] = $experience->individual_rate;
            $temp['duration'] = $experience->duration;
            $temp['image'] = isset($experience->media)?$experience->media:[];
            $temp['rating'] = $experience->rating;
            $temp['rating_member'] = $experience->review_total_user;
            array_push($experiences_arr,$temp);
        }


        $data['experiences'] = $experiences_arr;
        return $this->sendResponseWithData($data,"Experiences Retrieved Successfully.");
    }

    public $catid = [];
    function getMainCategory($id){
        $category = \App\Models\Category::where('estatus',1)->where('id',$id)->first();
        if($category->parent_category_id != 0){
            $this->catid[] = $category->id;
            $this->getMainCategory($category->parent_category_id);
        }else{
            $this->catid[] = $category->id; 
        }
        return $this->catid;
    }

    
}
