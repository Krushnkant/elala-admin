<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ {Post,PostTag,PostMedia,User,PostLike,PostCommant};
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

    public function delete_post(Request $request){
        $validator = Validator::make($request->all(), [
            'post_id' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        $post = Post::where('id',$request->post_id)->first();
        if (!$post){
            return $this->sendError("Post Not Exist", "Not Found Error", []);
        }

        PostCommant::where('post_id',$request->post_id)->delete();
        PostLike::where('post_id',$request->post_id)->delete();
        PostTag::where('post_id',$request->post_id)->delete();
        $postmedia = PostMedia::where('post_id',$request->post_id)->get();
            foreach($postmedia as $media){
                $image = public_path($media->name);
                if (file_exists($image)) {
                    unlink($image);
                }
                $media->delete();
            }

        $post->estatus = 3;
        $post->save();
        $post->delete();
        return $this->sendResponseSuccess("Post Deleted Successfully.");
    }

    public function get_my_posts(Request $request){
        $limit = isset($request->limit)?$request->limit:20;
        $user = User::where('id',Auth::user()->id)->where('estatus',1)->first();
        if (!$user){
            return $this->sendError("User Not Exist", "Not Found Error", []);
        }
        $posts = Post::with('posttags.user')->where('user_id',Auth::user()->id)->orderBy('created_at','DESC')->paginate($limit);
        $posts_arr = array();
        foreach ($posts as $post){
            $tag_array = array();
            foreach($post->posttags as $posttag){
                if($posttag->user){
                    $tag['id'] = $posttag->user->id;
                    $tag['name'] = $posttag->user->full_name;
                    array_push($tag_array,$tag);
                }    
            }
            $temp = array();
            $temp['id'] = $post->id;
            $temp['description'] = $post->description;
            $temp['is_private'] = $post->is_private;
            $temp['is_like'] = is_like($post->id)?1:0;
            $temp['is_commant'] = is_commant($post->id)?1:0;
            $temp['posttags'] = $tag_array;
            $temp['postmedia'] = $post->postmedia;
            $temp['host_tag_name'] = isset($post->hosttag)?$post->hosttag->full_name:"";
            $temp['user'] = $post->user;
            $temp['total_like'] = $post->total_like;
            $temp['total_commant'] = $post->total_commant;
            $temp['created_at'] = $post->created_at;
            array_push($posts_arr,$temp);
        }

        return $this->sendResponseWithData($posts_arr,"My Post Retrieved Successfully.");
    }

    public function get_all_posts(Request $request){
        $limit = isset($request->limit)?$request->limit:20;
        
        $posts = Post::with('posttags.user')->orderBy('created_at','DESC')->paginate($limit);
        $posts_arr = array();
        foreach ($posts as $post){
            $tag_array = array();
            foreach($post->posttags as $posttag){
                if($posttag->user){
                    $tag['id'] = $posttag->user->id;
                    $tag['name'] = $posttag->user->full_name;
                    array_push($tag_array,$tag);
                }    
            }
            $temp = array();
            $temp['id'] = $post->id;
            $temp['description'] = $post->description;
            $temp['is_private'] = $post->is_private;
            if(isset($request->user_id)){
               $user_id = $request->user_id;
              $temp['is_like'] = is_like($post->id,$user_id)?1:0;
            }else{
              $temp['is_like'] = 0; 
            }

            if(isset($request->user_id)){
                $user_id = $request->user_id;
                $temp['is_commant'] = is_commant($post->id,$user_id)?1:0;
            }else{
                $temp['is_commant'] = 0; 
            }
            $temp['posttags'] = $tag_array;
            $temp['postmedia'] = $post->postmedia;
            $temp['host_tag_name'] = isset($post->hosttag)?$post->hosttag->full_name:"";
            $temp['user'] = $post->user;
            $temp['total_like'] = $post->total_like;
            $temp['total_commant'] = $post->total_commant;
            $temp['created_at'] = $post->created_at;
            
            array_push($posts_arr,$temp);
        }

        return $this->sendResponseWithData($posts_arr,"My Post Retrieved Successfully.");
    }

    public function like_post(Request $request){
        $messages = [
            'post_id.required' =>'Please provide a post id',
        ];

        $validator = Validator::make($request->all(), [
            'post_id' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        $post = Post::where('id',$request->post_id)->first();
        if (!$post){
            return $this->sendError("Post Not Exist", "Not Found Error", []);
        }

        $likelist = PostLike::where('user_id',Auth::user()->id)->where('post_id',$request->post_id)->first();
        if($likelist){
            $likelist->delete();
            $action = "Unlike";
        }else{
            $likelist = new PostLike();
            $likelist->user_id = Auth::user()->id;
            $likelist->post_id = $request->post_id;
            $likelist->save();
            $action = "Like";
        }
        $total_like = 0;
        if($action == "Like"){
            $total_like = $post->total_like + 1;
        }else{
            $total_like = $post->total_like - 1;
        }
        $post->total_like = $total_like;
        $post->save();
        return $this->sendResponseWithData(['total_like' => $total_like],$action. " Post Successfully.");
    }

    public function like_post_users(Request $request){
        $postlikes = PostLike::with('user')->where('post_id',$request->post_id)->orderBy('created_at','DESC')->get();
        $postlikes_arr = array();
        foreach ($postlikes as $postlike){
            $temp = array();
            $temp['id'] = $postlike->id;
            $temp['user_id'] = $postlike->user->id;
            $temp['full_name'] = $postlike->user->full_name;
            $temp['profile_pic'] = $postlike->user->profile_pic;
            $temp['created_at'] = $postlike->created_at;
            array_push($postlikes_arr,$temp);
        }

        return $this->sendResponseWithData($postlikes_arr,"Post Like User Retrieved Successfully.");
    }


    public function commant_post(Request $request){
        $messages = [
            'post_id.required' =>'Please provide a post id',
            'commant.required' =>'Please provide a commant',
        ];

        $validator = Validator::make($request->all(), [
            'post_id' => 'required',
            'commant' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        $post = Post::where('id',$request->post_id)->first();
        if (!$post){
            return $this->sendError("Post Not Exist", "Not Found Error", []);
        }

        if($request->commant_id > 0){
            $commant = PostCommant::find($request->commant_id);
            $action = "Update";
        }else{
            $commant = new PostCommant();
            $action = "Add";
            $commant->parent_id = isset($request->parent_id)?$request->parent_id:0;
            $commant->user_id = Auth::user()->id;
            $commant->post_id = $request->post_id;
        }
        
        $commant->commant = $request->commant;
        $commant->save();
    
        if($action == "Add"){
           $total_commant = $post->total_commant + 1;
           $post->total_commant = $total_commant;
           $post->save();
        }else{
           $total_commant = $post->total_commant;
        }

        $temp = array();
        $postcommant = PostCommant::find($commant->id);
        $temp['id'] = $postcommant->id;
        $temp['user_id'] = $postcommant->user->id;
        $temp['full_name'] = $postcommant->user->full_name;
        $temp['profile_pic'] = $postcommant->user->profile_pic;
        $temp['commant'] = $postcommant->commant;
        $temp['created_at'] = $postcommant->created_at;
        $temp['total_commant'] = $total_commant;

     
        return $this->sendResponseWithData($temp,$action. " Post Commant Successfully.");
    }

    public function commant_post_users(Request $request){
        $postcommants = PostCommant::with('user')->where('post_id',$request->post_id)->where('parent_id',0)->orderBy('created_at','DESC')->get();
        $postcommants_arr = array();
        foreach ($postcommants as $postcommant){
            $temp = array();
            $temp['id'] = $postcommant->id;
            $temp['user_id'] = $postcommant->user->id;
            $temp['full_name'] = $postcommant->user->full_name;
            $temp['profile_pic'] = $postcommant->user->profile_pic;
            $temp['commant'] = $postcommant->commant;
            $temp['created_at'] = $postcommant->created_at;
            $temp['child_commant'] = $this->child_commant($request->post_id,$postcommant->id);
            array_push($postcommants_arr,$temp);
        }

        return $this->sendResponseWithData($postcommants_arr,"Post Commant User Retrieved Successfully.");
    }

    public function child_commant($post_id,$id){
        
        $postcommants = PostCommant::with('user')->where('post_id',$post_id)->where('parent_id',$id)->orderBy('created_at','DESC')->get();
        $postcommants_arr = array();
        foreach ($postcommants as $postcommant){
            $temp = array();
            $temp['id'] = $postcommant->id;
            $temp['user_id'] = $postcommant->user->id;
            $temp['full_name'] = $postcommant->user->full_name;
            $temp['profile_pic'] = $postcommant->user->profile_pic;
            $temp['commant'] = $postcommant->commant;
            $temp['created_at'] = $postcommant->created_at;
            $temp['child_commant'] = $this->child_commant($post_id,$postcommant->id);
            array_push($postcommants_arr,$temp);
        }

        return $postcommants_arr;
    }

    


}
