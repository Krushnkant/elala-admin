<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectPage;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\UserPermission;
use App\Models\DesignationPermission;
use App\Models\Designation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    private $page = "Users";

    public function index(){
        $designations = Designation::where('estatus',1)->get();
        return view('admin.users.list',compact('designations'))->with('page',$this->page);
    }

    public function addorupdateuser(Request $request){
        $messages = [
            'profile_pic.image' =>'Please provide a Valid Extension Image(e.g: .jpg .png)',
            'profile_pic.mimes' =>'Please provide a Valid Extension Image(e.g: .jpg .png)',
            'full_name.required' =>'Please provide a FullName',
            'mobile_no.required' =>'Please provide a Mobile No.',
            'email.required' =>'Please provide a Email Address.',
            'password.required' =>'Please provide a Password.',
            'dob.required' =>'Please provide a Date of Birth.',
            'designation_id.required' =>'Please Select Designation.',
        ];

        if (isset($request->action) && $request->action=="update"){
            $validator = Validator::make($request->all(), [
                'profile_pic' => 'image|mimes:jpeg,png,jpg',
                'full_name' => 'required',
                'mobile_no' => 'required|numeric|digits:10',
                'email' => 'required|email',
                'password' => 'required',
                'dob' => 'required',
                'designation_id' => 'required',
            ], $messages);
        }
        else{
            $validator = Validator::make($request->all(), [
                'profile_pic' => 'image|mimes:jpeg,png,jpg',
                'full_name' => 'required',
                'mobile_no' => 'required|numeric|digits:10',
                'email' => 'required|email|unique:users',
                'password' => 'required',
                'designation_id' => 'required',
            ], $messages);
        }

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(),'status'=>'failed']);
        }

        if(isset($request->action) && $request->action=="update"){
            $action = "update";
            $user = User::find($request->user_id);

            if(!$user){
                return response()->json(['status' => '400']);
            }

            $old_image = $user->profile_pic;
            $image_name = $old_image;
            $user->full_name = $request->full_name;
            $user->mobile_no = $request->mobile_no;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->decrypted_password = $request->password;
            $user->gender = $request->gender;
            $user->dob = $request->dob;
            $user->designation_id = $request->designation_id;
        }
        else{
            $action = "add";
            $user = new User();
            $user->full_name = $request->full_name;
            $user->mobile_no = $request->mobile_no;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->decrypted_password = $request->password;
            $user->gender = $request->gender;
            $user->dob = $request->dob;
            $user->role = 2;
            $user->designation_id = $request->designation_id;
            $user->created_at = new \DateTime(null, new \DateTimeZone('Asia/Kolkata'));
            $image_name=null;
        }

        if ($request->hasFile('profile_pic')) {
            $image = $request->file('profile_pic');
            $image_name = 'profilePic_' . rand(111111, 999999) . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('images/profile_pic');
            $image->move($destinationPath, $image_name);
            if(isset($old_image)) {
                $old_image = public_path('images/profile_pic/' . $old_image);
                if (file_exists($old_image)) {
                    unlink($old_image);
                }
            }
            $user->profile_pic = $image_name;
        }

        $user->save();

        if ($action=='add'){
            $designation_permissions =  DesignationPermission::where('designation_id',$request->designation_id)->get();
            if(count($designation_permissions) > 0){
                foreach ($designation_permissions as $designation_permission){
                    $user_permission = new UserPermission();
                    $user_permission->user_id = $user->id;
                    $user_permission->project_page_id = $designation_permission->project_page_id;
                    $user_permission->can_read = $designation_permission->can_read;
                    $user_permission->can_write = $designation_permission->can_write;
                    $user_permission->can_delete = $designation_permission->can_delete;
                    $user_permission->save();
                }
            }else{
                $project_page_ids1 = ProjectPage::where('parent_menu',0)->where('is_display_in_menu',0)->pluck('id')->toArray();
                $project_page_ids2 = ProjectPage::where('parent_menu',"!=",0)->where('is_display_in_menu',1)->pluck('id')->toArray();
                $project_page_ids = array_merge($project_page_ids1,$project_page_ids2);
                foreach ($project_page_ids as $pid) {
                    $user_permission = new UserPermission();
                    $user_permission->user_id = $user->id;
                    $user_permission->project_page_id = $pid;
                    $user_permission->save();
                }
            }
        }

        return response()->json(['status' => '200', 'action' => $action]);
    }

    public function alluserslist(Request $request){
        if ($request->ajax()) {
            $tab_type = $request->tab_type;
            if ($tab_type == "active_user_tab"){
                $estatus = 1;
            }
            elseif ($tab_type == "deactive_user_tab"){
                $estatus = 2;
            }

            $columns = array(
                0 =>'id',
                1 =>'profile_pic',
                2=> 'user_info',
                3=> 'contact_info',
                4=> 'login_info',
                5=> 'estatus',
                6=> 'created_at',
                7=> 'action',
            );

            $totalData = User::where('role',2);
            if (isset($estatus)){
                $totalData = $totalData->where('estatus',$estatus);
            }
            $totalData = $totalData->count();

            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');

         
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            if($order == "id"){
                $order == "created_at";
                $dir = 'desc';
            }

            if(empty($request->input('search.value')))
            {
                $users = User::with('designation')->where('role',2)->where('id', '<>',Auth::id());
                if (isset($estatus)){
                    $users = $users->where('estatus',$estatus);
                }
                $users = $users->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();
            }
            else {
                $search = $request->input('search.value');
                $users = User::with('designation')->where('role',2)->where('id', '<>',Auth::id());
                if (isset($estatus)){
                    $users = $users->where('estatus',$estatus);
                }
                $users = $users->where(function($query) use($search){
                      $query->where('id','LIKE',"%{$search}%")
                            ->orWhere('email', 'LIKE',"%{$search}%")
                            ->orWhere('mobile_no', 'LIKE',"%{$search}%")
                            ->orWhere('password', 'LIKE',"%{$search}%")
                            ->orWhere('created_at', 'LIKE',"%{$search}%");
                      })
                      ->offset($start)
                      ->limit($limit)
                      ->orderBy($order,$dir)
                      ->get();

                $totalFiltered = User::with('designation')->where('role',2)->where('id', '<>',Auth::id());
                if (isset($estatus)){
                    $totalFiltered = $totalFiltered->where('estatus',$estatus);
                }
                $totalFiltered = $totalFiltered->where(function($query) use($search){
                        $query->where('id','LIKE',"%{$search}%")
                            ->orWhere('email', 'LIKE',"%{$search}%")
                            ->orWhere('mobile_no', 'LIKE',"%{$search}%")
                            ->orWhere('password', 'LIKE',"%{$search}%")
                            ->orWhere('created_at', 'LIKE',"%{$search}%");
                        })
                        ->count();
            }

            $data = array();

            if(!empty($users))
            {
//                $i=1;
                foreach ($users as $user)
                {
                    $page_id = ProjectPage::where('route_url','admin.users.list')->pluck('id')->first();

                    if( $user->estatus==1 && (getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id))) ){
                        $estatus = '<label class="switch"><input type="checkbox" id="Userstatuscheck_'. $user->id .'" onchange="changeUserStatus('. $user->id .')" value="1" checked="checked"><span class="slider round"></span></label>';
                    }
                    elseif ($user->estatus==1){
                        $estatus = '<label class="switch"><input type="checkbox" id="Userstatuscheck_'. $user->id .'" value="1" checked="checked"><span class="slider round"></span></label>';
                    }

                    if( $user->estatus==2 && (getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id))) ){
                        $estatus = '<label class="switch"><input type="checkbox" id="Userstatuscheck_'. $user->id .'" onchange="changeUserStatus('. $user->id .')" value="2"><span class="slider round"></span></label>';
                    }
                    elseif ($user->estatus==2){
                        $estatus = '<label class="switch"><input type="checkbox" id="Userstatuscheck_'. $user->id .'" value="2"><span class="slider round"></span></label>';
                    }

                    if(isset($user->profile_pic) && $user->profile_pic!=null){
                        $profile_pic = $user->profile_pic;
                    }
                    else{
                        $profile_pic = url('images/default_avatar.jpg');
                    }

                    if(isset($user->full_name)){
                        $full_name = $user->full_name;
                    }
                    else{
                        $full_name="";
                    }

                    $user_info = '';
                    if (isset($full_name)){
                        $user_info = '<span> ' .$full_name .'</span>';
                    }
                    if (isset($user->designation)){
                        $user_info .= '<span> ' .$user->designation->title .'</span>';
                    }

                    $contact_info = '';
                    if (isset($user->email)){
                        $contact_info = '<span><i class="fa fa-envelope" aria-hidden="true"></i> ' .$user->email .'</span>';
                    }
                    if (isset($user->mobile_no)){
                        $contact_info .= '<span><i class="fa fa-phone" aria-hidden="true"></i> ' .$user->mobile_no .'</span>';
                    }

                    $login_info = '';
                    if (isset($user->email)){
                        $login_info = '<span>' .$user->email .'</span>';
                    }
                    if (isset($user->password)){
                        $login_info .= '<span>' .$user->decrypted_password .'</span>';
                    }

                   

                    $action='';
                    if ( getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id)) ){
                        $action .= '<button id="permissionUserBtn" class="btn btn-gray text-pink btn-sm" onclick="" data-id="' .$user->id. '"><i class="fa fa-unlock-alt" aria-hidden="true"></i></button>
                                    <button id="editUserBtn" class="btn btn-gray text-blue btn-sm" data-toggle="modal" data-target="#UserModal" onclick="" data-id="' .$user->id. '"><i class="fa fa-pencil" aria-hidden="true"></i></button>
                                    <button id="friendList" class="btn btn-gray text-black btn-sm" data-toggle="modal" data-target="#UserFriendListModal" onclick="" data-id="' .$user->id. '"><i class="fa fa-users" aria-hidden="true"></i></button>';
                    }
                    if ( getUSerRole()==1 || (getUSerRole()!=1 && is_delete($page_id)) ){
                        $action .= '<button id="deleteUserBtn" class="btn btn-gray text-danger btn-sm" data-toggle="modal" data-target="#DeleteUserModal" onclick="" data-id="' .$user->id. '"><i class="fa fa-trash-o" aria-hidden="true"></i></button>';
                    }

