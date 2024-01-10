<?php


use App\Models\{Category,ProjectPage};
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;


function getLeftMenuPages(){
    $pages = ProjectPage::where('parent_menu',0)->orderBy('sr_no','ASC')->get()->toArray();
    return $pages;
}

function getUSerRole(){
    return  \Illuminate\Support\Facades\Auth::user()->role;
}

function is_write($page_id){
    $is_write = \App\Models\UserPermission::where('user_id',\Illuminate\Support\Facades\Auth::user()->id)->where('project_page_id',$page_id)->where('can_write',1)->first();
    if ($is_write){
        return true;
    }
    return false;
}

function is_delete($page_id){
    $is_delete = \App\Models\UserPermission::where('user_id',\Illuminate\Support\Facades\Auth::user()->id)->where('project_page_id',$page_id)->where('can_delete',1)->first();
    if ($is_delete){
        return true;
    }
    return false;
}

function is_read($page_id){
    $is_read = \App\Models\UserPermission::where('user_id',\Illuminate\Support\Facades\Auth::user()->id)->where('project_page_id',$page_id)->where('can_read',1)->first();
    if ($is_read){
        return true;
    }
    return false;
}

function UploadImage($image, $path){
    $imageName = Str::random().'.'.$image->getClientOriginalExtension();
    $path = $image->move(public_path($path), $imageName);
    if($path == true){
        return $imageName;
    }else{
        return null;
    }
}


function compressImage($source, $destination, $quality) { 
    // Get image info 
    $imgInfo = getimagesize($source); 
    $mime = $imgInfo['mime']; 
     
    // Create a new image from file 
    switch($mime){ 
        case 'image/jpeg': 
            $image = @imagecreatefromjpeg($source); 
            break; 
        case 'image/png': 
            $image = @imagecreatefrompng($source); 
            break; 
        case 'image/gif': 
            $image = @imagecreatefromgif($source); 
            break; 
        default: 
            $image = @imagecreatefromjpeg($source); 
    } 
     
    // Save image 
    imagejpeg($image, $destination, $quality); 
     
    // Return compressed image 
    return $destination; 
}




function getExperienceStatus($experience_status){
    if($experience_status == 1){
        $experience_status = "Active";
        $class = "text-primary";
    }
    elseif($experience_status == 2){
        $experience_status = "Deactive";
        $class = "text-primary";
    }elseif($experience_status == 3){
        $experience_status = "Delete";
        $class = "text-primary";
    }elseif($experience_status == 4){
        $experience_status = "Pending";
        $class = "text-primary";
    }elseif($experience_status == 5){
        $experience_status = "Draft";
        $class = "text-info";
    }elseif($experience_status == 6){
        $experience_status = "Rejected";
        $class = "text-danger";
    }
   
    return ['experience_status' => $experience_status, 'class' => $class];
}

function getSubCategories($id){
    $category = \App\Models\Category::where('estatus',1)->where('parent_category_id',$id)->orderBy('sr_no','asc')->get();
    $catArray = array();
    foreach ($category as $cat){  
        $temp['id'] = $cat->id;
        $temp['name'] = $cat->category_name;
        $temp['category_thumb'] = $cat->category_thumb;
        $temp['child_category'] = getSubCategories($cat['id']);
        array_push($catArray,$temp);
    }
    return $catArray;
}

function send_sms($mobile_no, $otp){
    $url = 'https://www.smsgatewayhub.com/api/mt/SendSMS?APIKey=H26o0GZiiEaUyyy0kvOV5g&senderid=MADMRT&channel=2&DCS=0&flashsms=0&number=91'.$mobile_no.'&text=Welcome%20to%20Madness%20Mart,%20Your%20One%20time%20verification%20code%20is%20'.$otp.'.%20Regards%20-%20MADNESS%20MART&route=31&EntityId=1301164983812180724&dlttemplateid=1307165088121527950';
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));
    $response = curl_exec($curl);
    curl_close($curl);
//    echo $response;
}

