<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use App\Models\ProjectPage;
use App\Models\Language;
use App\Models\AgeGroup;
use App\Models\ExperienceMedia;
use App\Models\ExperienceProvideItem;
use App\Models\ExperienceBrindItem;
use App\Models\ExperienceLanguage;
use App\Models\CategoryAttribute;
use App\Models\ExperienceCategoryAttribute;
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
                2 => 'time',
                3 => 'price',
                4 => 'estatus',
                5 => 'created_at',
                6 => 'action',
            );

            $tab_type = $request->tab_type;
            if ($tab_type == "Approved_experience_tab"){
                $experience_status = [1];
            }
            elseif ($tab_type == "Rejected_experience_tab"){
                $experience_status = [6];
            }
            elseif ($tab_type == "Draft_experience_tab"){
                $experience_status = [5];
            }
            elseif ($tab_type == "Padding_experience_tab"){
                $experience_status = [4];
            }
            elseif ($tab_type == "Deactive_experience_tab"){
                $experience_status = [2];
            }
            
            $totalData = Experience::count();
            if (isset($experience_status)){
                $totalData = Experience::whereIn('estatus',$experience_status)->count();
            }
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
                $experiences = Experience::with('user','category');
                if(isset($experience_status)){
                    $experiences = $experiences->whereIn('estatus',$experience_status);
                }
                $experiences = $experiences->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();
              
            }
            else {
                $search = $request->input('search.value');
                $experiences =  Experience::with('user','category');
                if (isset($experience_status)){
                    $experiences = $experiences->whereIn('estatus',$experience_status);
                }
                $experiences = $experiences->where('sr_no','LIKE',"%{$search}%")
                    ->orWhere('title', 'LIKE',"%{$search}%")
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();


                $totalFiltered = count($experiences->toArray());
          
            }

            $data = array();
            //dd($experiences);
            if(!empty($experiences))
            {
                foreach ($experiences as $experience)
                {
                    $page_id = ProjectPage::where('route_url','admin.categories.list')->pluck('id')->first();
                    if($experience->estatus==5 || $experience->estatus==6){
                        $experience_status = getExperienceStatus($experience->estatus);
                        $estatus = '<span class="'.$experience_status['class'].'">'.$experience_status['experience_status'].'</span>';
                    }
                    if($experience->estatus==4){
                        $experience_status = getExperienceStatus($experience->estatus);
                         $estatus = '<span class="'.$experience_status['class'].'">'.$experience_status['experience_status'].'</span> <br>';
                         $estatus .= '<button type="button" class="btn mb-1 btn-success btn-xs" data-id="'.$experience->id.'" id="ApproveExperienceBtn">Approve</button>';
                        $estatus .= '<button type="button" class="btn mb-1 btn-danger btn-xs" data-id="'.$experience->id.'" id="RejectExperienceBtn">Reject</button>';
                    }
                    
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

                    $price = '<span> Price: '.$experience->individual_rate.'/Person</span><br>'.'<span>Price: '.$experience->min_private_group_rate.'/Group</span>';
                    $category_name = isset($experience->category)?$experience->category->category_name:"";
                    $full_name = isset($experience->user->full_name)?$experience->user->full_name:"";
                    $name = '<span style="font-size:15px">  '.$experience->title.'</span><br>'.'<span>'.$category_name.'</span><br>'.'<span>Experience By : '.$full_name.'</span>';
                    
                    $nestedData['name'] = $name;
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

    public function save(Request $request){
        //dd($request->all());
        $messages = [
            'type.required' =>'Please provide a type',
            'location.required' =>'Please provide a location',
            'language_id.required' =>'Please select language',
            'title.required' =>'Please provide a title',
            'description.required' =>'Please provide a description',
            'duration.required' =>'Please provide a time section',
            'age_limit.required' =>'Please provide a age limit',
            'is_bring_item.required' =>'Please provide a is bring item',
            'meet_address.required' =>'Please provide a  street address',
            'meet_city.required' =>'Please provide a city',
            'meet_state.required' =>'Please provide a state',
            'meet_country.required' =>'Please provide a country',
            'pine_code.required' =>'Please provide a pinecode',
            'max_member_public_group_size.required' =>'Please provide a Public Groups',
            'max_member_private_group_size.required' =>'Please provide a Private Groups',
            'individual_rate.required' =>'Please provide a individual rate',
            'min_private_group_rate.required' =>'Please provide a Private Groups Rate',
            'cancellation_policy_id.required' =>'Please Chosse a cancellation policy'
        ];

        
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'location' => 'required',
            'language_id' => 'required',
            'title' => 'required',
            'description' => 'required',
            'duration' => 'required',
            'age_limit' => 'required',
            'is_bring_item' => 'required',
            'meet_address' => 'required',
            'meet_city' => 'required',
            'meet_state' => 'required',
            'meet_country' => 'required',
            'pine_code' => 'required',
            'max_member_public_group_size' => 'required',
            'max_member_private_group_size' => 'required',
            'individual_rate' => 'required',
            'min_private_group_rate' => 'required',
            'cancellation_policy_id' => 'required',
        ], $messages);
        

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(),'status'=>'failed']);
        }

        
        $action = "update";
        $experience = Experience::find($request->experience_id);

        if(!$experience){
            return response()->json(['status' => '400']);
        }
        $experience->type = $request->type;
        $experience->location = $request->location;
        //$experience->language_id = $request->language_id;
        $experience->title = $request->title;
        $experience->slug = createSlug($request->slug);
        $experience->description = $request->description;
        $experience->duration = $request->duration;
        $experience->age_limit = implode(',',$request->age_limit);
        $experience->is_bring_item = $request->is_bring_item;
        $experience->meet_address = $request->meet_address;
        $experience->meet_address_flat_no = $request->meet_address_flat_no;
        $experience->meet_city = $request->meet_city;
        $experience->meet_state = $request->meet_state;
        $experience->meet_country = $request->meet_country;
        $experience->pine_code = $request->pine_code;
        $experience->max_member_public_group_size = $request->max_member_public_group_size;
        $experience->individual_rate = $request->individual_rate;
        $experience->min_private_group_rate = $request->min_private_group_rate;
        $experience->cancellation_policy_id = $request->cancellation_policy_id;
        $experience->save();

        

        $oldlanguageids = ExperienceLanguage::where('experience_id',$request->experience_id)->get()->pluck('language_id')->toArray();
        foreach($oldlanguageids as $oldlanguageid){
            if(!in_array($oldlanguageid,$request->language_id)){
                $experiencelanguage = ExperienceLanguage::find($oldlanguageid);
                $experiencelanguage->delete();
            }
        }

        foreach($request->language_id as $language_id){
            if(!in_array($language_id,$oldlanguageids)){
                $experiencelanguage = New ExperienceLanguage();
                $experiencelanguage->experience_id = $request->experience_id;
                $experiencelanguage->language_id = $language_id;
                $experiencelanguage->save();
            }
        }

        $experiencelanguage = ExperienceProvideItem::where('experience_id',$request->experience_id);
        $experiencelanguage->delete();

        $experiencelanguage = ExperienceBrindItem::where('experience_id',$request->experience_id);
        $experiencelanguage->delete();

        $provide_items = explode(',',$request->provide_item);
        foreach($provide_items as $provide_item){
            $experiencelanguage = New ExperienceProvideItem();
            $experiencelanguage->experience_id = $request->experience_id;
            $experiencelanguage->title = $provide_item;
            $experiencelanguage->save();
        }

        $bring_items = explode(',',$request->bring_item);
        foreach($bring_items as $bring_item){
            $experiencelanguage = New ExperienceBrindItem();
            $experiencelanguage->experience_id = $request->experience_id;
            $experiencelanguage->title = $bring_item;
            $experiencelanguage->save();
        }

        if(isset($request->tagid)){
            foreach($request->tagid as $tid){
                $tagvalue = 'tagvalue'.$tid;
                $value = implode(',',$request->$tagvalue);
                $categoryattribute = ExperienceCategoryAttribute::where('experience_id',$request->experience_id)->where('cat_attr_id',$tid)->first();
                $categoryattribute->value = $value;
                $categoryattribute->save();
            }
        }

        if(isset($request->expImg)){
            $expImgs = explode(',',$request->expImg);
            $check_ext = array(
                "jpg", "jpeg", "png"
            );
            foreach($expImgs as $expImg){
                $ext = pathinfo($expImg, PATHINFO_EXTENSION);
                if(in_array($ext, $check_ext)){
                    $type = 'img';
                }else{
                    $type = 'video';
                }
                $experiencemedia = New ExperienceMedia();
                $experiencemedia->experience_id = $request->experience_id;
                $experiencemedia->thumb = $expImg;
                $experiencemedia->type = $type;
                $experiencemedia->save();
            }
        }
        return response()->json(['status' => '200', 'action' => $action]);
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

    public function uploadfile(Request $request){
        if(isset($request->action) && $request->action == 'uploadExpIcon'){
            if ($request->hasFile('files')) {
                $image = $request->file('files')[0];
                $image_name = 'experienceThumb_' . rand(111111, 999999) . time() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('images/experience_images');
                $image->move($destinationPath, $image_name);
                return response()->json(['data' => 'images/experience_images/'.$image_name]);
            }
        }
    }


    public function removefile(Request $request){
        if(isset($request->action) && $request->action == 'removeCatIcon'){
            // $image = $request->file;
            // if(isset($image)) {
            //     $image = public_path($request->file);
            //     if (file_exists($image)) {
            //         unlink($image);
            //         return response()->json(['status' => '200']);
            //     }
            // }
            $Media = ExperienceMedia::find($request->imgId);
            if ($Media){
                $Media->delete();
                return response()->json(['status' => '200']);
            }
            return response()->json(['status' => '400']);

        }
    }

    public function change_experience_status(Request $request){
        if (isset($request->experience_id)) {
            $experience = Experience::find($request->experience_id);
            if (!$experience) {
                return ['status' => 400];
            }
            if (isset($request->action) && $request->action == 'approve'){
                $experience->estatus = 1;
                $experience->save();
                return ['status' => 200];
            }
            if (isset($request->action) && $request->action == 'reject'){
                $experience->estatus = 6;
                $experience->save();
                return ['status' => 200];
            }
        }
        return ['status' => 400];
    }
}
