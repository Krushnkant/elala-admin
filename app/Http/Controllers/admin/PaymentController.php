<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupplierPayments;
use App\Models\SingleOrdPayment;
use App\Models\ProjectPage;
use App\Models\User;
use Carbon\Carbon;

class PaymentController extends Controller
{
    private $page = "Payment";

    public function index(){
        $users = User::where('role',3)->where('is_completed',1)->get();
        return view('admin.payments.list',compact('users'))->with('page',$this->page);
    }

    public function allpaymentslist(Request $request){
        //dd($request->all());
        if ($request->ajax()) {
            $columns = array(
                0 =>'id',
                1 =>'user',
                2 =>'amount',
                3 => 'created_at'
            );

            $tab_type = $request->tab_type;
            if ($tab_type == "next_payments_tab"){
                $order_status = "next";
            }
            elseif ($tab_type == "last_payments_tab"){
                $order_status = "last";
            }
            elseif ($tab_type == "past_payments_tab"){
                $order_status = "past";
            }
            elseif ($tab_type == "upcoming_payments_tab"){
                $order_status = "upcoming";
            }
          
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            if($order == "id"){
                $order = "created_at";
                $dir = 'desc';
            }

            $totalData = SupplierPayments::count();
            if (isset($order_status) && $order_status == "next"){
                $totalData = SupplierPayments::whereDate('payment_date','>=', Carbon::today())->where('payment_status', 0)->groupBy('host_id')->orderBy('payment_date','ASC')->count();
            }
            if (isset($order_status) && $order_status == "last"){
                $totalData = SupplierPayments::whereDate('payment_date','<=', Carbon::today())->where('payment_status', 1)->groupBy('host_id')->orderBy('payment_date','ASC')->count();
            }
            if (isset($order_status) && $order_status == "past"){
                $totalData = SupplierPayments::whereDate('payment_date','<=', Carbon::today())->where('payment_status', 1)->orderBy('payment_date','ASC')->count();
            }
            if (isset($order_status) && $order_status == "upcoming"){
                $totalData = SupplierPayments::whereDate('payment_date','>=', Carbon::today())->where('payment_status', 0)->orderBy('payment_date','ASC')->count();
            }
            $totalFiltered = $totalData;
            if(empty($request->input('search.value')) &&  empty($request->host_filter)  && empty($request->start_date) && empty($request->end_date))
            {
                $Orders = SupplierPayments::with('user');
                if (isset($order_status) && $order_status == "next"){
                    $Orders = $Orders->whereDate('payment_date','>=', Carbon::today())->where('payment_status', 0)->groupBy('host_id')->orderBy('payment_date','ASC');
                }
                if (isset($order_status) && $order_status == "last"){
                    $Orders = $Orders->whereDate('payment_date','<=', Carbon::today())->where('payment_status', 1)->groupBy('host_id')->orderBy('payment_date','ASC');
                }
                if (isset($order_status) && $order_status == "past"){
                    $Orders = $Orders->whereDate('payment_date','<=', Carbon::today())->where('payment_status', 1)->orderBy('payment_date','ASC');
                }
                if (isset($order_status) && $order_status == "upcoming"){
                    $Orders = $Orders->whereDate('payment_date','>=', Carbon::today())->where('payment_status', 0)->orderBy('payment_date','ASC');
                }
                $Orders = $Orders->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();
                      
            }
            else {
                $search = $request->input('search.value');
                $Orders = SupplierPayments::with('user');
                
                if (isset($request->host_filter) && $request->host_filter!=""){
                    $host_filter = $request->host_filter;
                    $Orders = $Orders->where('host_id', $host_filter)->orWhere('user_id', $host_filter);
                }
                if (isset($request->start_date) && $request->start_date!="" && isset($request->end_date) && $request->end_date!=""){
                    $start_date = $request->start_date;
                    $end_date = $request->end_date;
                    $Orders = $Orders->whereRaw("DATE(booking_date) between '".$start_date."' and '".$end_date."'");
                }
                if (isset($order_status) && $order_status == "next"){
                    $Orders = $Orders->whereDate('payment_date','>=', Carbon::today())->where('payment_status', 0)->groupBy('host_id')->orderBy('payment_date','ASC');
                }
                if (isset($order_status) && $order_status == "last"){
                    $Orders = $Orders->whereDate('payment_date','<=', Carbon::today())->where('payment_status', 1)->groupBy('host_id')->orderBy('payment_date','ASC');
                }
                if (isset($order_status) && $order_status == "past"){
                    $Orders = $Orders->whereDate('payment_date','<=', Carbon::today())->where('payment_status', 1)->orderBy('payment_date','ASC');
                }
                if (isset($order_status) && $order_status == "upcoming"){
                    $Orders = $Orders->whereDate('payment_date','>=', Carbon::today())->where('payment_status', 0)->orderBy('payment_date','ASC');
                }
                $Orders = $Orders->where(function($query) use($search){
                    $query->where('id','LIKE',"%{$search}%")
                        ->orWhere('total_amt', 'LIKE',"%{$search}%");
                    });
                    
                    $Orders = $Orders->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();

                $totalFiltered = count($Orders->toArray());
            }

            $data = array();
            if(!empty($Orders))
            {
                foreach ($Orders as $Order)
                {
                    //dd($Order->orderslot);
                    $user_info = User::find($Order->host_id);
                    // dump($user_info);
                    $page_id = ProjectPage::where('route_url','admin.orders.list')->pluck('id')->first();
                    if(isset($user_info->profile_pic) && $user_info->profile_pic!=null){
                        $profile_pic = $user_info->profile_pic;
                    }
                    else{
                        $profile_pic = url('images/default_avatar.jpg');
                    }

                 

                    $nestedData['user'] = '<span><img src="'. $profile_pic .'" width="50px" height="50px" alt="Profile Pic"></span><span>'.$user_info->full_name.'</span>';
                    $nestedData['amount'] = $Order->total_amt;
                    $nestedData['created_at'] = date('d-m-Y', strtotime($Order->payment_date));
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
}
