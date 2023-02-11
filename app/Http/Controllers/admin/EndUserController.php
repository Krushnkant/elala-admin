<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectPage;
use App\Models\User;
use App\Models\Order;
use App\Models\UserLevel;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EndUserController extends Controller
{
    private $page = "Customers";

    public function index(){
        $users = User::where('role',3)->get();
        return view('admin.end_users.list',compact('users'))->with('page',$this->page);
    }

    

    public function allEnduserlist(Request $request){
        if ($request->ajax()) {
            $tab_type = $request->tab_type;
            if ($tab_type == "active_end_user_tab"){
                $estatus = 1;
            }
            elseif ($tab_type == "deactive_end_user_tab"){
                $estatus = 2;
            }

            $columns = array(
                0=>'id',
                1=>'profile_pic',
                2=> 'contact_info',
                3=> 'login_info',
                4=> 'estatus',
                5=> 'created_at',
                6=> 'action',
            );

            $totalData = User::where('role',3)->WhereNotNull('first_name');
            if (isset($estatus)){
                $totalData = $totalData->where('estatus',$estatus);
            }
            
            $totalData = $totalData->count();

            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
//            dd($columns[$request->input('order.0.column')]);
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            if($order == "id"){
                $order = "created_at";
                $dir = 'desc';
            }

            if(empty($request->input('search.value')))
            {
                $users = User::where('role',3)->WhereNotNull('full_name');
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
                $users =  User::where('role',3)->WhereNotNull('full_name');
                if (isset($estatus)){
                    $users = $users->where('estatus',$estatus);
                }
               
                $users = $users->where(function($query) use($search){
                    $query->where('full_name','LIKE',"%{$search}%")
                        ->orWhere('email', 'LIKE',"%{$search}%")
                        ->orWhere('mobile_no', 'LIKE',"%{$search}%")
                        ->orWhere('created_at', 'LIKE',"%{$search}%");
                    })
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();

                $totalFiltered = count($users->toArray());
            }

            $data = array();
    
            if(!empty($users))
            {
                foreach ($users as $user)
                {
                    $page_id = ProjectPage::where('route_url','admin.end_users.list')->pluck('id')->first();

                    if( $user->estatus==1 && (getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id))) ){
                        $estatus = '<label class="switch"><input type="checkbox" id="EndUserstatuscheck_'. $user->id .'" onchange="changeEndUserStatus('. $user->id .')" value="1" checked="checked"><span class="slider round"></span></label>';
                    }
                    elseif ($user->estatus==1){
                        $estatus = '<label class="switch"><input type="checkbox" id="EndUserstatuscheck_'. $user->id .'" value="1" checked="checked"><span class="slider round"></span></label>';
                    }

                    if( $user->estatus==2 && (getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id))) ){
                        $estatus = '<label class="switch"><input type="checkbox" id="EndUserstatuscheck_'. $user->id .'" onchange="changeEndUserStatus('. $user->id .')" value="2"><span class="slider round"></span></label>';
                    }
                    elseif ($user->estatus==2){
                        $estatus = '<label class="switch"><input type="checkbox" id="EndUserstatuscheck_'. $user->id .'" value="2"><span class="slider round"></span></label>';
                    }

                    if(isset($user->profile_pic) && $user->profile_pic!=null){
                        $profile_pic = $user->profile_pic;
                    }
                    else{
                        $profile_pic = url('images/default_avatar.jpg');
                    }

                    $contact_info = '';
                    if (isset($user->email)){
                        $contact_info .= '<span><i class="fa fa-envelope" aria-hidden="true"></i> ' .$user->email .'</span>';
                    }
                    if (isset($user->mobile_no)){
                        $contact_info .= '<span><i class="fa fa-phone" aria-hidden="true"></i> ' .$user->mobile_no .'</span>';
                    }

                    $login_info = '';
                    
                    if (isset($user->email)){
                        $login_info .= '<span> ' .$user->email .'</span>';
                    }
                    if (isset($user->decrypted_password)){
                        $login_info .= '<span> ' .$user->decrypted_password .'</span>';
                    }

                    if(isset($user->full_name)){
                        $full_name = $user->full_name;
                    }
                    else{
                        $full_name="";
                    }

                    $action='';
                    if ( getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id)) ){
                        $action .= '<button id="editEndUserBtn" class="btn btn-gray text-blue btn-sm" data-toggle="modal" data-target="#EndUserModal" onclick="" data-id="' .$user->id. '"><i class="fa fa-pencil" aria-hidden="true"></i></button>
                        <button id="friendList" class="btn btn-gray text-black btn-sm" data-toggle="modal" data-target="#UserFriendListModal" onclick="" data-id="' .$user->id. '"><i class="fa fa-users" aria-hidden="true"></i></button>';
                    }
                    if ( getUSerRole()==1 || (getUSerRole()!=1 && is_delete($page_id)) ){
                        $action .= '<button id="deleteEndUserBtn" class="btn btn-gray text-danger btn-sm" data-toggle="modal" data-target="#DeleteEndUserModal" onclick="" data-id="' .$user->id. '"><i class="fa fa-trash-o" aria-hidden="true"></i></button>';
                    }

                    $nestedData['profile_pic'] = '<img src="'. $profile_pic .'" width="50px" height="50px" alt="Profile Pic"><span>'.$full_name.'</span>';
                    $nestedData['contact_info'] = $contact_info;
                    $nestedData['login_info'] = $login_info;
                    $nestedData['estatus'] = $estatus;
                    $nestedData['created_at'] = date('d-m-Y h:i A', strtotime($user->created_at));
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

