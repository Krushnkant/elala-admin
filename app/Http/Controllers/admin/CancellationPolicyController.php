<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ExperienceCancellationPolicy;
use App\Models\ProjectPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CancellationPolicyController extends Controller
{
    private $page = "Cancellation Policy";

    public function index(){
        return view('admin.policy.list')->with('page',$this->page);
    }

    public function addorupdatepolicy(Request $request){

        $messages = [
            'title.required' =>'Please provide a title',
        ];

        $validator = Validator::make($request->all(), [
            'title' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(),'status'=>'failed']);
        }

      
        if(!isset($request->policy_id)){
            $policy = new ExperienceCancellationPolicy();
            $policy->title = $request->title;
            $policy->description = $request->description;
            $policy->created_at = new \DateTime(null, new \DateTimeZone('Asia/Kolkata'));
            $policy->save();
            return response()->json(['status' => '200', 'action' => 'add']);
        }
        else{
            $policy = ExperienceCancellationPolicy::find($request->policy_id);
            if ($policy) {
                $policy->title = $request->title;
                $policy->description = $request->description;
                $policy->save();
                return response()->json(['status' => '200', 'action' => 'update']);
            }
            return response()->json(['status' => '400']);
        }
    }

    public function allpolicylist(Request $request){
        if ($request->ajax()) {
            $tab_type = $request->tab_type;
            $columns = array(
                0=>'id',
                1=>'title',
                2=>'description',
                3=> 'estatus',
                4=> 'created_at',
                5=> 'action',
            );

          
           $totalData = ExperienceCancellationPolicy::count();
            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            if($order == "id"){
                $order = "created_at";
                $dir = 'desc';
            }

            if(empty($request->input('search.value')))
            {
               // $policys = ExperienceCancellationPolicy::where('is_specification',$is_specification)
                 $policies = ExperienceCancellationPolicy::
                    offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();
            }
            else {
                $search = $request->input('search.value');
               $policies =  ExperienceCancellationPolicy::where(function($query) use($search){
                        $query->where('id','LIKE',"%{$search}%")
                                ->orWhere('title', 'LIKE',"%{$search}%");
                        })
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();

                $totalFiltered = ExperienceCancellationPolicy::
                    where(function($query) use($search){
                        $query->where('id','LIKE',"%{$search}%")
                            ->orWhere('title', 'LIKE',"%{$search}%");
                    })
                    ->count();
            }

            $data = array();

            if(!empty($policies))
            {
                foreach ($policies as $policy)
                {
                    
                    $page_id = ProjectPage::where('route_url','admin.policy.list')->pluck('id')->first();

                    if($policy->estatus==1 && (getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id))) ){
                        $estatus = '<label class="switch"><input type="checkbox" id="policystatuscheck_'. $policy->id .'" onchange="chagePolicyStatus('. $policy->id .')" value="1" checked="checked"><span class="slider round"></span></label>';
                    }
                    else if ($policy->estatus==1){
                        $estatus = '<label class="switch"><input type="checkbox" id="policystatuscheck_'. $policy->id .'" value="1" checked="checked"><span class="slider round"></span></label>';
                    }

                    if($policy->estatus==2 && (getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id))) ){
                        $estatus = '<label class="switch"><input type="checkbox" id="policystatuscheck_'. $policy->id .'" onchange="chagePolicyStatus('. $policy->id .')" value="2"><span class="slider round"></span></label>';
                    }
                    else if ($policy->estatus==2){
                        $estatus = '<label class="switch"><input type="checkbox" id="policystatuscheck_'. $policy->id .'" value="2"><span class="slider round"></span></label>';
                    }

                    $action='';
                    if ( getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id)) ){
                        $action .= '<button id="editPolicyBtn" class="btn btn-gray text-blue btn-sm" data-toggle="modal" data-target="#PolicyModal" onclick="" data-id="' .$policy->id. '"><i class="fa fa-pencil" aria-hidden="true"></i></button>';
                    }
                    if ( getUSerRole()==1 || (getUSerRole()!=1 && is_delete($page_id)) ){
                        $action .= '<button id="deletePolicyBtn" class="btn btn-gray text-danger btn-sm" data-toggle="modal" data-target="#DeletePolicyModal" onclick="" data-id="' .$policy->id. '"><i class="fa fa-trash-o" aria-hidden="true"></i></button>';
                    }
                    $nestedData['title'] = $policy->title;
                    $nestedData['description'] = $policy->description;
                    $nestedData['estatus'] = $estatus;
                    $nestedData['created_at'] = date('d-m-Y h:i A', strtotime($policy->created_at));
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

    public function editpolicy($id){
        $policy = ExperienceCancellationPolicy::find($id);
        return response()->json($policy);
    }

    public function deletepolicy($id){
        $policy = ExperienceCancellationPolicy::find($id);
        if ($policy){
            $policy->estatus = 3;
            $policy->save();

            $policy->delete();
            return response()->json(['status' => '200']);
        }
        return response()->json(['status' => '400']);
    }

    public function chagepolicystatus($id){
        $policy = ExperienceCancellationPolicy::find($id);
        if ($policy->estatus==1){
            $policy->estatus = 2;
            $policy->save();
            return response()->json(['status' => '200','action' =>'deactive']);
        }
        if ($policy->estatus==2){
            $policy->estatus = 1;
            $policy->save();
            return response()->json(['status' => '200','action' =>'active']);
        }
    }
}
