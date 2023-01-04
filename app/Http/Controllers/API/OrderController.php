<?php

namespace App\Http\Controllers\API;
use App\Models\ {Order,ExperienceMedia,Experience,OrderSlot,Review};
use App\Http\Controllers\Controller;
use App\Http\Resources\ExperienceResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class OrderController extends BaseController
{
    public function checkorderslot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'experience_id' => 'required',
            'booking_date' => 'required',
            'schedule_time_id' => 'required',
            'total_member' => 'required'
        ]);
        if($validator->fails()){
            return response()->json(['errors' => $validator->errors(),'status'=>'failed']);
        }

        $experience = Experience::where('id',$request->experience_id)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
        $max_member_size = $experience->max_member_public_group_size;
        $avalable_member =  $max_member_size - $request->total_member;
        if($avalable_member >= 0){
            $order_slot = OrderSlot::where(['experience_id'=>$request->experience_id,'booking_date'=>$request->booking_date,'schedule_time_id'=>$request->schedule_time_id])->first();
            if($order_slot){
                if($max_member_size - ($request->total_member + $order_slot->total_member) < 0){
                    return $this->sendError("Only ".$max_member_size - $order_slot->total_member." space available this time slot", "Space Not Available", []);
                }else{
                    return $this->sendResponseSuccess("space available this time slot");
                }
            }else{
                return $this->sendResponseSuccess("space available this time slot");
            } 
        }else{
            return $this->sendError("Only ".$max_member_size." space available this time slot", "Space Not Available", []);
        } 
    }

    public function createorder(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'experience_id' => 'required',
            'booking_date' => 'required',
            'schedule_time_id' => 'required',
            'adults_member' => 'required',
            'children_member' => 'required',
            'infants_member' => 'required',
            'total_member' => 'required',
            'adults_amount' => 'required',
            'children_amount' => 'required',
            'infants_amount' => 'required',
            'total_amount' => 'required',
            //'payment_type' => 'required',
            //'payment_currency' => 'required',
            //'payment_date' => 'required',
        ]);

        
        if($validator->fails()){
            return response()->json(['errors' => $validator->errors(),'status'=>'failed']);
        }

        $experience = Experience::where('id',$request->experience_id)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }

        $order_slot = OrderSlot::where(['experience_id'=>$request->experience_id,'booking_date'=>$request->booking_date,'schedule_time_id'=>$request->schedule_time_id])->first();
        if($order_slot){
            $order_slot->total_member = $order_slot->total_member + $request->total_member;
        }else{
            $order_slot = New OrderSlot();
            $order_slot->experience_id = $request->experience_id;
            $order_slot->booking_date = $request->booking_date;
            $order_slot->schedule_time_id = $request->schedule_time_id;
            $order_slot->total_member = $request->total_member;
        }
        $order_slot->save();

        $last_order_id = Order::orderBy('id','desc')->pluck('id')->first();
        if(isset($last_order_id)) {
            $last_order_id = $last_order_id + 1;
            $len_last_order_id = strlen($last_order_id);
            if($len_last_order_id == 1){
                $last_order_id = "000".$last_order_id;
            }
            elseif($len_last_order_id == 2){
                $last_order_id = "00".$last_order_id;
            }
            elseif($len_last_order_id == 3){
                $last_order_id = "0".$last_order_id;
            }
        }
        else{
            $last_order_id = "0001";
        }

        $order = new Order();
        $order->user_id = Auth::user()->id;
        $order->custom_orderid = Carbon::now()->format('ymd') . $last_order_id;
        $order->experience_id = $request->experience_id;
        $order->host_id = $experience->user_id;
        $order->booking_date = $request->booking_date;
        $order->schedule_time_id = $request->schedule_time_id;
        $order->adults = $request->adults_member;
        $order->children = $request->children_member;
        $order->infants = $request->infants_member;
        $order->total_member = $request->total_member;
        $order->adults_amount = $request->adults_amount;
        $order->children_amount = $request->children_amount;
        $order->infants_amount = $request->infants_amount;
        $order->total_amount = $request->total_amount;
        $order->payment_type = isset($request->payment_type) ? $request->payment_type : 2;
        $order->payment_transaction_id = isset($request->payment_transaction_id) ? $request->payment_transaction_id : '';
        $order->payment_currency = isset($request->payment_currency) ? $request->payment_currency : 'INR';
        $order->gateway_name = isset($request->gateway_name) ? $request->gateway_name : '';
        $order->payment_mode = isset($request->payment_mode) ? $request->payment_mode : '';
        $order->payment_date = isset($request->payment_date) ? $request->payment_date : '';
        $order->save();

        return $this->sendResponseSuccess("Order Submitted Successfully");
    }

    public function getHostOrders(Request $request){
        $limit = isset($request->limit)?$request->limit:10;
        $orders = Order::select('orders.id as booking_id','orders.*','experiences.title','experiences.full_name')->leftJoin('experiences', function($join) {
            $join->on('experiences.id', '=', 'orders.experience_id');
          })->leftJoin('users', function($join) {
            $join->on('orders.user_id', '=', 'users.id');
          });
          if(isset($request->from_date) && $request->from_date != "" && isset($request->to_date) && $request->to_date != ""){
              $orders =  $orders->whereBetween('orders.booking_date', [$request->from_date, $request->to_date]);
          }
          if(isset($request->search) && $request->search != ""){
            $search =$request->search;
            $orders = $orders->where(function($query) use($search){
              $query->where('custom_orderid','LIKE',"%{$search}%")
                  ->orWhere('booking_date', 'LIKE',"%{$search}%")
                  ->orWhere('title', 'LIKE',"%{$search}%")
                  ->orWhere('full_name', 'LIKE',"%{$search}%");
              });  
          }
          $orders =  $orders->where('experiences.user_id',Auth::user()->id);
          $total_orders =  $orders->get();
          $orders =  $orders->paginate($limit);
        
        $orders_arr = array();
        foreach ($orders as $order){
            $image = ExperienceMedia::where('experience_id',$order->experience_id)->where('type','img')->first();
            $temp = array();
            $temp['id'] = $order->booking_id;
            $temp['experience_id'] = $order->experience_id;
            $temp['custom_orderid'] = $order->custom_orderid;
            $temp['booking_date'] = $order->booking_date;
            $temp['schedule_time_id'] = $order->schedule_time_id;
            $temp['total_member'] = $order->total_member;
            $temp['total_amount'] = $order->total_amount;
            $temp['title'] = $order->title;
            $temp['full_name'] = $order->full_name;
            $temp['image'] = isset($image->thumb)?$image->thumb:"";
            
            array_push($orders_arr,$temp);
        }
        $data['orders'] = $orders_arr;
        $data['total_order'] = count($total_orders);
        return $this->sendResponseWithData($data,"Orders Retrieved Successfully.");
    }

    public function getMyOrders(Request $request){
        $limit = isset($request->limit)?$request->limit:10;
        $orders = Order::select('orders.id as booking_id','orders.*','experiences.title','experiences.full_name')->leftJoin('experiences', function($join) {
            $join->on('experiences.id', '=', 'orders.experience_id');
          })->leftJoin('users', function($join) {
            $join->on('experiences.user_id', '=', 'users.id');
          });
          if(isset($request->from_date) && $request->from_date != "" && isset($request->to_date) && $request->to_date != ""){
            $orders =  $orders->whereBetween('orders.booking_date', [$request->from_date, $request->to_date]);
          }
          if(isset($request->search) && $request->search != ""){
            $search =$request->search;
            $orders = $orders->where(function($query) use($search){
              $query->where('custom_orderid','LIKE',"%{$search}%")
                  ->orWhere('booking_date', 'LIKE',"%{$search}%")
                  ->orWhere('title', 'LIKE',"%{$search}%")
                  ->orWhere('full_name', 'LIKE',"%{$search}%");
              });  
          }
          $orders =  $orders->where('orders.user_id',Auth::user()->id);
          $total_orders =  $orders->get();
          $orders =  $orders->paginate($limit);
        
        $orders_arr = array();
        foreach ($orders as $order){
            $image = ExperienceMedia::where('experience_id',$order->experience_id)->where('type','img')->first();
            $temp = array();
            $temp['id'] = $order->id;
            $temp['experience_id'] = $order->experience_id;
            $temp['custom_orderid'] = $order->custom_orderid;
            $temp['booking_date'] = $order->booking_date;
            $temp['schedule_time_id'] = $order->schedule_time_id;
            $temp['total_member'] = $order->total_member;
            $temp['total_amount'] = $order->total_amount;
            $temp['title'] = $order->title;
            $temp['full_name'] = $order->full_name;
            $temp['image'] = isset($image->thumb)?$image->thumb:"";
            
            array_push($orders_arr,$temp);
        }
        $data['orders'] = $orders_arr;
        $data['total_order'] = count($total_orders);
        return $this->sendResponseWithData($data,"Orders Retrieved Successfully.");
    }

    public function getOrderCalender($month,$years){
       
        $number = cal_days_in_month(CAL_GREGORIAN, $month, $years);
        $order_check = [];
        for($i = 1; $i <= $number; $i++){
            //dd($i.'-'.$month.'-'.$years);
            $orderHost = Order::whereDay('booking_date', '=', $i)->whereMonth('booking_date', '=', $month)->whereYear('booking_date', '=', $years)->where('host_id', '=', Auth::user()->id)->get();
            $orderHostCount = $orderHost->count();

            $Myorder = Order::whereDay('booking_date', '=', $i)->whereMonth('booking_date', '=', $month)->whereYear('booking_date', '=', $years)->where('user_id', '=', Auth::user()->id)->get();
            $MyorderCount = $Myorder->count();
          
            $order_check1['day'] = $i;
            $order_check1['orderHost'] = $orderHostCount;
            $order_check1['Myorder'] = $MyorderCount;
            array_push($order_check, $order_check1); 
           
        }

        return $this->sendResponseWithData($order_check,"Order Calender Retrieved Successfully.");
    }

    public function getOrderDetails($id){
      
        $order = Order::where('id',$id)->first();
        if (!$order){
            return $this->sendError("Order Not Exist", "Not Found Error", []);
        }
        $experience = Experience::find($order->id);
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
        $datas = new ExperienceResource($experience);
       // dd($datas->proccess_page);
        // $orders_arr = array();
       
          
        $temp = array();
        $temp['id'] = $order->id;
        $temp['experience_id'] = $order->experience_id;
        $temp['custom_orderid'] = $order->custom_orderid;
        $temp['booking_date'] = $order->booking_date;
        $temp['schedule_time_id'] = $order->schedule_time_id;
        $temp['total_member'] = $order->total_member;
        $temp['total_amount'] = $order->total_amount;
        $temp['title'] = $order->title;
        $temp['full_name'] = $order->full_name;
            
        //     array_push($orders_arr,$temp);
        //dd($datas);

        $data['experience'] = $datas;
        $data['order'] = $temp;
     
        return $this->sendResponseWithData($data,"Orders Deatails Retrieved Successfully.");
    }

    function add_review(Request $request){
       
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'experience_id' => 'required',
            'customer_id' => 'required',
            'rating' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        $order = Order::find($request->order_id);
        //$old_order_status = $order->order_status;
        if (!$order){
            return $this->sendError("Order Item Not Exist", "Not Found Error", []);
        }

        $review_item = New Review();
        // if($request->hasFile('review_images')) {
        //     $review_images = array();
        //     foreach ($request->file('review_images') as $image) {
        //         $image_name = 'review_images_' . rand(111111, 999999) . time() . '.' . $image->getClientOriginalExtension();
        //         $destinationPath = public_path('images/review_images');
        //         $image->move($destinationPath, $image_name);
        //         array_push($review_images,'images/review_images/'.$image_name);
        //     }

        //     $review_item->review_imgs = implode(",",$review_images);
        // }

        // if ($request->hasFile('review_video')){
        //     $image = $request->file('review_video');
        //     $image_name = 'review_video_' . rand(111111, 999999) . time() . '.' . $image->getClientOriginalExtension();
        //     $destinationPath = public_path('images/review_videos');
        //     $image->move($destinationPath, $image_name);
        //     $review_item->review_video = 'images/review_videos/'.$image_name;
        // }
        $review_item->order_id = $request->order_id;
        $review_item->experience_id = $request->experience_id;
        $review_item->customer_id = $request->customer_id;
        $review_item->description = $request->description;
        $review_item->rating = $request->rating;
        $review_item->save();

        if($review_item){
            $order = Order::find($request->order_id);
        }

        
            
        return $this->sendResponseSuccess("Review Submitted Successfully");
    }
}
