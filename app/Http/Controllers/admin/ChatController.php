<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{ProjectPage, User, Chat};
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        return view('admin.Chat.list');
    }

    public function FriendList()
    {
        return view('admin.Chat.friendList');
    }

    public function PersonalChat($receiverId, $userId)
    {
        $userReceiver = User::where('id', $receiverId)->first();
        $userSender = User::where('id', $userId)->first();
        return view('admin.Chat.personalChat', compact('receiverId','userId','userReceiver','userSender'));
    }

    public function getPersonalChat($receiverId, $userId, $limit, $page)
    {
        $get_all_chat = Chat::whereIn('user_id', [$userId, $receiverId])
                ->whereIn('receiver_id', [$userId, $receiverId])
                ->where('deleted_by', null)
                ->where('is_deleted', '0')
                ->orderBy('id', 'ASC')
                ->offset($page)
                ->limit($limit)
                ->get();

        foreach ($get_all_chat as $value) {
            $value->create_at = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $value->created_at)->format('d-m-Y H:i A');
        }
        return response($get_all_chat);
    }

    public function allFriendList(Request $request, $id)
    {
        $auth = Auth::user()->id;
        if ($request->ajax()) {
            $tab_type = $request->tab_type;
            if ($tab_type == "active_user_tab") {
                $estatus = 1;
            } elseif ($tab_type == "deactive_user_tab") {
                $estatus = 2;
            }

            $columns = array(
                0 => 'id',
                1 => 'profile_pic',
                2 => 'user_info',
                3 => 'contact_info',
                4 => 'login_info',
                5 => 'estatus',
                6 => 'created_at',
                7 => 'action',
            );

            $chatUsersReceiver = Chat::where('user_id', $id)->get()->pluck('receiver_id')->unique()->toarray();
            $chatUsersSender = Chat::where('receiver_id', $id)->get()->pluck('user_id')->unique()->toarray();
            $userBoth = array_merge($chatUsersReceiver,$chatUsersSender);
            $userUniqueBoth = array_unique($userBoth);

            $totalData = User::where('id', '!=', $auth)->whereIn('id', $userUniqueBoth);
            if (isset($estatus)) {
                $totalData = $totalData->where('estatus', $estatus);
            }
            $totalData = $totalData->count();

            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');


            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            if ($order == "id") {
                $order == "created_at";
                $dir = 'desc';
            }

            if (empty($request->input('search.value'))) {
                $users = User::with('designation')->whereIn('id', $userUniqueBoth);
                if (isset($estatus)) {
                    $users = $users->where('estatus', $estatus);
                }
                $users = $users->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();
            } else {
                $search = $request->input('search.value');
                $users = User::with('designation')->whereIn('id',  $userUniqueBoth);
                if (isset($estatus)) {
                    $users = $users->where('estatus', $estatus);
                }
                $users = $users->where(function ($query) use ($search) {
                    $query->where('id', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('mobile_no', 'LIKE', "%{$search}%")
                        ->orWhere('password', 'LIKE', "%{$search}%")
                        ->orWhere('created_at', 'LIKE', "%{$search}%");
                })
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();

                $totalFiltered = User::with('designation')->whereIn('id', $userUniqueBoth);
                if (isset($estatus)) {
                    $totalFiltered = $totalFiltered->where('estatus', $estatus);
                }
                $totalFiltered = $totalFiltered->where(function ($query) use ($search) {
                    $query->where('id', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('mobile_no', 'LIKE', "%{$search}%")
                        ->orWhere('password', 'LIKE', "%{$search}%")
                        ->orWhere('created_at', 'LIKE', "%{$search}%");
                })
                    ->count();
            }

            $data = array();

            if (!empty($users)) {
                foreach ($users as $user) {
                    $page_id = ProjectPage::where('route_url', 'admin.users.list')->pluck('id')->first();

                    if ($user->estatus == 1 && (getUSerRole() == 1 || (getUSerRole() != 1 && is_write($page_id)))) {
                        $estatus = '<label class="switch"><input type="checkbox" id="Userstatuscheck_' . $user->id . '" onchange="changeUserStatus(' . $user->id . ')" value="1" checked="checked"><span class="slider round"></span></label>';
                    } elseif ($user->estatus == 1) {
                        $estatus = '<label class="switch"><input type="checkbox" id="Userstatuscheck_' . $user->id . '" value="1" checked="checked"><span class="slider round"></span></label>';
                    }

                    if (isset($user->profile_pic) && $user->profile_pic != null) {
                        $profile_pic = $user->profile_pic;
                    } else {
                        $profile_pic = url('images/default_avatar.jpg');
                    }

                    if (isset($user->full_name)) {
                        $full_name = $user->full_name;
                    } else {
                        $full_name = "";
                    }

                    $user_info = '';
                    if (isset($full_name)) {
                        $user_info = '<a href=' . url('admin/personal-chat/'.$user->id.'/'.$id) . '><span> ' . $full_name . '</span></a>';
                    }
                    if (isset($user->designation)) {
                        $user_info .= '<span> ' . $user->designation->title . '</span>';
                    }

                    $nestedData['profile_pic'] = '<a href=' . url('admin/personal-chat/'.$user->id.'/'.$id) . '><img src="' . $profile_pic . '" width="50px" height="50px" alt="Profile Pic"></a>';
                    $nestedData['user_info'] = $user_info;
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
}
