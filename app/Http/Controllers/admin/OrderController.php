<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Experience;
use App\Models\ProjectPage;
use App\Models\User;
use Carbon\Carbon;

class OrderController extends Controller
{
    private $page = "Orders";

    public function index(){
        $users = User::where('role',3)->where('is_completed',1)->get();
        return view('admin.orders.list',compact('users'))->with('page',$this->page);
    }

    public function allOrderlist(Request $request){
        //dd($request->all());
        if ($request->ajax()) {
            $columns = array(
                0 =>'id',
                1 =>'experience',
                2 =>'order_info',
                3=> 'customer_info',
                4=> 'host',
                5=> 'note',
                6=> 'created_at'
            );

            $tab_type = $request->tab_type;
            if ($tab_type == "NewOrder_orders_tab"){
                $order_status = [1];
            }
            elseif ($tab_type == "OutforDelivery_orders_tab"){
                $order_status = [2];
            }
            elseif ($tab_type == "Delivered_orders_tab"){
                $order_status = [3];
            }
            elseif ($tab_type == "ReturnRequest_orders_tab"){
                $order_status = [4,5];
            }
            elseif ($tab_type == "Returned_orders_tab"){
                $order_status = [6];
            }
            elseif ($tab_type == "Cancelled_orders_tab"){
                $order_status = [7,8];
            }

            

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            if($order == "id"){
                $order = "created_at";
                $dir = 'desc';
            }

            $totalData = Order::count();
            if (isset($order_status)){
                $totalData = Order::whereIn('order_status',$order_status)->count();
            }
            $totalFiltered = $totalData;
            if(empty($request->input('search.value')) &&  empty($request->host_filter)  && empty($request->start_date) && empty($request->end_date))
            {
                $Orders = Order::with('experience.user','orderslot');
                if (isset($order_status)){
                    $Orders = $Orders->whereIn('order_status',$order_status);
                }
                $Orders = $Orders->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();  
            }
            else {
                $search = $request->input('search.value');
                $Orders = Order::with('experience.user','orderslot');
                
                if (isset($request->host_filter) && $request->host_filter!=""){
                    $host_filter = $request->host_filter;
                    $Orders = $Orders->where('host_id', $host_filter)->orWhere('user_id', $host_filter);
                }
                if (isset($request->start_date) && $request->start_date!="" && isset($request->end_date) && $request->end_date!=""){
                    $start_date = $request->start_date;
                    $end_date = $request->end_date;
                    $Orders = $Orders->whereRaw("DATE(booking_date) between '".$start_date."' and '".$end_date."'");
                }
                if (isset($order_status)){
                    $Orders = $Orders->whereIn('order_status',$order_status);
                }
                $Orders = $Orders->where(function($query) use($search){
                    $query->where('custom_orderid','LIKE',"%{$search}%")
                        ->orWhere('payment_type', 'LIKE',"%{$search}%");
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
                    $user_info = User::find($Order->user_id);
                    // dump($user_info);
                    $page_id = ProjectPage::where('route_url','admin.orders.list')->pluck('id')->first();
                    if(isset($user_info->profile_pic) && $user_info->profile_pic!=null){
                        $profile_pic = $user_info->profile_pic;
                    }
                    else{
                        $profile_pic = url('images/default_avatar.jpg');
                    }

                    if(isset($Order->experience->user->profile_pic) && $Order->experience->user->profile_pic!=null){
                        $host_pic = $Order->experience->user->profile_pic;
                    }
                    else{
                        $host_pic = url('images/default_avatar.jpg');
                    }

                    $time1 = Carbon::parse($Order->orderslot->time);
                    $endTime = $time1->addMinutes(isset($Order->experience->duration)?$Order->experience->duration:0);
                    $end_time = $endTime->format('H:i:s');

                    $order_info = '<span>Booking ID: '.$Order->custom_orderid.'</span>';
                    $order_info .= '<span>Total Order Cost: '.$Order->total_amount.'</span>';
                    $order_info .= '<span>Total Member: '.$Order->total_member.'</span>';
                    $booking_date = '<span><b> Date : </b>'.date('d-m-Y', strtotime($Order->booking_date)).'</span>';
                    $booking_date .= '<span><b> Slot : </b>'.$Order->orderslot->time.' to '.$end_time.'</span>';
                    $nestedData['experience'] = isset($Order->experience->title)?$Order->experience->title:"";
                    $nestedData['order_info'] = $order_info;
                    $nestedData['customer_info'] = '<span><img src="'. $profile_pic .'" width="50px" height="50px" alt="Profile Pic"></span><span>'.$user_info->full_name.'</span>';
                    $nestedData['host'] = '<span><img src="'. $host_pic .'" width="50px" height="50px" alt="Profile Pic"></span><span>'.isset($Order->experience->user->full_name)?$Order->experience->user->full_name:"".'</span>';;
                    $nestedData['booking'] = $booking_date;
                    $nestedData['created_at'] = date('d-m-Y h:i A', strtotime($Order->created_at));;
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
