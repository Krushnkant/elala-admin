<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

        $Wishlists = Wishlist::with('experience')->where('user_id',$request->user_id)->orderBy('created_at','DESC')->get();
        $Wishlists_arr = array();
        foreach ($Wishlists as $Wishlist){
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
            $temp['image'] = isset($Wishlist->experience->media[0])?url($Wishlist->experience->media[0]->thumb):"";
            array_push($Wishlists_arr,$temp);
          
        }
        return $this->sendResponseWithData($Wishlists_arr,"Wishlist Items Retrieved Successfully.");
    }

    
}
