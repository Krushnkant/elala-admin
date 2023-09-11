<?php

namespace App\Http\Controllers\API;
use App\Models\ {ActivityLog, Order,ExperienceMedia,Experience,OrderSlot,Review,User,ExperienceProvideItem,ExperienceBrindItem,ExperienceDiscountRate,ExperienceScheduleTime,ExperienceLanguage,SingleOrdPayment,SupplierPayments};
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

    public function availableprivategroupdate(Request $request)
    {
        

        $validator = Validator::make($request->all(), [
            'experience_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);
        if($validator->fails()){
            return response()->json(['errors' => $validator->errors(),'status'=>'failed']);
        }

        $experience = Experience::where('id',$request->experience_id)->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }

        //dd($order_slot);

        //$number = cal_days_in_month(CAL_GREGORIAN, $request->booking_month, $request->booking_year);

        $startDate = new Carbon($request->start_date);
        $endDate = new Carbon($request->end_date);
        $all_dates = array();
        while ($startDate->lte($endDate)){
            $all_dates[] = $startDate->toDateString();
            $startDate->addDay();
        }
      
        $orders_arr = [];
        //for($i = 1; $i <= $number; $i++){
         foreach($all_dates as $paymentDate){    
           // dd($paymentDate);
            //$paymentDate = $i.'/'.$request->booking_month.'/'.$request->booking_year;
            $day = Carbon::createFromFormat('Y-m-d', $paymentDate)->format('l');
            //dump($day);
            $order_slot = OrderSlot::where(['experience_id'=>$request->experience_id])->whereYear('booking_date', '=', $request->booking_year)->whereMonth('booking_date', '=', $request->booking_month)->get()->pluck('schedule_time_id');
            $experiencescheduletimes = ExperienceScheduleTime::where(['experience_id'=>$request->experience_id,'day'=>$day])->whereNotIn('id', $order_slot)->get();
            
            foreach($experiencescheduletimes as $experiencescheduletime){
                $temp = array();
                $temp['id'] = $experiencescheduletime->id;
                $temp['experience_id'] = $experiencescheduletime->experience_id;
                $temp['day'] = $experiencescheduletime->day;
                $temp['start_time'] = $experiencescheduletime->time;
                $time1 = Carbon::parse($experiencescheduletime->time);
                $endTime = $time1->addMinutes(isset($experience->duration)?$experience->duration:0);
                $temp['end_time'] = $endTime->format('H:i:s');
                $temp['individual_rate'] = $experience->individual_rate;
                $temp['min_private_group_rate'] = $experience->min_private_group_rate;
                $date = $paymentDate;
                $temp['date'] = date('d-m-Y', strtotime($date)); 
                array_push($orders_arr,$temp);
            }
        }
        return $this->sendResponseWithData($orders_arr,"Private Group Slot Retrieved Successfully.");
           
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
        $order->is_group_order = isset($request->is_group_order) ? $request->is_group_order : 0;
        $order->save();
        $ActivityLog = ActivityLog::create([
            "title"=>"Order Create",
            "old_data"=>$order,
            "type"=>3,
            "action"=>1,
            "item_id"=> $order->id,
            "user_id"=>Auth::user()->id,
        ]);
        $days = 7;
        if($order){
           
            $dt = Carbon::now()->addDays($days);
            $dt =  $dt->toDateString();

            $user = User::where('id',$experience->user_id)->first();
            if($user){
                $user->out_stand_amt = (int)$user->out_stand_amt + (int)$order->total_amount;
                $user->save();  
            }

            $supplierPayments = SupplierPayments::where('payment_date',$dt)->where('host_id',$experience->user_id)->first();
            if(!$supplierPayments){
                $supplierPayments = new SupplierPayments();
                $supplierPayments->host_id = $experience->user_id;
                $supplierPayments->total_amt = $order->total_amount;
                $supplierPayments->payment_date = $dt;
                $supplierPayments->save();  
                ActivityLog::create([
                    "title"=>"Experience Supplier Payments",
                    "old_data"=>$order,
                    "type"=>3,
                    "action"=>1,
                    "item_id"=> $order->id,
                    "user_id"=>$experience->user_id,
                ]);
            }else{
                $ActivityLog=ActivityLog::create([
                    "title"=>"Experience Supplier Payments update",
                    "old_data"=>$supplierPayments,
                    "type"=>3,
                    "action"=>1,
                    "item_id"=> $supplierPayments->id,
                    "user_id"=>$experience->user_id,
                ]);
                $supplierPayments->total_amt = (int)$supplierPayments->total_amt + (int)$order->total_amount;
                $supplierPayments->save();
                ActivityLog::where('id',$ActivityLog->id)->update([
                    "new_data"=>$supplierPayments,
                ]);
            }
            
            if($supplierPayments){
                $singleOrdPayment = new SingleOrdPayment();
                $singleOrdPayment->payment_id = 1;
                $singleOrdPayment->order_id = $order->id;
                $singleOrdPayment->total_amt = $order->total_amount;
                $singleOrdPayment->save();
                $ActivityLog=ActivityLog::create([
                    "title"=>"Experience Single Order Payment",
                    "old_data"=>$supplierPayments,
                    "type"=>3,
                    "action"=>1,
                    "item_id"=> $supplierPayments->id,
                    "user_id"=>$experience->user_id,
                ]);
            }

        }

        return $this->sendResponseSuccess("Order Submitted Successfully");
    }

    public function getHostOrders(Request $request){
        $limit = isset($request->limit)?$request->limit:10;
        $orders = Order::select('orders.id as booking_id','orders.*','experiences.title','users.full_name','users.id as uid')->leftJoin('experiences', function($join) {
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
            $temp['user_id'] = $order->uid;
            $temp['full_name'] = $order->full_name;
            $temp['is_group_order'] = $order->is_group_order;
            $temp['image'] = isset($image->thumb)?'images/experience_images_thumb/'.$image->thumb:"";
            
            array_push($orders_arr,$temp);
        }
        $data['orders'] = $orders_arr;
        $data['total_order'] = count($total_orders);
        return $this->sendResponseWithData($data,"Orders Retrieved Successfully.");
    }

    public function getMyOrders(Request $request){
        $limit = isset($request->limit)?$request->limit:10;
        $orders = Order::select('orders.id as booking_id','orders.*','experiences.title','users.full_name','users.id as uid')->leftJoin('experiences', function($join) {
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
            $temp['user_id'] = $order->uid;
            $temp['full_name'] = $order->full_name;
            $temp['is_group_order'] = $order->is_group_order;
            $temp['image'] = isset($image->thumb)?'images/experience_images_thumb/'.$image->thumb:"";
            
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

    public function getOrderDetails($id,Request $request){
      
        $order = Order::with('orderslot')->where('id',$id)->first();
        if (!$order){
            return $this->sendError("Order Not Exist", "Not Found Error", []);
        }
        $experience = Experience::find($order->experience_id);
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }

        $ProvideItem = ExperienceProvideItem::where('experience_id',$experience->id)->get(['id','title']);
        $BrindItem = ExperienceBrindItem::where('experience_id',$experience->id)->get(['id','title']);
        $Images = ExperienceMedia::where('experience_id',$experience->id)->where('type','img')->get(['id','thumb']);
        $Videos = ExperienceMedia::where('experience_id',$experience->id)->where('type','video')->get(['id','thumb']);

        $ExperienceLanguage = ExperienceLanguage::with('language')->where('experience_id',$experience->id)->get();
        $lan_titles = array();
        foreach($ExperienceLanguage as $ExLanguage){
             $lan_titles[] = $ExLanguage->language->title;
        }
        $lan_string = implode(',',$lan_titles);
        $is_in_wishlist = false;
        if(isset($request->user_id) && $request->user_id!=0 && $request->user_id!="") {
            $wishlist = \App\Models\Wishlist::where('user_id',$request->user_id)->where('experience_id',$experience->id)->first();
            if ($wishlist){
                $is_in_wishlist = true;
            }
        }

        $hostUsers = User::where('id',$experience->user_id)->first();
        if($hostUsers){
            $host['id'] = $hostUsers->id;
            $host['full_name'] = $hostUsers->full_name;
            $host['bio'] = $hostUsers->bio;
            $host['profile_pic'] = $hostUsers->profile_pic;
            $host['rating'] = hostRating($hostUsers->id);
            $host['rating_member'] = hostReviewMember($hostUsers->id);
            $host['created_at'] = $hostUsers->created_at;
        }else{
            $host = "";
        }

        $media_array = array();
        if($experience['image'] != ""){
            $media_array[0]['id'] = 0;
            $media_array[0]['thumb'] = 'images/experience_images/'.$experience['image'];
            $media_array[0]['type'] = 'img';
        }
        foreach($Images as $media){
            $temp = array();
            $temp['id'] = $media['id'];
            $temp['thumb'] = 'images/experience_images/'.$media['thumb'];
            $temp['type'] = $media['type'];
            array_push($media_array,$temp);
        }

        $video_array = array();
        foreach($Videos as $media){
            $temp = array();
            $temp['id'] = $media['id'];
            $temp['thumb'] = 'images/experience_videos/'.$media['thumb'];
            $temp['type'] = $media['type'];
            array_push($video_array,$temp);
        }
  
        $experienceData =  [
            'id' => $experience->id,
            'slug' => $experience->slug,
            'type' => $experience->type,
            'location' => $experience->location,
            'latitude' => $experience->latitude,
            'longitude' => $experience->longitude,
            'category_id' => $experience->category_id,
            'title' => $experience->title,
            'description' => $experience->description,
            'images' => $media_array,
            'videos' => $video_array,
            'duration' => $experience->duration,
            'age_limit' => explode(',',$experience->age_limit),
            'provide_items' => $ProvideItem,
            'is_bring_item' => $experience->is_bring_item,
            'brind_items' => $BrindItem,
            'is_meet_address' => $experience->is_meet_address,
            'meet_address' => $experience->meet_address,
            'meet_address_flat_no' => $experience->meet_address_flat_no,
            'meet_city' => $experience->meet_city,
            'meet_state' => $experience->meet_state,
            'meet_country' => $experience->meet_country,
            'pine_code' => $experience->pine_code,
            'meet_latitude' => $experience->meet_latitude,
            'meet_longitude' => $experience->meet_longitude,
            'max_member_public_group_size' => $experience->max_member_public_group_size,
            'max_member_private_group_size' => $experience->max_member_private_group_size,
            'individual_rate' => $experience->individual_rate,
            'min_private_group_rate' => $experience->min_private_group_rate,
            'experience_language' => $lan_string,
            'cancellation_policy_id' => $experience->cancellation_policy_id,
            'rating' => $experience->rating,
            'rating_member' => $experience->review_total_user,
            'estatus' => $experience->estatus,
            'host' => $host,
            'is_in_wishlist' => $is_in_wishlist
        ];
        $orderData = array();
        $orderData['id'] = $order->id;
        $orderData['experience_id'] = $order->experience_id;
        $orderData['custom_orderid'] = $order->custom_orderid;
        $orderData['booking_date'] = $order->booking_date;
        $orderData['schedule_time'] =$order->orderslot->time;
        $orderData['adults_member'] = $order->adults;
        $orderData['children_member'] = $order->children;
        $orderData['infants_member'] = $order->infants;
        $orderData['total_member'] = $order->total_member;
        $orderData['adults_amount'] = $order->adults_amount;
        $orderData['children_amount'] = $order->children_amount;
        $orderData['infants_amount'] = $order->infants_amount;
        $orderData['total_amount'] = $order->total_amount;
        $orderData['experience'] = $experienceData;
        $orderData['review'] = Review::select('id','rating','description')->where('order_id',$id)->get()->toArray();
        $orderData['create_date'] = $order->created_at;

        //$data['order'] = $orderData;
       //$data['experience'] = $experienceData;

        return $this->sendResponseWithData($orderData,"Orders Deatails Retrieved Successfully.");
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
        $review_item->order_id = $request->order_id;
        $review_item->experience_id = $request->experience_id;
        $review_item->customer_id = $request->customer_id;
        $review_item->description = $request->description;
        $review_item->rating = $request->rating;
        $review_item->save();

       
        return $this->sendResponseSuccess("Review Submitted Successfully");
    }

   
}
