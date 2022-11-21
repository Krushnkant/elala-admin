<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\AgeGroup;
use App\Models\ProjectPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AgeGroupController extends Controller
{
    private $page = "Age Group";

    public function index(){
        return view('admin.agegroups.list')->with('page',$this->page);
    }

    public function addorupdateagegroups(Request $request){
        $messages = [
            'from_age.required' =>'Please provide a From Age',
            'to_age.required' =>'Please provide a To Age.',
        ];

        $validator = Validator::make($request->all(), [
            'from_age' => 'required|numeric',
            'to_age' => 'required|numeric',
            
        ], $messages);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(),'status'=>'failed']);
        }
        
            if(isset($request->action) && $request->action=="update"){
                $from_age = (int)$request->from_age;
                $agegroupCheck = AgeGroup::whereRaw("? BETWEEN from_age AND to_age", [$from_age])->where('id','<>',$request->agegroup_id)->first();
                
                if($agegroupCheck == null && $agegroupCheck == ""){
                    $action = "update";
                    $agegroup = AgeGroup::find($request->agegroup_id);

                    if(!$agegroup){
                        return response()->json(['status' => '400']);
                    }

                    $agegroup->from_age = $request->from_age;
                    $agegroup->to_age = $request->to_age;
                 
                }else{
                    return response()->json(['status' => '400' ,'message' => 'This age range allready added']);  
                }
            }else{
                $from_age = (int)$request->from_age;
                $agegroupCheck = AgeGroup::whereRaw("? BETWEEN from_age AND to_age", [$from_age])->first();
                if($agegroupCheck == null && $agegroupCheck == ""){
                    $action = "add";
                    $agegroup = new AgeGroup();
                    $agegroup->from_age = $request->from_age;
                    $agegroup->to_age = $request->to_age;
                    $agegroup->created_at = new \DateTime(null, new \DateTimeZone('Asia/Kolkata'));
                }else{
                    return response()->json(['status' => '400' ,'message' => 'This age range allready added']);  
                }
            }
        
            $agegroup->save();
            return response()->json(['status' => '200', 'action' => $action]);
    }

    public function allagegroupslist(Request $request){
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
                1 =>'from_age',
                2=> 'to_age',
                4=> 'estatus',
                5=> 'created_at',
                6=> 'action',
            );

            $totalData = AgeGroup::count();

            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
//            dd($columns[$request->input('order.0.column')]);
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            if($order == "id"){
                $order == "created_at";
                $dir = 'asc';
            }

            if(empty($request->input('search.value')))
            {
                $agegroups = AgeGroup::offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();
            }
            else {
                $search = $request->input('search.value');
                $agegroups =  AgeGroup::where(function($query) use($search){
                      $query->where('id','LIKE',"%{$search}%")
                            ->orWhere('created_at', 'LIKE',"%{$search}%");
                      })
                      ->offset($start)
                      ->limit($limit)
                      ->orderBy($order,$dir)
                      ->get();

                $totalFiltered = AgeGroup::count();
            }

            $data = array();

            if(!empty($agegroups))
            {
                foreach ($agegroups as $agegroup)
                {
                    $page_id = ProjectPage::where('route_url','admin.agegroup.list')->pluck('id')->first();

                    if( $agegroup->estatus==1 && (getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id))) ){
                        $estatus = '<label class="switch"><input type="checkbox" id="agegroupstatuscheck_'. $agegroup->id .'" onchange="changeAgegroupStatus('. $agegroup->id .')" value="1" checked="checked"><span class="slider round"></span></label>';
                    }
                    elseif ($agegroup->estatus==1){
                        $estatus = '<label class="switch"><input type="checkbox" id="agegroupstatuscheck_'. $agegroup->id .'" value="1" checked="checked"><span class="slider round"></span></label>';
                    }

                    if( $agegroup->estatus==2 && (getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id))) ){
                        $estatus = '<label class="switch"><input type="checkbox" id="agegroupstatuscheck_'. $agegroup->id .'" onchange="changeAgegroupStatus('. $agegroup->id .')" value="2"><span class="slider round"></span></label>';
                    }
                    elseif ($agegroup->estatus==2){
                        $estatus = '<label class="switch"><input type="checkbox" id="agegroupstatuscheck_'. $agegroup->id .'" value="2"><span class="slider round"></span></label>';
                    }
                    $action='';
                    if ( getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id)) ){
                        $action .= '<button id="editAgeGroupBtn" class="btn btn-gray text-blue btn-sm" data-toggle="modal" data-target="#AgeGroupModel" onclick="" data-id="' .$agegroup->id. '"><i class="fa fa-pencil" aria-hidden="true"></i></button>';
                    }
                    if ( getUSerRole()==1 || (getUSerRole()!=1 && is_delete($page_id)) ){
                        $action .= '<button id="deleteAgeGroupBtn" class="btn btn-gray text-danger btn-sm" data-toggle="modal" data-target="#DeleteAgeGroupModel" onclick="" data-id="' .$agegroup->id. '"><i class="fa fa-trash-o" aria-hidden="true"></i></button>';
                    }

                    $nestedData['from_age'] = $agegroup->from_age;
                    $nestedData['to_age'] = $agegroup->to_age;
                    $nestedData['estatus'] = $estatus;
                    $nestedData['created_at'] = date('Y-m-d H:i:s', strtotime($agegroup->created_at));
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

    public function changeagegroupstatus($id){
        $agegroup = AgeGroup::find($id);
        if ($agegroup->estatus==1){
            $agegroup->estatus = 2;
            $agegroup->save();
            return response()->json(['status' => '200','action' =>'deactive']);
        }
        if ($agegroup->estatus==2){
            $agegroup->estatus = 1;
            $agegroup->save();
            return response()->json(['status' => '200','action' =>'active']);
        }
    }

    public function editagegroups($id){
        $agegroup = AgeGroup::find($id);
        return response()->json($agegroup);
    }

    public function deleteagegroup($id){
        $agegroup = AgeGroup::find($id);
        if ($agegroup){
            $agegroup->estatus = 3;
            $agegroup->save();
            $agegroup->delete();
            return response()->json(['status' => '200']);
        }
        return response()->json(['status' => '400']);
    }
}
