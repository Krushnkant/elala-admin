@extends('admin.layout')

@section('content')
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Post</a></li>
            </ol>
        </div>
    </div>
    <!-- row -->

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        @if(isset($action) && $action=='list')
                            <div class="table-responsive">
                                <table id="post" class="table zero-configuration customNewtable" style="width:100%">
                                    <thead>
                                    <tr>
                                        <th>Sr. No</th>
                                        <th>Post</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tfoot>
                                    <tr>
                                        <th>Sr. No</th>
                                        <th>Post</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endif

                        @if(isset($action) && $action=='edit')
                            @include('admin.posts.edit')
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="DeletepostModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Remove post</h5>
                </div>
                <div class="modal-body">
                    Are you sure you wish to remove this post?
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal" type="button">Cancel</button>
                    <button class="btn btn-danger" id="RemovepostSubmit" type="submit">Remove <i class="fa fa-circle-o-notch fa-spin removeloadericonfa" style="display:none;"></i></button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<!-- post JS start -->
<script type="text/javascript">

$('#host_tag').select2({
    width: '100%',
    placeholder: "Select Host",
    allowClear: true
}).trigger('change');   

 
$('#tag_friends').select2({
    width: '100%',
    multiple: true,
    placeholder: "Select Friends",
    allowClear: true,
    autoclose: false,
    closeOnSelect: false,
});


$(document).ready(function() {
    post_table(true);
});

$('body').on('click', '#AddpostBtn', function () {
    location.href = "{{ route('admin.posts.add') }}";
});

$('body').on('click', '#save_closepostBtn', function () {
    save_post($(this),'save_close');
});

$('body').on('click', '#save_newpostBtn', function () {
    save_post($(this),'save_new');
});

function save_post(btn,btn_type){
    $(btn).prop('disabled',true);
    $(btn).find('.loadericonfa').show();
    var action  = $(btn).attr('data-action');

    var formData = new FormData($("#postCreateForm")[0]);
    formData.append('action',action);

    $.ajax({
        type: 'POST',
        url: "{{ route('admin.posts.save') }}",
        data: formData,
        processData: false,
        contentType: false,
        success: function (res) {
            if(res.status == 'failed'){
                $(btn).prop('disabled',false);
                $(btn).find('.loadericonfa').hide();

                if (res.errors.description) {
                    $('#description-error').show().text(res.errors.description);
                } else {
                    $('#description-error').hide();
                }

                if (res.errors.catImg) {
                    $('#postthumb-error').show().text(res.errors.catImg);
                } else {
                    $('#postthumb-error').hide();
                }
            }

            if(res.status == 200){
                if(btn_type == 'save_close'){
                    $(btn).prop('disabled',false);
                    $(btn).find('.loadericonfa').hide();
                    location.href="{{ route('admin.posts.list')}}";
                    if(res.action == 'add'){
                        toastr.success("post Added",'Success',{timeOut: 5000});
                    }
                    if(res.action == 'update'){
                        toastr.success("post Updated",'Success',{timeOut: 5000});
                    }
                }
                if(btn_type == 'save_new'){
                    $(btn).prop('disabled',false);
                    $(btn).find('.loadericonfa').hide();
                    $('#postCreateForm').trigger("reset");
                    location.href="{{ route('admin.posts.add')}}";
                    if(res.action == 'add'){
                        toastr.success("post Added",'Success',{timeOut: 5000});
                    }
                    if(res.action == 'update'){
                        toastr.success("post Updated",'Success',{timeOut: 5000});
                    }
                }
            }

        },
        error: function (data) {
            $(btn).prop('disabled',false);
            $(btn).find('.loadericonfa').hide();
            toastr.error("Please try again",'Error',{timeOut: 5000});
        }
    });
}