//            return json_encode($json_data);
            echo json_encode($json_data);
        }
    }

    public function addorupdateEnduser(Request $request){
        $messages = [
            'profile_pic.image' =>'Please provide a Valid Extension Image(e.g: .jpg .png)',
            'profile_pic.mimes' =>'Please provide a Valid Extension Image(e.g: .jpg .png)',
            'full_name.required' =>'Please provide a Full Name',
            'mobile_no.required' =>'Please provide a Mobile No.',
            'dob.required' =>'Please provide a Date of Birth.',
            'email.required' =>'Please provide a valid E-mail address.',
            'password.required' =>'Please provide a Password.',
            'bio.required' =>'Please provide a Bio.',
        ];

        if ($request->is_premium == 1){
            if(isset($request->action) && $request->action=="update"){
                $validator = Validator::make($request->all(), [
                    'profile_pic' => 'image|mimes:jpeg,png,jpg',
                    'adhar_front' => 'image|mimes:jpeg,png,jpg',
                    'adhar_back' => 'image|mimes:jpeg,png,jpg',
                    'full_name' => 'required',
                    //'mobile_no' => 'required|numeric|digits:10',
                    'adhar_card_no' => 'required|numeric|digits:12',
                    'dob' => 'required',
                
                    'email' => ['required', 'string', 'email', 'max:191',Rule::unique('users')->where(function ($query) use ($request) {
                        return $query->where('role', 3)->where('id','!=',$request->user_id)->where('estatus','!=',3);
                    })],
                    'mobile_no' => ['required', 'numeric', 'digits:10',Rule::unique('users')->where(function ($query) use ($request) {
                        return $query->where('role', 3)->where('id','!=',$request->user_id)->where('estatus','!=',3);
                    })],
                    'password' => 'required',
                    'bio' => 'required',
                ], $messages);
          }else{
                $validator = Validator::make($request->all(), [
                    'profile_pic' => 'image|mimes:jpeg,png,jpg',
                    'adhar_front' => 'required|image|mimes:jpeg,png,jpg',
                    'adhar_back' => 'required|image|mimes:jpeg,png,jpg',
                    'full_name' => 'required',
                    //'mobile_no' => 'required|numeric|digits:10',
                    'adhar_card_no' => 'required|numeric|digits:12',
                    'dob' => 'required',
                
                    'email' => ['required', 'string', 'email', 'max:191',Rule::unique('users')->where(function ($query) use ($request) {
                        return $query->where('role', 3)->where('id','!=',$request->user_id)->where('estatus','!=',3);
                    })],
                    'mobile_no' => ['required', 'numeric', 'digits:10',Rule::unique('users')->where(function ($query) use ($request) {
                        return $query->where('role', 3)->where('id','!=',$request->user_id)->where('estatus','!=',3);
                    })],
                    'password' => 'required',
                    'bio' => 'required',
                ], $messages);

          }
        }
        else{
            $validator = Validator::make($request->all(), [
                'profile_pic' => 'image|mimes:jpeg,png,jpg',
                'full_name' => 'required',
                'mobile_no' => ['required', 'numeric', 'digits:10',Rule::unique('users')->where(function ($query) use ($request) {
                    return $query->where('role', 3)->where('id','!=',$request->user_id)->where('estatus','!=',3);
                })],
                'dob' => 'required',
                'bio' => 'required',
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
            $user->gender = $request->gender;
            $user->dob = $request->dob;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->decrypted_password = $request->password;
            $user->bio = $request->bio;
                 
        }
        else{
            $action = "add";
            $user = new User();
          
            $user->full_name = $request->full_name;
            $user->mobile_no = $request->mobile_no;
            $user->gender = $request->gender;
            $user->dob = $request->dob;
            $user->role = 3;
            $user->bio = $request->bio;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->decrypted_password = $request->password;
            $user->is_verify = 1;
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
       
        return response()->json(['status' => '200', 'action' => $action]);
    }

    public function editEnduser($id){
        $user = User::find($id);
        return response()->json($user);
    }

    public function deleteEnduser($id){
        $user = User::find($id);
        if ($user){
            $user->estatus = 3;
            $user->save();

            $user->delete();
            return response()->json(['status' => '200']);
        }
        return response()->json(['status' => '400']);
    }

    public function changeEnduserstatus($id){
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

    public function levelEnduser($userid){
        return view('admin.end_users.levellist',compact('userid'))->with('page',$this->page);
    }

 

    public function viewClieldUser($id)
    {
        $user = User::where('role',3)->where('id',$id)->first();
        $users = User::where('role',3)->where('parent_user_id',$id)->get();
        return view('admin.end_users.user_list',compact('id','users','user'))->with('page',$this->page);
    }

    public function ParentWiseUsers(Request $request, $id, $level,$parent_id=0)
    {
        
        if ($request->ajax()) {
            // dump($id); // 2 user
            $columns = array(
                0 =>'id',
                1 =>'name',
                2=> 'parent_name',
                3=> 'commission',
                // 4=> 'level_1',
                // 5=> 'level_2',
                // 6=> 'level_3',
                4=> 'action',
            );

            $now = Carbon::now();
            // $month = $now->month;
            $years = $now->year;
            $auth = User::where('id', $id)->first();
            
            if($level === "1"){
                $getUserId = User::where('parent_user_id', $id)->where('role',3)->get()->pluck('id')->toArray(); // child (3, 4)
                $totalDataa = User::where('parent_user_id', $id)->where('role',3); //user (57)
                
            }elseif($level === "2"){
                $first_level = User::where('parent_user_id', $id)->where('role',3)->where('estatus', 1)->get()->pluck('id')->toArray();
                $second_level = User::whereIn('parent_user_id', $first_level)->where('role',3)->where('estatus', 1)->get()->pluck('id')->toArray();
                $totalDataa = User::whereIn('parent_user_id', $first_level)->where('role',3); //user (57)
                
            }elseif($level === "3"){
                $first_level = User::where('parent_user_id', $id)->where('role',3)->where('estatus', 1)->get()->pluck('id')->toArray();
                $second_level = User::whereIn('parent_user_id', $first_level)->where('role',3)->where('estatus', 1)->get()->pluck('id')->toArray();
                $third_level = User::whereIn('parent_user_id', $second_level)->where('role',3)->where('estatus', 1)->get()->pluck('id')->toArray();
                $totalDataa = User::whereIn('parent_user_id', $second_level)->where('role',3); //user (57)

            }   
            if (isset($estatus)){
                $totalData = $totalData->where('estatus',$estatus);
            }

            if (isset($parent_id) && $parent_id != 0){
                $totalData = $totalDataa->WhereHas('parent', function ($query) use($parent_id){
                    $query->where('id',$parent_id);
                });
            }
            $totalData = $totalDataa->count();
            $totalFiltered = $totalData;

            $limit = (int)$request->input('length');
            $start = (int)$request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            if($order == "id"){
                $order = "created_at";
                $dir = 'desc';
            }

            if(empty($request->input('search.value')))
            {
              
                if (isset($estatus)){
                    $totalDataa = $totalDataa->where('estatus',$estatus);
                }
                if (isset($parent_id) && $parent_id != 0){
                    $totalDataa = $totalDataa->WhereHas('parent', function ($query) use($parent_id){
                        $query->where('id',$parent_id);
                    });
                }
                $users = $totalDataa->with('orders','parent');
                $users = $users->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();
                   
            }
            else {
                $search = $request->input('search.value');
                $users =  $totalDataa->with('orders','parent');
                if (isset($estatus)){
                    $users = $users->where('estatus',$estatus);
                }
                if (isset($parent_id) && $parent_id != 0){
                    $totalDataa = $users->orWhereHas('parent', function ($query) use($parent_id){
                        $query->where('id',$parent_id);
                    });
                }
                $users = $users->where(function($query) use($search){
                      $query->where('id','LIKE',"%{$search}%")
                            ->orWhereHas('parent', function ($query) use($search){
                                $query->where('full_name', 'LIKE',"%{$search}%");
                            })
                            ->orWhere('full_name', 'LIKE',"%{$search}%")
                            ->orWhere('premiumuserid','LIKE',"%{$search}%")
                            ->orWhere('email', 'LIKE',"%{$search}%")
                            ->orWhere('mobile_no', 'LIKE',"%{$search}%")
                            ->orWhere('password', 'LIKE',"%{$search}%")
                            ->orWhere('created_at', 'LIKE',"%{$search}%");
                      })
                      ->offset($start)
                      ->limit($limit)
                      ->orderBy($order,$dir)
                      ->get();
                $totalFiltered = User::where('role',2);
                if (isset($estatus)){
                    $totalFiltered = $totalFiltered->where('estatus',$estatus);
                }
                $totalFiltered = $totalFiltered->where(function($query) use($search){
                        $query->where('id','LIKE',"%{$search}%")
                            ->orWhere('full_name', 'LIKE',"%{$search}%")
                            ->orWhere('premiumuserid','LIKE',"%{$search}%")
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
                foreach ($users as $user)
                {
                    if(isset($user->full_name)){
                        $full_name = $user->full_name . " [ ".$user->premiumuserid." ]";
                    }
                    else{
                        $full_name="";
                    }
                  
                    $nestedData['name'] = $full_name;
                    $nestedData['parent_name'] = $user->parent->full_name . " [ ".$user->parent->premiumuserid." ]";
                    $nestedData['register_date'] = date('d-m-Y h:i A', strtotime($user->created_at));
                    ///$nestedData['action'] = $action;
                    $data[] = $nestedData;

                }
                // dd();
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

    public function getState(Request $request)
    {
        $data['states'] = User::where("parent_user_id",$request->country_id)
                    ->get(["full_name","id"]);
        return response()->json($data);
    }


}
