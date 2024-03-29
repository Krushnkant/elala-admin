<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Experience;
use App\Models\User;
use App\Models\ExperienceMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class WishlistController extends BaseController
{
    public function update_wishlist(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'experience_id' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        $experience = Experience::where('id',$request->experience_id)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }

        $user = User::where('id',Auth::user()->id)->where('estatus',1)->where('role',3)->first();
        if (!$user){
            return $this->sendError("User Not Exist", "Not Found Error", []);
        }
           

        $Wishlist = Wishlist::where('user_id',$request->user_id)->where('experience_id',$request->experience_id)->first();
        if($Wishlist){
            $Wishlist->delete();
        }
        else{
            $Wishlist = new Wishlist();
            $Wishlist->user_id = $request->user_id;
            $Wishlist->experience_id = $request->experience_id;
            $Wishlist->save();
        }

        $Wishlist_count = Wishlist::where('user_id',$request->user_id)->count();
        return $this->sendResponseWithData(['total_wishlist_items' => $Wishlist_count],"Wishlist Items Updated Successfully.");
    }

    public function wishlistitem_list(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        $user = User::where('id',Auth::user()->id)->where('estatus',1)->where('role',3)->first();
        if (!$user){
            return $this->sendError("User Not Exist", "Not Found Error", []);
        }

        $Wishlists = Wishlist::with('experience')->where('user_id',$request->user_id)->orderBy('created_at','DESC')->get();
        $Wishlists_arr = array();
        foreach ($Wishlists as $Wishlist){
           $experiencemedias = ExperienceMedia::where('experience_id',$Wishlist->experience_id)->where('type', '=', 'img')->get(['id','thumb','type'])->toArray(); 
          
            $media_array = array();
            if(isset($Wishlist->experience->image) && $Wishlist->experience->image != ""){
                $media_array[0]['id'] = 0;
                $media_array[0]['thumb'] = 'images/experience_images_thumb/'.$Wishlist->experience->image;
                $media_array[0]['type'] = 'img';
            }
            foreach($experiencemedias as $media){
                $temp = array();
                $temp['id'] = $media['id'];
                $temp['thumb'] = 'images/experience_images_thumb/'.$media['thumb'];
                $temp['type'] = $media['type'];
                array_push($media_array,$temp);
            }

            $temp = array();
            $temp['user_id'] = $Wishlist->user_id;
            $temp['experience_id'] = $Wishlist->experience_id;
            $temp['id'] = $Wishlist->experience->id;
            $temp['slug'] = $Wishlist->experience->slug;
            $temp['title'] = $Wishlist->experience->title;
            $temp['description'] = $Wishlist->experience->description;
            $temp['location'] = $Wishlist->experience->location;
            $temp['individual_rate'] = $Wishlist->experience->individual_rate;
            $temp['duration'] = $Wishlist->experience->duration;
            //$temp['image'] = isset($Wishlist->experience->media[0])?url($Wishlist->experience->media[0]->thumb):"";
            $temp['image'] = isset($media_array)?$media_array:[];
            $temp['rating'] = $Wishlist->experience->rating;
            $temp['rating_member'] = $Wishlist->experience->review_total_user;
            array_push($Wishlists_arr,$temp);
          
        }
        return $this->sendResponseWithData($Wishlists_arr,"Wishlist Items Retrieved Successfully.");
    }

    
}
