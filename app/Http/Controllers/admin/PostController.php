<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostTag;
use App\Models\PostLike;
use App\Models\PostCommant;
use App\Models\PostMedia;
use App\Models\User;
use App\Models\ProjectPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    private $page = "Post";

    public function index($id=0){
        $action = "list";
        $posts = Post::where('estatus',1)->get();
       
        return view('admin.posts.list',compact('action','posts','id'))->with('page',$this->page);
    }

    public function create(){
        $action = "create";
        $posts = Post::where('estatus',1)->get()->toArray();
        $sr_no = Post::where('estatus',1)->orderBy('sr_no','desc')->pluck('sr_no')->first();
        return view('admin.posts.list',compact('action','posts','sr_no'))->with('page',$this->page);
    }

    public function save(Request $request){
        $messages = [
            'description.required' =>'Please provide a POST Name',
            'catImg.required' =>'Please provide a Post Image',
        ];

        if(isset($request->action) && $request->action=="update"){
            $validator = Validator::make($request->all(), [
                'description' => 'required',
                'catImg' => 'required',
            ], $messages);
        }
        else{
            $validator = Validator::make($request->all(), [
                'description' => 'required',
                'catImg' => 'required',
            ], $messages);
        }

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(),'status'=>'failed']);
        }
      
        
            $action = "update";
            $post = Post::find($request->post_id);

            if(!$post){
                return response()->json(['status' => '400']);
            }

            // if ($post->post_thumb != $request->catImg){
            //     if(isset($post->post_thumb)) {
            //         $image = public_path($post->post_thumb);
            //         if (file_exists($image)) {
            //             unlink($image);
            //         }
            //     }
            //     $post->post_thumb = $request->catImg;

            // }

            $post->description = $request->description;
            $post->is_private = isset($request->is_private)?1:0;
            $post->host_tag = $request->host_tag;
            
        

        $post->save();
         
        if($post){
            
            foreach($request->tag_friends as $tag_friend){
               $PostTag = PostTag::where('user_id',$tag_friend)->where('post_id',$post->id)->get();
               foreach($PostTag as $Postt){
                    if(in_array($Postt->user_id,$request->tag_friends)){
                      
                        
                    }else{
                        $tag = PostTag::find($Postt->id);
                        $tag->delete();
                    }
                }
            
                if(count($PostTag) <= 0){
                    $tag = New PostTag();
                    $tag->post_id = $post->id;
                    $tag->user_id = $tag_friend;
                    $tag->save();
                }

            }
        }
        return response()->json(['status' => '200', 'action' => $action]);
    }

    public function allpostlist(Request $request){
        if ($request->ajax()) {
            $columns = array(
                0 =>'sr_no',
                1 =>'post_thumb',
                2 => 'user_name',
                3 => 'description',
                4 => 'host_tag',
                5 => 'privacy',
                6 => 'activity',
                7 => 'estatus',
                8 => 'created_at',
                9 => 'action',
            );
            $totalData = Post::with('postuser','hosttag','postimage')->count();
            
            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            if($order == "sr_no"){
                $order = "created_at";
                $dir = 'desc';
            }

            if(empty($request->input('search.value')))
            {
                $posts = Post::with('postuser','hosttag','postimage')->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();
              
            }
            else {
                $search = $request->input('search.value');
                $posts =  Post::with('postuser','hosttag','postimage')->Where('description', 'LIKE',"%{$search}%")
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();

                $totalFiltered = Post::with('postuser','hosttag','postimage')->Where('description', 'LIKE',"%{$search}%")
                    ->count();
          
            }

            $data = array();

            if(!empty($posts))
            {
                foreach ($posts as $post)
                {
                    $page_id = ProjectPage::where('route_url','admin.posts.list')->pluck('id')->first();

                    if( $post->estatus==1 && (getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id))) ){
                        $estatus = '<label class="switch"><input type="checkbox" id="postStatuscheck_'. $post->id .'" onchange="chagepostStatus('. $post->id .')" value="1" checked="checked"><span class="slider round"></span></label>';
                    }
                    elseif ($post->estatus==1){
                        $estatus = '<label class="switch"><input type="checkbox" id="postStatuscheck_'. $post->id .'" value="1" checked="checked"><span class="slider round"></span></label>';
                    }

                    if( $post->estatus==2 && (getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id))) ){
                        $estatus = '<label class="switch"><input type="checkbox" id="postStatuscheck_'. $post->id .'" onchange="chagepostStatus('. $post->id .')" value="2"><span class="slider round"></span></label>';
                    }
                    elseif ($post->estatus==2){
                        $estatus = '<label class="switch"><input type="checkbox" id="postStatuscheck_'. $post->id .'" value="2"><span class="slider round"></span></label>';
                    }

                    $thumb_path = url('images/placeholder_image.png');
                    if(isset($post->postimage->name) && $post->postimage->name!=null){
                        $thumb_path = url($post->postimage->name);
                    }

                    if(isset($post->user->full_name)){
                        $user_full_name = $post->user->full_name;
                    }else{
                        $user_full_name="";
                    }
              
                    if(isset($post->hosttag->full_name)){
                        $host_tag_full_name = $post->hosttag->full_name;
                    }else{
                        $host_tag_full_name="";
                    }

                    if($post->is_private==1){
                        $privacy = '<label class="">Private</label>';
                    }else{
                        $privacy = '<label class="">Public</label>';
                    }

                    $activity = '';
                    if (isset($post->total_like)){
                        $activity = '<span class="mr-2"><i class="fa fa-thumbs-up" aria-hidden="true"></i> ' .$post->total_like .'</span>';
                    }
                    if (isset($post->total_commant)){
                        $activity .= '<span><i class="fa fa-comment" aria-hidden="true"></i> ' .$post->total_commant .'</span>';
                    }

                    $action='';
                    if ( getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id)) ){
                        $action .= '<button title="Edit" id="editpostBtn" class="btn btn-gray text-blue btn-sm" data-id="' .$post->id. '"><i class="fa fa-pencil" aria-hidden="true"></i></button>';
                    }
                    if ( getUSerRole()==1 || (getUSerRole()!=1 && is_delete($page_id)) ){
                        $action .= '<button title="Delete" id="deletepostBtn" class="btn btn-gray text-danger btn-sm" data-toggle="modal" data-target="#DeletepostModal" onclick="" data-id="' .$post->id. '"><i class="fa fa-trash-o" aria-hidden="true"></i></button>';
                    }
                    
                    $nestedData['user_name'] = $user_full_name;
                    $nestedData['post_thumb'] = '<img src="'. $thumb_path .'" width="50px" height="50px" alt="Thumbnail">';
                    $nestedData['description'] = $post->description;
                    $nestedData['privacy'] = $privacy;
                    $nestedData['host_tag'] = $host_tag_full_name;
                    $nestedData['activity'] = $activity;
                    $nestedData['estatus'] = $estatus;
                    $nestedData['created_at'] = date('d-m-Y h:i A', strtotime($post->created_at));
                    $nestedData['action'] = $action;
                    $data[] = $nestedData;
                }
            }

            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $data,
            );
            echo json_encode($json_data);
        }
    }

    public function changepoststatus($id){
        $post = Post::find($id);
        if ($post->estatus==1){
            $post->estatus = 2;
            $post->save();
            return response()->json(['status' => '200','action' =>'deactive']);
        }
        if ($post->estatus==2){
            $post->estatus = 1;
            $post->save();
            return response()->json(['status' => '200','action' =>'active']);
        }
    }

    public function deletepost($id){
        $post = Post::find($id);
        if ($post){

            $postcommant = PostCommant::where('post_id',$id)->delete();
            $postlike = PostLike::where('post_id',$id)->delete();
            
            $posttag = PostTag::where('post_id',$id)->delete();

            $postmedia = PostMedia::where('post_id',$id)->get();
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

            
            return response()->json(['status' => '200']);
        }
        return response()->json(['status' => '400']);
    }

    public function editpost($id){
        $action = "edit";
        $post = Post::with('postmedia')->find($id);
        $users = User::where('role',3)->where('is_completed',1)->get(['id','full_name','profile_pic'])->toArray();
        return view('admin.posts.list',compact('action','post','users'))->with('page',$this->page);
    }

    public function uploadfile(Request $request){
        if(isset($request->action) && $request->action == 'uploadPostIcon'){
            if ($request->hasFile('files')) {
                $image = $request->file('files')[0];
                $image_name = 'post_media_' . rand(111111, 999999) . time() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('images/post_media');
                $image->move($destinationPath, $image_name);
                return response()->json(['data' => 'images/post_media/'.$image_name]);
            }
        }
    }

    public function removefile(Request $request){
        if(isset($request->action) && $request->action == 'removeCatIcon'){
            $image = $request->file;
            if(isset($image)) {
                $image = public_path($request->file);
                if (file_exists($image)) {
                    unlink($image);
                    return response()->json(['status' => '200']);
                }
            }
        }
    }

    


}
