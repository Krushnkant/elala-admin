<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Experience;
use App\Models\User;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    private $page = "Review";

    public function index(){
        $action = "list";
        return view('admin.review.list',compact('action'))->with('page',$this->page);
    }

    public function allReviewlist(Request $request){
        if ($request->ajax()) {
            $columns = array(
                0 =>'sr_no',
                1 =>'experice',
                2 => 'user',
                3 => 'review_text',
                4 => 'review_rating',
                5 => 'created_at',
                6 => 'action',
            );

            $tab_type = $request->tab_type;
            if ($tab_type == "fake_review_tab"){
                $order_status = 1;
            }
            elseif ($tab_type == "real_review_tab"){
                $order_status = 2;
            }

            $totalData = Review::count();
            if (isset($order_status) && $order_status == 1){
                $totalData = Review::where('user_id',1)->count();
            }else if(isset($order_status) && $order_status == 2){
                $totalData = Review::where('user_id','<>',1)->count();
            }


            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            if($order == "sr_no"){
                $order = "created_at";
                $dir = 'desc';
            }

            if(empty($request->input('search.value')))
            {
                $reviews = Review::where('estatus',1);
                if (isset($order_status) && $order_status == 1){
                    $reviews = $reviews->where('user_id',1);
                }else if(isset($order_status) && $order_status == 2){
                    $reviews = $reviews->where('user_id','<>',1);
                }
                $reviews = $reviews->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();
              
            }
            else {
                $search = $request->input('search.value');
                $reviews =  Review::where('estatus',1);
                if (isset($order_status) && $order_status == 1){
                    $reviews = $reviews->where('user_id',1);
                }else if(isset($order_status) && $order_status == 2){
                    $reviews = $reviews->where('user_id','<>',1);
                }
                $reviews = $reviews->Where('reviewer', 'LIKE',"%{$search}%")
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();
                $totalFiltered = Review::Where('reviewer', 'LIKE',"%{$search}%")
                    ->count();
            }
            //dd($reviews);
            $data = array();

            if(!empty($reviews))
            {
                foreach ($reviews as $review)
                {
                    
                    $userdata = User::where('id',$review->customer_id)->first();
                    $experience = Experience::where('id',$review->experience_id)->first();
    
                    $action='';
                    if($review->status == 0){
                        $action .= '<button id="AcceptBtn" onclick="acceptstatus('. $review->id .')" class="btn btn-gray text-blue btn-sm" data-id="' .$review->id. '">Accept</button>';
                        $action .= '<button id="Reject" onclick="rejectstatus('. $review->id .')" class="btn btn-gray text-blue btn-sm" data-id="' .$review->id. '">Reject</button>';
                    }else if($review->status == 1){
                        $action .= 'Accept';
                    }else{
                        $action .= 'Reject';
                    }
                  
                    $nestedData['experice'] = $experience->title;
                    $nestedData['user'] = $userdata->full_name;
                    $nestedData['review_text'] = $review->description;
                    $nestedData['review_rating'] = $review->rating .' <i class="fa fa-star checked"></i>';
                   // $nestedData['estatus'] = $estatus;
                    $nestedData['created_at'] = date('d-m-Y h:i A', strtotime($review->created_at));
                    $nestedData['action'] = $action;
                    $data[] = $nestedData;
                }
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

    public function rejectstatus($id){
        $review = Review::find($id);
        $review->status = 2;
        $review->save();
        return response()->json(['status' => '200']);
       
    }

    public function acceptstatus($id){
        $review = Review::find($id);
        $review->status = 1;
        $review->save();
        
        if($review){
            $experience = Experience::find($review->experience_id);
            if($experience){
                $avgStar = Review::where('status',1)->avg('rating');
                $experience->rating = $avgStar;
                $experience->review_total_user = $experience->review_total_user + 1;
                $experience->save();
            }
        }
            
            // $productvariant = Experience::find($review->experience_id);
            // if($productvariant){
            //     $product_rating = (($productvariant->total_rate_value + $review->rating)/($productvariant->review_total_user + 1));
            //     $productvariant->total_review = $productvariant->total_review + 1;
            //     $productvariant->total_rate_value = $productvariant->total_rate_value + $review->rating;
            //     $productvariant->product_rating = $product_rating;
            //     $productvariant->save();
            // }
        

        return response()->json(['status' => '200']);
       
    }
}
