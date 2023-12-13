<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{SingleOrdPayment, SupplierPayments};
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;

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

        $keyIndex = 1; // Live or Sandbox Index
        // $merchantKey = "864f4078-1517-4d26-af3a-63a8a4d29de7"; // Live Key
        $merchantKey = "099eb0cd-02cf-4e2a-8aca-3e6c6aff0399"; // Sandbox Key

        $client = new Client();

        $payload = array();
        $payload['merchantId'] = "PGTESTPAYUAT";
        $payload['merchantTransactionId'] = str_random(22);
        $payload['merchantUserId'] = $request['merchantUserId'];
        $payload['amount'] = $request['amount'] * 100;
        $payload['redirectUrl'] = $request['redirectUrl'];
        $payload['redirectMode'] = "REDIRECT";
        $payload['callbackUrl'] = "https://webhook.site/callback-url";
        $payload['mobileNumber'] = $request['mobileNumber'];
        $payload['paymentInstrument'] = array("type" => "PAY_PAGE");

        $encodedPayload = base64_encode(json_encode($payload, JSON_UNESCAPED_SLASHES));
        $xVerifyKey = hash('sha256', $encodedPayload . "/pg/v1/pay" . $merchantKey) . '###' . $keyIndex;

        $response = $client->request('POST', 'https://api-preprod.phonepe.com/apis/pg-sandbox/pg/v1/pay', [
            'body' => '{"request": "' . $encodedPayload . '"}',
            'headers' => [
                'Content-Type' => 'application/json',
                'accept' => 'application/json',
                'X-VERIFY' => $xVerifyKey
            ],
        ]);

        // return $response->response;
        return $response->getBody();
    }

}
