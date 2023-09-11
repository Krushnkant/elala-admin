<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectPage;
use App\Models\PageInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InfopageController extends Controller
{
    public function aboutus(){
        $Infopages = PageInfo::first();
        $canWrite = false;
        $page_id = ProjectPage::where('route_url','admin.info_page.about')->pluck('id')->first();
        if( getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id)) ){
            $canWrite = true;
        }
        return view('admin.infopage.about',compact('Infopages','canWrite'))->with('page','About Us');
    }

    public function edit(){
        $Infopages = PageInfo::first();
        return response()->json($Infopages);
    }

    public function update(Request $request){
        $Infopages = PageInfo::first();
        if(!$Infopages){
            $Infopages = New PageInfo;
        }
        $Infopages->about_description = isset($request->about_description)?$request->about_description:$Infopages->about_description;
        $Infopages->contact_description = isset($request->contact_description)?$request->contact_description:$Infopages->contact_description;
        $Infopages->privacy_policy = isset($request->privacy_policy)?$request->privacy_policy:$Infopages->privacy_policy;
        $Infopages->terms_condition = isset($request->terms_condition)?$request->terms_condition:$Infopages->terms_condition;

        $old_about_image = $Infopages->about_image;
        if ($request->hasFile('about_image')) {
            $image = $request->file('about_image');
            $image_name = 'about_image_' . rand(111111, 999999) . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('images/infopage');
            $image->move($destinationPath, $image_name);
            if(isset($old_about_image)) {
                $old_about_image = public_path('images/infopage/' . $old_about_image);
                if (file_exists($old_about_image)) {
                    unlink($old_about_image);
                }
            }
            $Infopages->about_image = $image_name;
        }

        $old_contact_image = $Infopages->contact_image;
        if ($request->hasFile('contact_image')) {
            $image = $request->file('contact_image');
            $image_name = 'contact_image_' . rand(111111, 999999) . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('images/infopage');
            $image->move($destinationPath, $image_name);
            if(isset($old_contact_image)) {
                $old_contact_image = public_path('images/infopage/' . $old_contact_image);
                if (file_exists($old_contact_image)) {
                    unlink($old_contact_image);
                }
            }
            $Infopages->contact_image = $image_name;
        }

        $Infopages->save();
        return response()->json(['status' => '200','Infopages' => $Infopages]);
    }

    public function contactus(){
        $Infopages = PageInfo::first();
        $canWrite = false;
        $page_id = ProjectPage::where('route_url','admin.info_page.contact')->pluck('id')->first();
        if( getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id)) ){
            $canWrite = true;
        }
        return view('admin.infopage.contact',compact('Infopages','canWrite'))->with('page','Contact Us');
    }

    public function privacy_policy(){
        $Infopages = PageInfo::first();
        $canWrite = false;
        $page_id = ProjectPage::where('route_url','admin.info_page.privacy_policy')->pluck('id')->first();
        if( getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id)) ){
            $canWrite = true;
        }
        return view('admin.infopage.privacy_policy',compact('Infopages','canWrite'))->with('page','Privacy Policy');
    }

    public function terms_condition(){
        $Infopages = PageInfo::first();
        $canWrite = false;
        $page_id = ProjectPage::where('route_url','admin.info_page.terms_condition')->pluck('id')->first();
        if( getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id)) ){
            $canWrite = true;
        }
        return view('admin.infopage.terms_condition',compact('Infopages','canWrite'))->with('page','Terms Condition');
    }

}
