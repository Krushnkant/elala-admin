<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{SingleOrdPayment, SupplierPayments};
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
// use GuzzleHttp\Client;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;
use App\Models\Experience;
use App\Models\OrderSlot;
use App\Models\ActivityLog;
use App\Models\User;

class PaymentController extends BaseController
{
    public function paymentHistory(Request $request)
    {

        $NextSupplierPayments = SupplierPayments::whereDate('payment_date', '>=', Carbon::today())->where('host_id', $user_id = Auth::user()->id)->where('payment_status', 0)->first(['id', 'total_amt', 'payment_date']);
        // if(!$NextSupplierPayments){
        //     $NextSupplierPayments = SupplierPayments::whereDate('payment_date', Carbon::tomorrow())->where('host_id', $user_id = Auth::user()->id)->first(['id','total_amt']);
        // }
        $Payment['nextPayment'] = $NextSupplierPayments;

        $LastSupplierPayments = SupplierPayments::whereDate('payment_date', '<=', Carbon::today())->where('host_id', $user_id = Auth::user()->id)->where('payment_status', 1)->first(['id', 'total_amt', 'payment_date']);
        // if(!$LastSupplierPayments){
        //     $LastSupplierPayments = SupplierPayments::whereDate('payment_date', Carbon::tomorrow())->where('host_id', $user_id = Auth::user()->id)->first(['id','total_amt']);
        // }

        $Payment['lastPayment'] = $LastSupplierPayments;
        $PassLastSupplierPayments = SupplierPayments::whereDate('payment_date', '<=', Carbon::today())->where('host_id', $user_id = Auth::user()->id)->where('payment_status', 1)->sum('total_amt');
        $UpcomingLastSupplierPayments = SupplierPayments::whereDate('payment_date', '>=', Carbon::today())->where('host_id', $user_id = Auth::user()->id)->where('payment_status', 0)->sum('total_amt');
        $Payment['pastPayment'] = $PassLastSupplierPayments;
        $Payment['upcomingPayment'] = $UpcomingLastSupplierPayments;
        return $this->sendResponseWithData($Payment, "Payment Retrieved Successfully.");
    }
    public function nextlastpayment(Request $request)
    {
        $limit = isset($request->limit) ? $request->limit : 10;
        $SingleOrdPayments = SingleOrdPayment::where('payment_id', $request->payment_id)->paginate($limit);
        $SingleOrdPayments_arr = array();
        foreach ($SingleOrdPayments as $SingleOrdPayment) {
            $temp = array();
            $temp['payment_date'] = $SingleOrdPayment->created_at;
            $temp['net_payment'] = $SingleOrdPayment->total_amt;
            $temp['final_amount'] = $SingleOrdPayment->total_amt;
            array_push($SingleOrdPayments_arr, $temp);
        }
        $data['next_payments'] = $SingleOrdPayments_arr;
        $data['total_payment'] = count(SingleOrdPayment::where('id', $request->payment_id)->get());
        return $this->sendResponseWithData($data, "Payment Retrieved Successfully.");
    }

    public function pastpayment(Request $request)
    {
        $limit = isset($request->limit) ? $request->limit : 10;
        $SingleOrdPayments = SupplierPayments::whereDate('payment_date', '<=', Carbon::today())->where('payment_status', 1)->paginate($limit);
        $SingleOrdPayments_arr = array();
        foreach ($SingleOrdPayments as $SingleOrdPayment) {
            $temp = array();
            $temp['payment_date'] = $SingleOrdPayment->payment_date;
            $temp['net_payment'] = $SingleOrdPayment->total_amt;
            $temp['final_amount'] = $SingleOrdPayment->total_amt;
            array_push($SingleOrdPayments_arr, $temp);
        }
        $data['last_payments'] = $SingleOrdPayments_arr;
        $data['total_payment'] = count(SupplierPayments::whereDate('payment_date', '<=', Carbon::today())->where('payment_status', 1)->get());
        return $this->sendResponseWithData($data, "Past Payment Retrieved Successfully.");
    }

