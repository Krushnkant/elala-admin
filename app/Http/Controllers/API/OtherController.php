<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{PageInfo,TeamMember,Testimonial,Faq,SingleOrdPayment,SupplierPayments};
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class OtherController extends BaseController
{
    public function infopage(){
        $PageInfo = PageInfo::first();
        $data['about_description'] = $PageInfo->about_description;
        $data['contact_description'] = $PageInfo->contact_description;
        $data['about_image'] = isset($PageInfo->about_image)?'images/infopage/'.$PageInfo->about_image:"";
        $data['contact_image'] = isset($PageInfo->contact_image)?'images/infopage/'.$PageInfo->contact_image:"";
        $data['privacy_policy'] = $PageInfo->privacy_policy;
        $data['terms_condition'] = $PageInfo->terms_condition;
        return $this->sendResponseWithData($data,"Page Info Data Retrieved Successfully.");
    }

    public function getTeamMember(){
        $TeamMembers = TeamMember::where('estatus',1)->get();
        $teammembers_arr = array();
        foreach ($TeamMembers as $TeamMember){
            $temp = array();
            $temp['id'] = $TeamMember->id;
            $temp['name'] = $TeamMember->name;
            $temp['position'] = $TeamMember->position;
            $temp['image'] = isset($TeamMember->image)?'images/teams/'.$TeamMember->image:"";
            array_push($teammembers_arr,$temp);
        }
        return $this->sendResponseWithData($teammembers_arr,"Team Member Retrieved Successfully.");
    }

    public function getTestimonial(){
        $Testimonials = Testimonial::where('estatus',1)->get();
        $testimonials_arr = array();
        foreach ($Testimonials as $Testimonial){
            $temp = array();
            $temp['id'] = $Testimonial->id;
            $temp['name'] = $Testimonial->name;
            $temp['country'] = $Testimonial->country;
            $temp['description'] = $Testimonial->description;
            $temp['image'] = isset($Testimonial->image)?'images/testimonials/'.$Testimonial->image:"";
            array_push($testimonials_arr,$temp);
        }
        return $this->sendResponseWithData($testimonials_arr,"Testimonial Retrieved Successfully.");
    }


    public function contact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'message' => 'required',
        ]);
        if($validator->fails()){
            return response()->json(['errors' => $validator->errors(),'status'=>'failed']);
        }
        return $this->sendResponseSuccess("Contact Send Successfully");
    }

    public function getFaq(){
        $Faqs = Faq::where('estatus',1)->get();
        $faqs_arr = array();
        foreach ($Faqs as $Faq){
            $temp = array();
            $temp['id'] = $Faq->id;
            $temp['question'] = $Faq->question;
            $temp['answer'] = $Faq->answer;
            array_push($faqs_arr,$temp);
        }
        return $this->sendResponseWithData($faqs_arr,"Testimonial Retrieved Successfully.");
    }

    public function paymentHistory(Request $request){
        //$data = array();
        // $now = Carbon::now();
        // $yesterday = Carbon::yesterday();
        // $tomorrow = Carbon::tomorrow();
  
     
        $NextSupplierPayments = SupplierPayments::whereDate('payment_date','>=', Carbon::today())->where('host_id', $user_id = Auth::user()->id)->where('payment_status', 0)->first(['id','total_amt','payment_date']);
        // if(!$NextSupplierPayments){
        //     $NextSupplierPayments = SupplierPayments::whereDate('payment_date', Carbon::tomorrow())->where('host_id', $user_id = Auth::user()->id)->first(['id','total_amt']);
        // }
        $Payment['nextPayment'] = $NextSupplierPayments;

        $LastSupplierPayments = SupplierPayments::whereDate('payment_date','<=', Carbon::today())->where('host_id', $user_id = Auth::user()->id)->where('payment_status', 1)->first(['id','total_amt','payment_date']);
        // if(!$LastSupplierPayments){
        //     $LastSupplierPayments = SupplierPayments::whereDate('payment_date', Carbon::tomorrow())->where('host_id', $user_id = Auth::user()->id)->first(['id','total_amt']);
        // }

        $Payment['lastPayment'] = $LastSupplierPayments;
        $PassLastSupplierPayments = SupplierPayments::whereDate('payment_date','<=', Carbon::today())->where('host_id', $user_id = Auth::user()->id)->where('payment_status', 1)->sum('total_amt');
        $UpcomingLastSupplierPayments = SupplierPayments::whereDate('payment_date','>=', Carbon::today())->where('host_id', $user_id = Auth::user()->id)->where('payment_status', 0)->sum('total_amt');
        $Payment['pastPayment'] = $PassLastSupplierPayments;
        $Payment['upcomingPayment'] = $UpcomingLastSupplierPayments;
        return $this->sendResponseWithData($Payment,"Payment Retrieved Successfully.");
    }


    public function nextlastpayment(Request $request){
        $SingleOrdPayments = SingleOrdPayment::where('id',$request->payment_id)->get();
        $SingleOrdPayments_arr = array();
        foreach ($SingleOrdPayments as $SingleOrdPayment){
            $temp = array();
            $temp['payment_date'] = $SingleOrdPayment->created_at;
            $temp['net_payment'] = $SingleOrdPayment->total_amt;
            $temp['final_amount'] = $SingleOrdPayment->total_amt;
            array_push($SingleOrdPayments_arr,$temp);
        }
        return $this->sendResponseWithData($SingleOrdPayments_arr,"Payment Retrieved Successfully.");
    }

    public function pastpayment(Request $request){
        $SingleOrdPayments = SupplierPayments::whereDate('payment_date','<=', Carbon::today())->where('payment_status', 1)->get();
        $SingleOrdPayments_arr = array();
        foreach ($SingleOrdPayments as $SingleOrdPayment){
            $temp = array();
            $temp['payment_date'] = $SingleOrdPayment->payment_date;
            $temp['net_payment'] = $SingleOrdPayment->total_amt;
            $temp['final_amount'] = $SingleOrdPayment->total_amt;
            array_push($SingleOrdPayments_arr,$temp);
        }
        return $this->sendResponseWithData($SingleOrdPayments_arr,"Past Payment Retrieved Successfully.");
    }

    public function upcomingpayment(Request $request){
        $SingleOrdPayments = SupplierPayments::whereDate('payment_date','>=', Carbon::today())->where('payment_status', 0)->get();
        $SingleOrdPayments_arr = array();
        foreach ($SingleOrdPayments as $SingleOrdPayment){
            $temp = array();
            $temp['payment_date'] = $SingleOrdPayment->payment_date;
            $temp['net_payment'] = $SingleOrdPayment->total_amt;
            $temp['final_amount'] = $SingleOrdPayment->total_amt;
            array_push($SingleOrdPayments_arr,$temp);
        }
        return $this->sendResponseWithData($SingleOrdPayments_arr," Upcoming Payment Retrieved Successfully.");
    }

}
