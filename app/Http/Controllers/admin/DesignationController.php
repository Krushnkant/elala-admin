<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProjectPage;
use App\Models\Designation;
use App\Models\DesignationPermission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class DesignationController extends Controller
{
    private $page = "Designation";
    public function index(){
        return view('admin.designation.list')->with('page',$this->page);
    }

    public function addorupdatedesignation(Request $request){
        $messages = [
            'title.required' =>'Please provide a Title.',
        ];
        $validator = Validator::make($request->all(), [
            'title' => 'required',
        ], $messages);
      
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(),'status'=>'failed']);
        }

        if(isset($request->action) && $request->action=="update"){
            $action = "update";
            $designation = Designation::find($request->designation_id);

            if(!$designation){
                return response()->json(['status' => '400']);
            }

            
            $designation->title = $request->title;
        }
        else{
            $action = "add";
            $designation = new Designation();
            $designation->title = $request->title;
        }

        $designation->save();

        if ($action=='add'){
            $project_page_ids1 = ProjectPage::where('parent_menu',0)->where('is_display_in_menu',0)->pluck('id')->toArray();
            $project_page_ids2 = ProjectPage::where('parent_menu',"!=",0)->where('is_display_in_menu',1)->pluck('id')->toArray();
            $project_page_ids = array_merge($project_page_ids1,$project_page_ids2);
            foreach ($project_page_ids as $pid) {
                $designation_permission = new DesignationPermission();
                $designation_permission->designation_id = $designation->id;
                $designation_permission->project_page_id = $pid;
                $designation_permission->save();
            }
        }

        return response()->json(['status' => '200', 'action' => $action]);
    }

    public function alldesignationslist(Request $request){
        if ($request->ajax()) {
           

            $columns = array(
                0 =>'id',
                1=> 'title',
                2=> 'estatus',
                3=> 'created_at',
                4=> 'action',
            );

            $totalData = Designation::count();

            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
//            dd($columns[$request->input('order.0.column')]);
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            if($order == "id"){
                $order == "created_at";
                $dir = 'desc';
            }

            if(empty($request->input('search.value')))
            {
                $designations = Designation::offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();
            }
            else {
                $search = $request->input('search.value');
                $designations =  Designation::where(function($query) use($search){
                      $query->where('id','LIKE',"%{$search}%")
                            ->orWhere('title', 'LIKE',"%{$search}%")
                     
                            ->orWhere('created_at', 'LIKE',"%{$search}%");
                      })
                      ->offset($start)
                      ->limit($limit)
                      ->orderBy($order,$dir)
                      ->get();

                $totalFiltered = Designation::where(function($query) use($search){
                        $query->where('id','LIKE',"%{$search}%")
                            ->orWhere('title', 'LIKE',"%{$search}%")
                            ->orWhere('created_at', 'LIKE',"%{$search}%");
                        })
                        ->count();
            }

            $data = array();

            if(!empty($designations))
            {
//                $i=1;
                foreach ($designations as $designation)
                {
                    $page_id = ProjectPage::where('route_url','admin.designations.list')->pluck('id')->first();

                    if( $designation->estatus==1 && (getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id))) ){
                        $estatus = '<label class="switch"><input type="checkbox" id="Designationstatuscheck_'. $designation->id .'" onchange="changeDesignationStatus('. $designation->id .')" value="1" checked="checked"><span class="slider round"></span></label>';
                    }
                    elseif ($designation->estatus==1){
                        $estatus = '<label class="switch"><input type="checkbox" id="Designationstatuscheck_'. $designation->id .'" value="1" checked="checked"><span class="slider round"></span></label>';
                    }

                    if( $designation->estatus==2 && (getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id))) ){
                        $estatus = '<label class="switch"><input type="checkbox" id="Designationstatuscheck_'. $designation->id .'" onchange="changeDesignationStatus('. $designation->id .')" value="2"><span class="slider round"></span></label>';
                    }
                    elseif ($designation->estatus==2){
                        $estatus = '<label class="switch"><input type="checkbox" id="Designationstatuscheck_'. $designation->id .'" value="2"><span class="slider round"></span></label>';
                    }

                    $action='';
                    if ( getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id)) ){
                        $action .= '<button id="permissionDesignationBtn" class="btn btn-gray text-pink btn-sm" onclick="" data-id="' .$designation->id. '"><i class="fa fa-unlock-alt" aria-hidden="true"></i></button>
                                    <button id="editDesignationBtn" class="btn btn-gray text-blue btn-sm" data-toggle="modal" data-target="#DesignationModal" onclick="" data-id="' .$designation->id. '"><i class="fa fa-pencil" aria-hidden="true"></i></button>';
                    }
                    if ( getUSerRole()==1 || (getUSerRole()!=1 && is_delete($page_id)) ){
                        $action .= '<button id="deleteDesignationBtn" class="btn btn-gray text-danger btn-sm" data-toggle="modal" data-target="#DeleteDesignationModal" onclick="" data-id="' .$designation->id. '"><i class="fa fa-trash-o" aria-hidden="true"></i></button>';
                    }

                    $nestedData['title'] = $designation->title;
                    $nestedData['estatus'] = $estatus;
                    $nestedData['created_at'] = date('Y-m-d H:i A', strtotime($designation->created_at));
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

    public function changedesignationstatus($id){
        $designation = Designation::find($id);
        if ($designation->estatus==1){
            $designation->estatus = 2;
            $designation->save();
            return response()->json(['status' => '200','action' =>'deactive']);
        }
        if ($designation->estatus==2){
            $designation->estatus = 1;
            $designation->save();
            return response()->json(['status' => '200','action' =>'active']);
        }
    }

    public function editdesignation($id){
        $designation = Designation::find($id);
        return response()->json($designation);
    }

    public function deletedesignation($id){
        $designation = Designation::find($id);
        if ($designation){
            
            $designation->estatus = 3;
            $designation->save();

            $designation->delete();

            return response()->json(['status' => '200']);
        }
        return response()->json(['status' => '400']);
    }

    public function permissiondesignation($id){
        $page = "Designation Permission";
        $designation_permissions = DesignationPermission::where('designation_id',$id)->orderBy('project_page_id','asc')->get();
        return view('admin.designation.permission',compact('designation_permissions'))->with('page',$page);
    }

    public function savepermission(Request $request){
        foreach ($request->permissionData as $pdata) {
            $designation_permission = DesignationPermission::where('designation_id',$request->designation_id)->where('project_page_id',$pdata['page_id'])->first();
            $designation_permission->can_read = $pdata['can_read'];
            $designation_permission->can_write = $pdata['can_write'];
            $designation_permission->can_delete = $pdata['can_delete'];
            $designation_permission->save();
        }
        return response()->json(['status' => '200']);
    }
}