    public function upcomingpayment(Request $request)
    {
        $limit = isset($request->limit) ? $request->limit : 10;
        $SingleOrdPayments = SupplierPayments::whereDate('payment_date', '>=', Carbon::today())->where('payment_status', 0)->paginate($limit);
        $SingleOrdPayments_arr = array();
        foreach ($SingleOrdPayments as $SingleOrdPayment) {
            $temp = array();
            $temp['id'] = $SingleOrdPayment->id;
            $temp['payment_date'] = $SingleOrdPayment->payment_date;
            $temp['net_payment'] = $SingleOrdPayment->total_amt;
            $temp['final_amount'] = $SingleOrdPayment->total_amt;
            array_push($SingleOrdPayments_arr, $temp);
        }
        $data['upcomimg_payments'] = $SingleOrdPayments_arr;
        $data['total_payment'] = count(SupplierPayments::whereDate('payment_date', '>=', Carbon::today())->where('payment_status', 0)->get());
        return $this->sendResponseWithData($data, " Upcoming Payment Retrieved Successfully.");
    }

    public function pastupcomingpayment(Request $request)
    {
        $limit = isset($request->limit) ? $request->limit : 10;
        $SingleOrdPayments = SingleOrdPayment::with('order.experience')->where('payment_id', $request->payment_id)->paginate($limit);
        $SingleOrdPayments_arr = array();
        foreach ($SingleOrdPayments as $SingleOrdPayment) {
            $temp = array();
            $temp['payment_date'] = $SingleOrdPayment->created_at;
            $temp['net_payment'] = $SingleOrdPayment->total_amt;
            $temp['final_amount'] = $SingleOrdPayment->total_amt;
            $temp['order'] = $SingleOrdPayment->order;
            array_push($SingleOrdPayments_arr, $temp);
        }
        $data['payments'] = $SingleOrdPayments_arr;
        $data['total_payment'] = count(SingleOrdPayment::where('id', $request->payment_id)->get());
        return $this->sendResponseWithData($data, "Payment Retrieved Successfully.");
    }

