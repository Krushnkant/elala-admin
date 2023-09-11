<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttributeOption;
use App\Models\CategoryAttribute;

class CategoryAttributeController extends Controller
{
    public function addcategoryattribute($id)
    {
        $already = CategoryAttribute::with('attr_optioin')->where('category_id', $id)->get();
        if(count($already) > 0){
            return view('admin.categories.update', compact('id','already'));
        }else{
            return view('admin.categories.add', compact('id'));
        }
    }

    public function categoryattributestore(Request $request)
    {
        $data = $request->all();
        //dd($data);
        $field_titles = (isset($data['title']) && $data['title']) ? $data['title'] : null;
        $old_field_titles = (isset($data['old_title']) && $data['old_title']) ? $data['old_title'] : null;
        $oldcateids = CategoryAttribute::where('category_id',$request->category_id)->get()->pluck('id');
        $allreadycateids = (isset($data['allreadycateids']) && $data['allreadycateids']) ? $data['allreadycateids'] : [];
        $oldattroptionids = AttributeOption::wherein('attribute_id',$oldcateids)->get()->pluck('id'); 
         
        // if(count($catattr) > 0){
        //     $delete_sub_form_data_1 = AttributeOption::whereIn('attribute_id', $catattr)->delete();
        //     $delete_subform_remove_form_fields = CategoryAttribute::where('category_id', $request->category_id)->delete();
        // }
        $optionArray = [];
        if($field_titles != ""){
            foreach($field_titles as $key => $field_title){
                $CategoryAttributes = new CategoryAttribute();
                $CategoryAttributes->category_id = $request->category_id;
                $CategoryAttributes->title = $field_title;
                $CategoryAttributes->field_id = $request->field_type[$key];
                $CategoryAttributes->save();
                if($request->field_type[$key] == 2 || $request->field_type[$key] == 3){
                    $field_option_name = 'field_options_'.$key;
                    $field_options = $request->$field_option_name;
                    foreach($field_options as  $field_option){
                        $AttributeOption = new AttributeOption();
                        $AttributeOption->attribute_id = $CategoryAttributes->id;
                        $AttributeOption->option_value = $field_option;
                        $AttributeOption->save();
                        $optionArray[] = $AttributeOption->id;
                    }
                   
                    
                }
            }
            
        }

        if($old_field_titles != ""){
            foreach($old_field_titles as $key => $old_field_title){
                $CategoryAttributes = CategoryAttribute::find($request->allreadycateids[$key]);
                $CategoryAttributes->title = $old_field_title;
                $CategoryAttributes->field_id = $request->old_field_type[$key];
                $CategoryAttributes->save();
                if($request->old_field_type[$key] == 2 || $request->old_field_type[$key] == 3){
                    $field_option_name = 'field_options_'.$key;
                    if(isset($request->$field_option_name)){
                        $field_options = $request->$field_option_name;
                        foreach($field_options as $field_option){
                            $AttributeOption = new AttributeOption();
                            $AttributeOption->attribute_id = $CategoryAttributes->id;
                            $AttributeOption->option_value = $field_option;
                            $AttributeOption->save();
                        }
                    }

                    $old_field_option_id = 'old_field_options_ids_'.$key;
                    if(isset($request->$old_field_option_id)){
                    $old_field_options = $request->$old_field_option_id;
                    $field_option_name = 'old_field_options_'.$key;
                    $field_option = $request->$field_option_name;
                    //dd($field_options);
                    foreach($old_field_options as $opkey => $old_field_option){
                        $AttributeOption = AttributeOption::find($old_field_option);
                        $AttributeOption->attribute_id = $CategoryAttributes->id;
                        $AttributeOption->option_value = $field_option[$opkey];
                        $AttributeOption->save();
                        $optionArray[] = $AttributeOption->id;
                    }
                   }
                }
            } 
        }
        
        if(isset($oldcateids) && $oldcateids != ""){
            foreach($oldcateids as $oldcateid){
                if(!in_array($oldcateid, $allreadycateids)){
                    CategoryAttribute::where('id',$oldcateid)->delete();
                }
            }
        }
       
        if(isset($oldattroptionids) && $oldattroptionids != ""){
            foreach($oldattroptionids as $oldattroptionid){
                if(!in_array($oldattroptionid, $optionArray)){
                    AttributeOption::where('id',$oldattroptionid)->delete();
                }
            }
        }

        return response()->json(['status' => '200', 'action' => 'done']);
    }
}
