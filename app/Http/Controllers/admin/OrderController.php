<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Experience;
use App\Models\ProjectPage;
use App\Models\User;

class OrderController extends Controller
{
    private $page = "Orders";

    public function index(){
        return view('admin.orders.list')->with('page',$this->page);
    }

    public function allOrderlist(Request $request){
        if ($request->ajax()) {
            $columns = array(
                0 =>'id',
                1 =>'order_info',
                2=> 'customer_info',
                3=> 'note',
                4=> 'payment_status',
                5=> 'order_status',
                6=> 'created_at',
                7=> 'action',
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

            if(empty($request->input('search.value')))
            {
                $Orders = Order::with('experience');
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
                $Orders = Order::with('experience');
                if (isset($order_status)){
                    $Orders = $Orders->whereIn('order_status',$order_status);
                }
                $Orders = $Orders->where(function($query) use($search){
                    $query->where('custom_orderid','LIKE',"%{$search}%")
                        ->orWhere('payment_type', 'LIKE',"%{$search}%");
                    })
                    ->offset($start)
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
                    $user_info = User::find($Order->user_id);
                    // dump($user_info);
                    $page_id = ProjectPage::where('route_url','admin.orders.list')->pluck('id')->first();

                    $action = '';
                   // $action .= '<button id="invoiceBtn" class="btn btn-gray text-blue btn-sm" onclick="getInvoiceData(\''.$Order->id.'\')"><i class="fa fa-print" aria-hidden="true"></i></button>';
                    if($Order->tracking_url != ""){
                        $action .= '<a href="'.$Order->tracking_url.'" id="" class="btn btn-gray text-dark btn-sm" ><i class="fa fa-truck" aria-hidden="true"></i></a>';
                    }else{
                        $action .= '<button id="editTrackingBtn" class="btn btn-gray text-dark btn-sm" data-toggle="modal" data-target="#TrackingModal" onclick="" data-id="' .$Order->id. '" ><i class="fa fa-truck" aria-hidden="true"></i></button>';
                    }

                    if( getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id)) ) {
                        $action .= '<button id="ViewOrderBtn" target="blank" class="btn gradient-9 btn-sm" onclick="editOrder(' . $Order->id . ')"><i class="fa fa-eye" aria-hidden="true"></i></button>';
                    }
                    
                    if ( isset($Order->order_status) && $Order->order_status == 4 && (getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id))) ){
                        $action .= '<button type="button" class="btn mb-1 btn-success btn-xs" data-id="'.$Order->id.'" id="ApproveReturnRequestBtn">Approve</button>';
                        $action .= '<button type="button" class="btn mb-1 btn-danger btn-xs" data-id="'.$Order->id.'" id="RejectReturnRequestBtn">Reject</button>';
                    }

                    $order_info = '<span>Booking ID: '.$Order->custom_orderid.'</span>';
                    $order_info .= '<span>Total Order Cost: '.$Order->total_amount.'</span>';

              
                    $customer_info = '';
                    

                    $NoteBoxDisplay = $Order->order_note;
                    if( getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id)) ) {
                        $NoteBoxDisplay = '<textarea class="custom-textareaBox orderNoteBox" id="orderNoteBox' . $Order->id . '" rows="4" data-id="' . $Order->id . '">' . $Order->order_note . '</textarea>';
                    }

                  

                    $date = '<span><b>Order Date:</b></span><span>'.date('d-m-Y h:i A', strtotime($Order->created_at)).'</span>';
                    if(isset($Order->delivery_date)){
                        $date .= '<span><b>Delivery Date:</b></span><span>'.$Order->delivery_date.'</span>';
                    }

                    $nestedData['order_info'] = $order_info;
                    $nestedData['customer_info'] = $customer_info;
                    $nestedData['note'] = $NoteBoxDisplay;
                    $nestedData['payment_status'] = "";
                    $nestedData['order_status'] = "";
                    $nestedData['created_at'] = $date;
                    $nestedData['action'] = $action;
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