function createSlug($title, $id = 0)
{
    $slug = str_slug($title);
    $allSlugs = getRelatedSlugs($slug, $id);
    if (! $allSlugs->contains('slug', $slug)){
        return $slug;
    }

    $i = 1;
    $is_contain = true;
    do {
        $newSlug = $slug . '-' . $i;
        if (!$allSlugs->contains('slug', $newSlug)) {
            $is_contain = false;
            return $newSlug;
        }
        $i++;
    } while ($is_contain);
}

function getRelatedSlugs($slug, $id = 0)
{
    return \App\Models\Experience::select('slug')->where('slug', 'like', $slug.'%')
    ->where('id', '<>', $id)
    ->get();
}


function checkExperienceStatus($new_status,$old_status){
    $new_status = getStatuNumber($new_status);
    $old_status = getStatuNumber($old_status);
    $status = false;
    if($new_status > $old_status){
        $status = true;
    }
    return $status;
}

function getStatuNumber($experience_status){
    $no = 0;
    if($experience_status == "TypePage"){
        $no = 1;
    }else if($experience_status == "LocationPage"){
        $no = 2;
    }elseif($experience_status == "CategoryPage"){
        $no = 3;
    }elseif($experience_status == "DetailsPage"){
        $no = 4;
    }elseif($experience_status == "MediaPage"){
        $no = 5;
    }elseif($experience_status == "AgePage"){
        $no = 6;
    }elseif($experience_status == "ProvideItemPage"){
        $no = 7;
    }elseif($experience_status == "BrindItemPage"){
        $no = 8;
    }elseif($experience_status == "MeetLocationPage"){
        $no = 9;
    }elseif($experience_status == "GroupSizePage"){
        $no = 10;
    }elseif($experience_status == "ScheduleTimePage"){
        $no = 11;
    }elseif($experience_status == "PricePage"){
        $no = 12;
    }elseif($experience_status == "DiscountPage"){
        $no = 13;
    }elseif($experience_status == "CancelletionPolicyPage"){
        $no = 14;
    }elseif($experience_status == "CategoryAttributePage"){
        $no = 15;
    }
   
    return $no;
}

function hostRating($id){
    $ReviewRating = \App\Models\Experience::where('user_id',$id)->where('rating','>',0)->avg('rating');
    //$ReviewRating = \App\Models\Review::whereIn('experience_id',$experiences)->avg('rating');
    return round($ReviewRating, 2);
}

function hostReviewMember($id){
    $ReviewRatingMember = \App\Models\Experience::where('user_id',$id)->where('review_total_user','>',0)->sum('review_total_user');
    return $ReviewRatingMember;
}

function is_like($post_id,$user_id = 0){
    if($user_id == 0){
       $user_id = \Illuminate\Support\Facades\Auth::user()->id;
    }
    $is_like = \App\Models\PostLike::where('user_id',$user_id)->where('post_id',$post_id)->first();
    if ($is_like){
        return true;
    }
    return false;
}

function is_commant($post_id,$user_id = 0){
    if($user_id == 0){
        $user_id = \Illuminate\Support\Facades\Auth::user()->id;
     }
    $is_like = \App\Models\PostCommant::where('user_id',$user_id)->where('post_id',$post_id)->first();
    if ($is_like){
        return true;
    }
    return false;
}

function follower($user_id){
    return $follower = \App\Models\UserFollower::where('following_id',$user_id)->get()->count();
}

function following($user_id){
    return $follower = \App\Models\UserFollower::where('user_id',$user_id)->get()->count();
}

function is_follower($user_id,$follower_id){
     $follower = \App\Models\UserFollower::where('user_id',$follower_id)->where('following_id',$user_id)->first();
     if($follower){
        return $status = $follower->estatus;
     }else{
        return $status = "";
     }
}

function is_follower_random($user_id,$follower_id){
    $follower = \App\Models\UserFollower::where('user_id',$user_id)->where('following_id',$follower_id)->first();
    if($follower){
       return $status = $follower->estatus;
    }else{
       return $status = "";
    }
}

function isFriend($user_id,$following_id) {
    $checkstatus = \App\Models\UserFollower::where(['user_id'=>$user_id,'following_id'=>$following_id])->orwhere(['user_id'=>$following_id,'following_id'=>$user_id])->where('follow_each_other',1)->first();
    if($checkstatus){
        return $status = $checkstatus->follow_each_other;
     }else{
        return $status = 0;
     }
}