function post_table(is_clearState=false){
    var cat_id = "{{ isset($id)?$id:0 }}";
    if(is_clearState){
        $('#post').DataTable().state.clear();
    }

    $('#post').DataTable({
        "destroy": true,
        "processing": true,
        "serverSide": true,
        'stateSave': function(){
            if(is_clearState){
                return false;
            }
            else{
                return true;
            }
        },
        "ajax":{
            "url": "{{ url('admin/allpostlist') }}",
            "dataType": "json",
            "type": "POST",
            "data":{ _token: '{{ csrf_token() }}',cat_id:cat_id },
            // "dataSrc": ""
        },
        'columnDefs': [
            { "width": "5px", "targets": 0 },
            { "width": "120px", "targets": 1 },
            { "width": "50px", "targets": 2 },
            { "width": "10px", "targets": 3 },
        ],
        "columns": [
            {data: 'sr_no', name: 'sr_no', class: "text-center", orderable: false,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {data: 'post', name: 'post', orderable: false, searchable: false, class: "text-left"},
            {data: 'estatus', name: 'estatus', orderable: false, searchable: false, class: "text-center"},
            {data: 'action', name: 'action', orderable: false, searchable: false, class: "text-center"},
        ]
    });
}

function chagepostStatus(post_id) {
    $.ajax({
        type: 'GET',
        url: "{{ url('admin/changepoststatus') }}" +'/' + post_id,
        success: function (res) {
            if(res.status == 200 && res.action=='deactive'){
                $("#postStatuscheck_"+post_id).val(2);
                $("#postStatuscheck_"+post_id).prop('checked',false);
                toastr.success("post Deactivated",'Success',{timeOut: 5000});
            }
            if(res.status == 200 && res.action=='active'){
                $("#postStatuscheck_"+post_id).val(1);
                $("#postStatuscheck_"+post_id).prop('checked',true);
                toastr.success("post activated",'Success',{timeOut: 5000});
            }
        },
        error: function (data) {
            toastr.error("Please try again",'Error',{timeOut: 5000});
        }
    });
}



$('body').on('click', '#deletepostBtn', function (e) {
    // e.preventDefault();
    var post_id = $(this).attr('data-id');
    $("#DeletepostModal").find('#RemovepostSubmit').attr('data-id',post_id);
});

$('body').on('click', '#RemovepostSubmit', function (e) {
    $('#RemovepostSubmit').prop('disabled',true);
    $(this).find('.removeloadericonfa').show();
    e.preventDefault();
    var post_id = $(this).attr('data-id');
    $.ajax({
        type: 'GET',
        url: "{{ url('admin/posts') }}" +'/' + post_id +'/delete',
        success: function (res) {
            if(res.status == 200){
                $("#DeletepostModal").modal('hide');
                $('#RemovepostSubmit').prop('disabled',false);
                $("#RemovepostSubmit").find('.removeloadericonfa').hide();
                post_table();
                toastr.success("post Deleted",'Success',{timeOut: 5000});
            }

            if(res.status == 400){
                $("#DeletepostModal").modal('hide');
                $('#RemovepostSubmit').prop('disabled',false);
                $("#RemovepostSubmit").find('.removeloadericonfa').hide();
                post_table();
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        },
        error: function (data) {
            $("#DeletepostModal").modal('hide');
            $('#RemovepostSubmit').prop('disabled',false);
            $("#RemovepostSubmit").find('.removeloadericonfa').hide();
            post_table();
            toastr.error("Please try again",'Error',{timeOut: 5000});
        }
    });
});

$('#DeletepostModal').on('hidden.bs.modal', function () {
    $(this).find("#RemovepostSubmit").removeAttr('data-id');
});

$('body').on('click', '#editpostBtn', function () {
    var post_id = $(this).attr('data-id');
    var url = "{{ url('admin/posts') }}" + "/" + post_id + "/edit";
    window.open(url,"_blank");
});


function removeuploadedimg(divId ,inputId, imgName){
    if(confirm("Are you sure you want to remove this file?")){
        $("#"+divId).remove();
        $("#"+inputId).removeAttr('value');
        var filerKit = $("#postIconFiles").prop("jFiler");
        filerKit.reset();
    }
}



</script>
<!-- post JS end -->
@endsection