//                    $nestedData['id'] = $i;
                    $nestedData['profile_pic'] = '<img src="'. $profile_pic .'" width="50px" height="50px" alt="Profile Pic">';
                    $nestedData['user_info'] = $user_info;
                    $nestedData['contact_info'] = $contact_info;
                    $nestedData['login_info'] = $login_info;
                    $nestedData['estatus'] = $estatus;
                    $nestedData['created_at'] = date('Y-m-d H:i A', strtotime($user->created_at));
                    $nestedData['action'] = $action;
                    $data[] = $nestedData;
//                    $i=$i+1;
                }
            }

            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $data,
            );

//            return json_encode($json_data);
            echo json_encode($json_data);
        }
    }

    public function changeuserstatus($id){
        $user = User::find($id);
        if ($user->estatus==1){
            $user->estatus = 2;
            $user->save();
            return response()->json(['status' => '200','action' =>'deactive']);
        }
        if ($user->estatus==2){
            $user->estatus = 1;
            $user->save();
            return response()->json(['status' => '200','action' =>'active']);
        }
    }

    public function edituser($id){
        $user = User::find($id);
        return response()->json($user);
    }

    public function deleteuser($id){
        $user = User::find($id);
        if ($user){
            $image = $user->profile_pic;
            $user->estatus = 3;
            $user->save();

            $user->delete();

            $image = public_path('images/profile_pic/' . $image);
            if (file_exists($image)) {
                unlink($image);
            }
            return response()->json(['status' => '200']);
        }
        return response()->json(['status' => '400']);
    }

    public function permissionuser($id){
        $page = "User Permission";
        $user_permissions = UserPermission::where('user_id',$id)->orderBy('project_page_id','asc')->get();
        return view('admin.users.permission',compact('user_permissions'))->with('page',$page);
    }

    public function savepermission(Request $request){
        foreach ($request->permissionData as $pdata) {
            $user_permission = UserPermission::where('user_id',$request->user_id)->where('project_page_id',$pdata['page_id'])->first();
            $user_permission->can_read = $pdata['can_read'];
            $user_permission->can_write = $pdata['can_write'];
            $user_permission->can_delete = $pdata['can_delete'];
            $user_permission->save();
        }

        return response()->json(['status' => '200']);
    }

    public function loginlog(){
        return view('admin.users.loginlog')->with('page',$this->page);
    }

    public function allloginloglist(Request $request){
        if ($request->ajax()) {
            $tab_type = $request->tab_type;
            if ($tab_type == "active_user_tab"){
                $estatus = 1;
            }
            elseif ($tab_type == "deactive_user_tab"){
                $estatus = 2;
            }

            $columns = array(
                0 =>'id',
                1 =>'profile_pic',
                2=> 'user_info',
                3=> 'contact_info',
                4=> 'created_at',
            );

            $totalData = UserLogin::count();

            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');

         
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            if($order == "id"){
                $order == "created_at";
                $dir = 'desc';
            }

            if(empty($request->input('search.value')))
            {
                $users = UserLogin::with('user')->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();
            }
            else {
                $search = $request->input('search.value');
                $users = UserLogin::with('user')->where(function($query) use($search){
                      $query->where('id','LIKE',"%{$search}%")
                            ->orWhere('created_at', 'LIKE',"%{$search}%");
                      })
                      ->offset($start)
                      ->limit($limit)
                      ->orderBy($order,$dir)
                      ->get();

                $totalFiltered = UserLogin::with('user')->where(function($query) use($search){
                        $query->where('id','LIKE',"%{$search}%")
                            ->orWhere('created_at', 'LIKE',"%{$search}%");
                        })
                        ->count();
            }

            $data = array();

            if(!empty($users))
            {
//                $i=1;
                foreach ($users as $userlog)
                {
                    $page_id = ProjectPage::where('route_url','admin.loginlog.list')->pluck('id')->first();
                
                    if(isset($userlog->user->profile_pic) && $userlog->user->profile_pic!=null){
                        $profile_pic = $userlog->user->profile_pic;
                    }
                    else{
                        $profile_pic = url('images/default_avatar.jpg');
                    }

                    if(isset($userlog->user->full_name)){
                        $full_name = $userlog->user->full_name;
                    }
                    else{
                        $full_name="";
                    }

                    $user_info = '';
                    if (isset($full_name)){
                        $user_info = '<span> ' .$full_name .'</span>';
                    }
                    if (isset($userlog->user->designation)){
                        $user_info .= '<span> ' .$userlog->user->designation->title .'</span>';
                    }

                    $contact_info = '';
                    if (isset($userlog->user->email)){
                        $contact_info = '<span><i class="fa fa-envelope" aria-hidden="true"></i> ' .$userlog->user->email .'</span>';
                    }
                    if (isset($userlog->user->mobile_no)){
                        $contact_info .= '<span><i class="fa fa-phone" aria-hidden="true"></i> ' .$userlog->user->mobile_no .'</span>';
                    }

                    $login_info = '';
                    if (isset($userlog->user->email)){
                        $login_info = '<span>' .$userlog->user->email .'</span>';
                    }
                    if (isset($userlog->user->password)){
                        $login_info .= '<span>' .$userlog->user->decrypted_password .'</span>';
                    }
                    

//                    $nestedData['id'] = $i;
                    $nestedData['profile_pic'] = '<img src="'. $profile_pic .'" width="50px" height="50px" alt="Profile Pic">';
                    $nestedData['user_info'] = $user_info;
                    $nestedData['contact_info'] = $contact_info;
                    $nestedData['created_at'] = date('Y-m-d H:i A', strtotime($userlog->created_at));
                    $data[] = $nestedData;
//                    $i=$i+1;
                }
            }

            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $data,
            );

//            return json_encode($json_data);
            echo json_encode($json_data);
        }
    }
}