    public function payment_initiate(Request $request)
    {


        $info=$request->all();


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

        $res =  $this->createorder($info);

        if($res['status']){
            Log::info($res['order_id']);
        $keyIndex = 1; // Live or Sandbox Index
        $merchantKey = "864f4078-1517-4d26-af3a-63a8a4d29de7"; // Live Key
        // $merchantKey = "099eb0cd-02cf-4e2a-8aca-3e6c6aff0399"; // Sandbox Key
          $payload = array();
          $payload['merchantId'] = "PGTESTPAYUAT";
          $payload['merchantTransactionId'] = (string)$res['order_id'];
          $payload['merchantUserId'] = $request['merchantUserId'];
          $payload['amount'] = $request['total_amount'] * 100;
          $payload['redirectUrl'] = route('response');
          $payload['redirectMode'] = "POST";
          $payload['callbackUrl'] = route('response');
          $payload['mobileNumber'] = $request['mobileNumber'];
          $payload['paymentInstrument'] = array("type" => "PAY_PAGE");

          $encodedPayload = base64_encode(json_encode($payload));
          $xVerifyKey = hash('sha256', $encodedPayload . "/pg/v1/pay" . $merchantKey) . '###' . $keyIndex;

          $response = Curl::to(env('PAYMENT_API_URL'))
                  ->withHeader('Content-Type:application/json')
                  ->withHeader('X-VERIFY:'.$xVerifyKey)
                  ->withData(json_encode(['request' => $encodedPayload]))
                  ->post();
                  $rData = json_decode($response);
            // Log::info(["logggggggggg"=>$rData]);
            // Log::info(["logggggggggg"=>env('PAYMENT_API_URL')]);
          return $this->sendResponseWithData($rData->data->instrumentResponse->redirectInfo->url, "Payment Retrieved Successfully.");
        }

    }
    public function response(Request $request)
    {
        $input = $request->all();

        $saltKey = '099eb0cd-02cf-4e2a-8aca-3e6c6aff0399';
        $saltIndex = 1;

        $finalXHeader = hash('sha256','/pg/v1/status/'.$input['merchantId'].'/'.$input['transactionId'].$saltKey).'###'.$saltIndex;

        $response = Curl::to(env('PAYMENT_RESPONSE_URL').$input['merchantId'].'/'.$input['transactionId'])
                ->withHeader('Content-Type:application/json')
                ->withHeader('accept:application/json')
                ->withHeader('X-VERIFY:'.$finalXHeader)
                ->withHeader('X-MERCHANT-ID:'.$input['transactionId'])
                ->get();
        $data=json_decode($response);
        if($data->success==true){
           $orderId = (int)$data->data->merchantTransactionId;
           $order=Order::where('id',$orderId)->where('payment_verify',0)->first();
           if($order){

           $order_slot = OrderSlot::where(['experience_id'=>$order->experience_id,'booking_date'=>$order->booking_date,'schedule_time_id'=>$order->schedule_time_id])->first();
           if($order_slot){
               $order_slot->total_member = $order_slot->total_member + $order->total_member;
           }else{
               $order_slot = New OrderSlot();
               $order_slot->experience_id = $order->experience_id;
               $order_slot->booking_date = $order->booking_date;
               $order_slot->schedule_time_id = $order->schedule_time_id;
               $order_slot->total_member = $order->total_member;
           }
           $order_slot->save();
           $ActivityLog = ActivityLog::create([
                "title"=>"Order Create",
                "old_data"=>$order,
                "type"=>3,
                "action"=>1,
                "item_id"=> $order->id,
                "user_id"=>$order->user_id,
            ]);
            $days = 7;
            if($order){
                 $experience = Experience::where('id',$order->experience_id)->first();

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
                $order->payment_verify=1;
                $order->payment_response=$response;
                $order->save();
                return redirect()->away(env("PAYMENT_SUCCESS_URL"));
            }
           }else{
            return redirect()->away(env("PAYMENT_FAIL_URL"));
            }
        }else{
            return redirect()->away(env("PAYMENT_FAIL_URL"));
        }

    }
    public function createorder($request)
    {

        Log::info($request);

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
        $experience = Experience::where('id',$request['experience_id'])->first();
        if (!$experience){
            return $this->sendError("Experience Not Exist", "Not Found Error", []);
        }
        $order = new Order();
        $order->user_id = Auth::user()->id;
        $order->custom_orderid = Carbon::now()->format('ymd') . $last_order_id;
        $order->experience_id = $request['experience_id'];
        $order->host_id = $experience->user_id;
        $order->booking_date = $request['booking_date'];
        $order->schedule_time_id = $request['schedule_time_id'];
        $order->adults = $request['adults_member'];
        $order->children = $request['children_member'];
        $order->infants = $request['infants_member'];
        $order->total_member = $request['total_member'];
        $order->adults_amount = $request['adults_amount'];
        $order->children_amount = $request['children_amount'];
        $order->infants_amount = $request['infants_amount'];
        $order->total_amount = $request['total_amount'];
        $order->payment_type = isset($request['payment_type']) ? $request['payment_type'] : 2;
        $order->payment_transaction_id = isset($request['payment_transaction_id']) ? $request['payment_transaction_id'] : '';
        $order->payment_currency = isset($request['payment_currency']) ? $request['payment_currency'] : 'INR';
        $order->gateway_name = isset($request['gateway_name']) ? $request['gateway_name'] : '';
        $order->payment_mode = isset($request['payment_mode']) ? $request['payment_mode'] : '';
        $order->payment_date = isset($request['payment_date']) ? $request['payment_date'] : '';
        $order->is_group_order = isset($request['is_group_order']) ? $request['is_group_order'] : 0;
        $order->save();

        return ['status'=>true,'order_id'=>$order->id];
    }
}
