<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ {Post,PostTag,PostMedia,User};
use Illuminate\Support\Facades\Auth;

class PostController extends BaseController
{
    public function create_post(Request $request){
        $messages = [
            'description.required' =>'Please provide a Description',
        ];

        $validator = Validator::make($request->all(), [
            'description' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        if($request->post_id > 0){
            $post =  Post::find($request->post_id);
        }else{
            $post = new Post();  
        }

        $post->user_id = Auth::user()->id;
        $post->description = $request->description;
        $post->is_private = $request->is_private;
        $post->host_tag = isset($request->host_tag)?$request->host_tag:"";
        $post->save();
        
        if($post){
            if(isset($request->tags)){
              $tags =  explode(",",$request->tags);
              $PostTagOld = PostTag::where('post_id',$post->id)->get()->pluck('user_id')->toArray();
              $deleteids = array();
                foreach($PostTagOld as $PostOld){
                    if(!in_array($PostOld,$tags)){
                        $deleteids[] = $PostOld;
                    }
                }

                foreach($tags as $tag){
                    if(!in_array($tag,$PostTagOld)){  
                        $PostTags = PostTag::where('post_id',$post->id)->where('user_id',$tag)->first();
                        if($PostTags == ""){
                            $posttag = new PostTag();
                            $posttag->post_id = $post->id;
                            $posttag->user_id = $tag;
                            $posttag->save();
                        }
                    }
               }
            }
        }
        $allowedMimeTypes = ['jpeg','gif','png','bmp','svg','PNG','JPEG','jpg','JPG'];
        if($request->hasFile('media')) {
            foreach ($request->file('media') as $image) {
                $postmedia = new PostMedia();
                $postmedia->post_id = $post->id;
                $image_name = 'post_images_' . rand(111111, 999999) . time() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('images/post_media');
                $image->move($destinationPath, $image_name);
               // array_push($experience_images,'images/experience_images/'.$image_name);
                $postmedia->name = 'images/post_media/'.$image_name;
                if(in_array($image->getClientOriginalExtension(), $allowedMimeTypes)){
                    $postmedia->type = 0;
                }else{
                    $postmedia->type = 1;
                }
                $postmedia->save();
            }  
        }
        return $this->sendResponseSuccess("Post Added Successfully");
    }

    public function get_my_posts(Request $request){
       
        $user = User::where('id',Auth::user()->id)->where('estatus',1)->first();
        if (!$user){
            return $this->sendError("User Not Exist", "Not Found Error", []);
        }
        $posts = Post::with('posttags.user')->where('user_id',Auth::user()->id)->orderBy('created_at','DESC')->get();
        $posts_arr = array();
        foreach ($posts as $post){
            $tag_array = array();
            foreach($post->posttags as $posttag){
                $tag['id'] = $posttag->user->id;
                $tag['name'] = $posttag->user->full_name;
                array_push($tag_array,$tag);    
            }
            $temp = array();
            $temp['id'] = $post->id;
            $temp['description'] = $post->description;
            $temp['is_private'] = $post->is_private;
            $temp['posttags'] = $tag_array;
            $temp['postmedia'] = $post->postmedia;
            $temp['host_tag_name'] = $post->hosttag->full_name;
            array_push($posts_arr,$temp);
        }

        return $this->sendResponseWithData($posts_arr,"Post Retrieved Successfully.");
    }
}
