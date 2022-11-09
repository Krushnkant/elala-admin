<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use App\Models\ProjectPage;
use App\Models\Language;
use App\Models\AgeGroup;
use App\Models\ExperienceProvideItem;
use App\Models\ExperienceBrindItem;
use App\Models\ExperienceLanguage;
use App\Models\CategoryAttribute;
use App\Models\ExperienceCancellationPolicy;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;


class ExperienceController extends Controller
{
    private $page = "Experience";

    public function index(){
        $action = "list";
        $experiences = Experience::where('estatus',1)->get();
        return view('admin.experience.list',compact('action','experiences'))->with('page',$this->page);
    }

    public function allexperiencelist(Request $request){
        if ($request->ajax()) {
            $columns = array(
                0 => 'sr_no',
                1 => 'name',
                2 => 'title',
                3 => 'category_name',
                4 => 'time',
                5 => 'price',
                6 => 'estatus',
                7 => 'created_at',
                8 => 'action',
            );
            $totalData = Experience::count();
            
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
                $experiences = Experience::with('user','category')->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();
              
            }
            else {
                $search = $request->input('search.value');
                $experiences =  Experience::with('user','category')->where('sr_no','LIKE',"%{$search}%")
                    ->orWhere('title', 'LIKE',"%{$search}%")
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();


                $totalFiltered = Experience::with('user','category')->where('sr_no','LIKE',"%{$search}%")
                    ->orWhere('title', 'LIKE',"%{$search}%")
                    ->count();
          
            }

            $data = array();
            //dd($experiences);
            if(!empty($experiences))
            {
                foreach ($experiences as $experience)
                {
                    $page_id = ProjectPage::where('route_url','admin.categories.list')->pluck('id')->first();

                    if( $experience->estatus==1 && (getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id))) ){
                        $estatus = '<label class="switch"><input type="checkbox" id="ExperienceStatuscheck_'. $experience->id .'" onchange="chageExperienceStatus('. $experience->id .')" value="1" checked="checked"><span class="slider round"></span></label>';
                    }
                    elseif ($experience->estatus==1){
                        $estatus = '<label class="switch"><input type="checkbox" id="ExperienceStatuscheck_'. $experience->id .'" value="1" checked="checked"><span class="slider round"></span></label>';
                    }

                    if( $experience->estatus==2 && (getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id))) ){
                        $estatus = '<label class="switch"><input type="checkbox" id="ExperienceStatuscheck_'. $experience->id .'" onchange="chageExperienceStatus('. $experience->id .')" value="2"><span class="slider round"></span></label>';
                    }
                    elseif ($experience->estatus==2){
                        $estatus = '<label class="switch"><input type="checkbox" id="ExperienceStatuscheck_'. $experience->id .'" value="2"><span class="slider round"></span></label>';
                    }

                    $action='';
                    if( getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id)) ){
                        $action .= '<button id="editExperienceBtn" class="btn btn-gray text-blue btn-sm" data-id="' .$experience->id. '"><i class="fa fa-pencil" aria-hidden="true"></i></button>';
                    }
                    if(getUSerRole()==1 || (getUSerRole()!=1 && is_delete($page_id)) ){
                        $action .= '<button id="deleteExperienceBtn" class="btn btn-gray text-danger btn-sm" data-toggle="modal" data-target="#DeleteExperienceModal" onclick="" data-id="' .$experience->id. '"><i class="fa fa-trash-o" aria-hidden="true"></i></button>';
                    }

                    $price = '<span> Price: '.$experience->individual_rate.'/Person</span><br>'.'<span>Price:'.$experience->min_private_group_rate.'/Group</span>';
                    
                    $nestedData['name'] = $experience->user->full_name;
                    $nestedData['title'] = $experience->title;
                    $nestedData['category_name'] = $experience->category->category_name;
                    $nestedData['time'] = $experience->duration .' min';
                    $nestedData['price'] = $price;
                    $nestedData['estatus'] = $estatus;
                    $nestedData['created_at'] = date('d-m-Y h:i A', strtotime($experience->created_at));
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

    public function editexperience($id){
        $action = "edit";
        $languages = Language::where('estatus',1)->get()->toArray();
        $agegroups = AgeGroup::where('estatus',1)->get()->toArray();
        $cancellationpolicy = ExperienceCancellationPolicy::get()->toArray();
        
        
        $experiencelanguage = ExperienceLanguage::where('experience_id',$id)->pluck('language_id')->toArray();
        $provideitem = ExperienceProvideItem::where('experience_id',$id)->pluck('title')->toArray();
        $provideitems = implode(',',$provideitem);
        $brinditem = ExperienceBrindItem::where('experience_id',$id)->pluck('title')->toArray();
        $brinditems = implode(',',$brinditem);
        $experience = Experience::with('category','media','scheduletime','discountrate','categoryattribute')->find($id);
        $categoryattributes = CategoryAttribute::with('attr_optioin')->where('category_id',$experience->category_id)->get()->toArray();
        return view('admin.experience.list',compact('action','experience','languages','agegroups','cancellationpolicy','categoryattributes','experiencelanguage','provideitems','brinditems'))->with('page',$this->page);
    }

    public function changeexperiencestatus($id){
        $experience = Experience::find($id);
        if ($experience->estatus==1){
            $experience->estatus = 2;
            $experience->save();
            return response()->json(['status' => '200','action' =>'deactive']);
        }
        if ($experience->estatus==2){
            $experience->estatus = 1;
            $experience->save();
            return response()->json(['status' => '200','action' =>'active']);
        }
    }

    public function deleteexperience($id){
        $experience = Experience::find($id);
        if ($experience){
            $experience->estatus = 3;
            $experience->save();
            $experience->delete();
            return response()->json(['status' => '200']);
        }
        return response()->json(['status' => '400']);
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
