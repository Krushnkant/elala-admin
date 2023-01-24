<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{SingleOrdPayment,SupplierPayments};
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;


class PaymentController extends Controller
{
    public function paymentHistory(Request $request){

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
        $limit = isset($request->limit)?$request->limit:10;
        $SingleOrdPayments = SingleOrdPayment::where('id',$request->payment_id)->paginate($limit);
        $SingleOrdPayments_arr = array();
        foreach ($SingleOrdPayments as $SingleOrdPayment){
            $temp = array();
            $temp['payment_date'] = $SingleOrdPayment->created_at;
            $temp['net_payment'] = $SingleOrdPayment->total_amt;
            $temp['final_amount'] = $SingleOrdPayment->total_amt;
            array_push($SingleOrdPayments_arr,$temp);
        }
        $data['next_payments'] = $SingleOrdPayments_arr;
        $data['total_payment'] = count(SingleOrdPayment::where('id',$request->payment_id)->get());
        return $this->sendResponseWithData($data,"Payment Retrieved Successfully.");
    }

    public function pastpayment(Request $request){
        $limit = isset($request->limit)?$request->limit:10;
        $SingleOrdPayments = SupplierPayments::whereDate('payment_date','<=', Carbon::today())->where('payment_status', 1)->paginate($limit);
        $SingleOrdPayments_arr = array();
        foreach ($SingleOrdPayments as $SingleOrdPayment){
            $temp = array();
            $temp['payment_date'] = $SingleOrdPayment->payment_date;
            $temp['net_payment'] = $SingleOrdPayment->total_amt;
            $temp['final_amount'] = $SingleOrdPayment->total_amt;
            array_push($SingleOrdPayments_arr,$temp);
        }
        $data['last_payments'] = $SingleOrdPayments_arr;
        $data['total_payment'] = count(SupplierPayments::whereDate('payment_date','<=', Carbon::today())->where('payment_status', 1)->get());
        return $this->sendResponseWithData($data,"Past Payment Retrieved Successfully.");
    }

    public function upcomingpayment(Request $request){
        $limit = isset($request->limit)?$request->limit:10;
        $SingleOrdPayments = SupplierPayments::whereDate('payment_date','>=', Carbon::today())->where('payment_status', 0)->paginate($limit);
        $SingleOrdPayments_arr = array();
        foreach ($SingleOrdPayments as $SingleOrdPayment){
            $temp = array();
            $temp['payment_date'] = $SingleOrdPayment->payment_date;
            $temp['net_payment'] = $SingleOrdPayment->total_amt;
            $temp['final_amount'] = $SingleOrdPayment->total_amt;
            array_push($SingleOrdPayments_arr,$temp);
        }
        $data['upcomimg_payments'] = $SingleOrdPayments_arr;
        $data['total_payment'] = count( SupplierPayments::whereDate('payment_date','>=', Carbon::today())->where('payment_status', 0)->get());
        return $this->sendResponseWithData($data," Upcoming Payment Retrieved Successfully.");
    }
}
