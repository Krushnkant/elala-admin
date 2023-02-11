@extends('admin.layout')

@section('content')
<style>
    .receiver_get_personal_chat_list {
        width: 50%;
        float: left;
    }

    .sender_get_personal_chat_list {
        width: 50%;
        float: right;
    }

    .get_personal_chat_list {
        width: 100%;
        float: left;
    }

    span.sender_data,
    span.receiver_data {
        display: block;
    }

    .heading h2 {
        width: 50%;
        float: left;
        font-size: 18px;
        text-transform: capitalize;
    }

    span.receiver_data.text-left p {
        background: #cfcfcf;
        padding: 5px 10px;
        display: inline-block;
        border-radius: 10px;
        color: #000;
        margin-bottom: 5px;
    }

    span.sender_data.text-right p {
        background: #bbc2ef;
        padding: 5px 10px;
        display: inline-block;
        border-radius: 10px;
        color: #000;
        margin-bottom: 5px;
    }

    .get_personal_chat_list span span {
        display: block;
    }
</style>
<div class="row page-titles mx-0">
    <div class="col p-md-0">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ url('admin/users') }}">User List</a></li>
            <li class="breadcrumb-item active"><a href="javascript:void(0)">Personal Chat List</a></li>
        </ol>
    </div>
</div>
<!-- row -->

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="tab-pane fade show active table-responsive" id="all_user_tab">
                        <div class="heading w-100 float-left">
                            <h2 class="text-left">
                                <img src="" class="profile_img" />
                                {{$userSender->full_name}}
                            </h2>
                            <h2 class="text-right">
                                <img src="" class="profile_img" />
                                {{$userReceiver->full_name}}
                            </h2>
                        </div>
                        <div class="get_personal_chat_list">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<!-- user list JS start -->
<script type="text/javascript">
    var limit = 20;
    var page = 0;
    var url = "{{url('/')}}";
    var receiverId = "{{$receiverId}}";
    var userId = "{{$userId}}";

    $(document).ready(function() {
        GetPersonalChatList(receiverId, userId, limit, page)
    })

    function GetPersonalChatList(receiverId, userId, limit, page) {
        $.ajax({
            url: url + '/admin/getpersonallist/' + receiverId + '/' + userId + '/' + limit + '/' + page,
            type: "POST",
            dataType: 'JSON',
            data: {
                "_token": "{{ csrf_token() }}"
            },
            beforeSend: function() {
                console.log("loader")
            },
            success: function(response) {
                if (response.length > 0) {
                    let receiver = '';
                    let sender = '';
                    response.forEach(element => {
                        if (Number(userId) == element?.user_id) {
                            receiver = '<span class="receiver_data text-left"><p>' + element?.message_text + '</p><span>' + element?.create_at + '</span></span>'
                        } else {
                            receiver = '<span class="sender_data text-right"><p>' + element?.message_text + '</p><span>' + element?.create_at + '</span></span>'
                        }
                        $(".get_personal_chat_list").append(receiver)
                    });
                }
            },
            error: function(response) {
                console.log("error---", response)
            }
        })
    }

    $(window).scroll(function() {
        if ($(window).scrollTop() == $(document).height() - $(window).height()) {
            page++;
            GetPersonalChatList(receiverId, userId, limit, page)
        }
    });
</script>
<!-- user list JS end -->
@endsection