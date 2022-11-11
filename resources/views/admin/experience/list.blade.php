@extends('admin.layout')

@section('content')
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Experience</a></li>
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
                            <h4 class="card-title">Experience List</h4>
                            <div class="custom-tab-1">
                                <ul class="nav nav-tabs mb-3">
                                    <li class="nav-item experience_page_tabs" data-tab="ALL_experience_tab"><a class="nav-link active show" data-toggle="tab" href="">ALL</a>
                                    </li>
                                    <li class="nav-item experience_page_tabs" data-tab="Approved_experience_tab"><a class="nav-link" data-toggle="tab" href="">Approved</a>
                                    </li>
                                    <li class="nav-item experience_page_tabs" data-tab="Rejected_experience_tab"><a class="nav-link" data-toggle="tab" href="">Rejected</a>
                                    </li>
                                    <li class="nav-item experience_page_tabs" data-tab="Draft_experience_tab"><a class="nav-link" data-toggle="tab" href="">Draft</a>
                                    </li>
                                    <li class="nav-item experience_page_tabs" data-tab="Padding_experience_tab"><a class="nav-link" data-toggle="tab" href="">Padding</a>
                                    </li>
                                    <li class="nav-item experience_page_tabs" data-tab="Deactive_experience_tab"><a class="nav-link" data-toggle="tab" href="">Deactive</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="table-responsive">
                                <table id="Experience" class="table zero-configuration customNewtable" style="width:100%">
                                    <thead>
                                    <tr>
                                        <th>Sr. No</th>
                                        <th>Name</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Time</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tfoot>
                                    <tr>
                                        <th>Sr. No</th>
                                        <th>Name</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Time</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endif

                        @if(isset($action) && $action=='edit')
                            @include('admin.experience.edit')
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="DeleteExperienceModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Remove Experience</h5>
                </div>
                <div class="modal-body">
                    Are you sure you wish to remove this Experience?
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal" type="button">Cancel</button>
                    <button class="btn btn-danger" id="RemoveExperienceSubmit" type="submit">Remove <i class="fa fa-circle-o-notch fa-spin removeloadericonfa" style="display:none;"></i></button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<!-- category JS start -->
<script type="text/javascript">
$('body').on('click', '.experience_page_tabs', function () {
    var tab_type = $(this).attr('data-tab');
    experience_table(tab_type,true);
});

function get_experience_page_tabType(){
    var tab_type;
    $('.experience_page_tabs').each(function() {
        var thi = $(this);
        if($(thi).find('a').hasClass('show')){
            tab_type = $(thi).attr('data-tab');
        }
    });
    return tab_type;
}

$('#language_id').select2({
    width: '100%',
    placeholder: "Select Language",
    allowClear: true
}).trigger('change');    


$(document).ready(function() {
    experience_table('',true);
});

$('body').on('click', '#save_closeExperienceBtn', function () {
    save_experience($(this),'save_close');
});

$('body').on('click', '#save_newExperienceBtn', function () {
    save_experience($(this),'save_new');
});

function save_experience(btn,btn_type){
    $(btn).prop('disabled',true);
    $(btn).find('.loadericonfa').show();
    var action  = $(btn).attr('data-action');

    var formData = new FormData($("#ExperienceCreateForm")[0]);
    formData.append('action',action);

    $.ajax({
        type: 'POST',
        url: "{{ route('admin.experience.save') }}",
        data: formData,
        processData: false,
        contentType: false,
        success: function (res) {
            if(res.status == 'failed'){
                $(btn).prop('disabled',false);
                $(btn).find('.loadericonfa').hide();

                if (res.errors.type) {
                    $('#type-error').show().text(res.errors.type);
                } else {
                    $('#type-error').hide();
                }

                if (res.errors.location) {
                    $('#location-error').show().text(res.errors.location);
                } else {
                    $('#location-error').hide();
                }

                if (res.errors.language_id) {
                    $('#language_id-error').show().text(res.errors.language_id);
                } else {
                    $('#language_id-error').hide();
                }

                if (res.errors.title) {
                    $('#title-error').show().text(res.errors.title);
                } else {
                    $('#title-error').hide();
                }

                if (res.errors.description) {
                    $('#description-error').show().text(res.errors.description);
                } else {
                    $('#description-error').hide();
                }

                if (res.errors.duration) {
                    $('#duration-error').show().text(res.errors.duration);
                } else {
                    $('#duration-error').hide();
                }

                if (res.errors.age_limit) {
                    $('#age_limit-error').show().text(res.errors.age_limit);
                } else {
                    $('#age_limit-error').hide();
                }

                if (res.errors.is_bring_item) {
                    $('#is_bring_item-error').show().text(res.errors.is_bring_item);
                } else {
                    $('#is_bring_item-error').hide();
                }

                if (res.errors.meet_address) {
                    $('#meet_address-error').show().text(res.errors.meet_address);
                } else {
                    $('#meet_address-error').hide();
                }

                if (res.errors.meet_city) {
                    $('#meet_city-error').show().text(res.errors.meet_city);
                } else {
                    $('#meet_city-error').hide();
                }

                if (res.errors.meet_state) {
                    $('#meet_state-error').show().text(res.errors.meet_state);
                } else {
                    $('#meet_state-error').hide();
                }

                if (res.errors.meet_country) {
                    $('#meet_country-error').show().text(res.errors.meet_country);
                } else {
                    $('#meet_country-error').hide();
                }
                if (res.errors.pine_code) {
                    $('#pine_code-error').show().text(res.errors.pine_code);
                } else {
                    $('#pine_code-error').hide();
                }
                if (res.errors.max_member_public_group_size) {
                    $('#max_member_public_group_size-error').show().text(res.errors.max_member_public_group_size);
                } else {
                    $('#max_member_public_group_size-error').hide();
                }
                if (res.errors.max_member_private_group_size) {
                    $('#max_member_private_group_size-error').show().text(res.errors.max_member_private_group_size);
                } else {
                    $('#max_member_private_group_size-error').hide();
                }
                if (res.errors.individual_rate) {
                    $('#individual_rate-error').show().text(res.errors.individual_rate);
                } else {
                    $('#individual_rate-error').hide();
                }
                if (res.errors.min_private_group_rate) {
                    $('#min_private_group_rate-error').show().text(res.errors.min_private_group_rate);
                } else {
                    $('#min_private_group_rate-error').hide();
                }
                if (res.errors.cancellation_policy_id) {
                    $('#cancellation_policy_id-error').show().text(res.errors.cancellation_policy_id);
                } else {
                    $('#cancellation_policy_id-error').hide();
                }
            }

            if(res.status == 200){
                if(btn_type == 'save_close'){
                    $(btn).prop('disabled',false);
                    $(btn).find('.loadericonfa').hide();
                    location.href="{{ route('admin.experience.list')}}";
                    if(res.action == 'update'){
                        toastr.success("Experience Updated",'Success',{timeOut: 5000});
                    }
                }
                if(btn_type == 'save_new'){
                    $(btn).prop('disabled',false);
                    $(btn).find('.loadericonfa').hide();
                   // $('#ExperienceCreateForm').trigger("reset");
                    if(res.action == 'update'){
                        toastr.success("Experience Updated",'Success',{timeOut: 5000});
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

function experience_table(tab_type='',is_clearState=false){
    if(is_clearState){
        $('#Experience').DataTable().state.clear();
    }

    $('#Experience').DataTable({
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
            "url": "{{ url('admin/allexperiencelist') }}",
            "dataType": "json",
            "type": "POST",
            "data":{ _token: '{{ csrf_token() }}', tab_type: tab_type},
            // "dataSrc": ""
        },
        'columnDefs': [
            { "width": "50px", "targets": 0 },
            { "width": "120px", "targets": 1 },
            { "width": "170px", "targets": 2 },
            { "width": "70px", "targets": 3 },
            { "width": "120px", "targets": 4 },
            { "width": "120px", "targets": 5 },
            { "width": "120px", "targets": 6 },
            { "width": "120px", "targets": 7 },
            { "width": "120px", "targets": 8 },
        ],
        "columns": [
            {data: 'sr_no', name: 'sr_no', class: "text-center", orderable: false,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {data: 'name', name: 'name', orderable: false, searchable: false, class: "text-center"},
            {data: 'title', name: 'title', orderable: false, searchable: false, class: "text-center"},
            {data: 'category_name', name: 'category_name', class: "text-left", orderable: false, searchable: false,},
            {data: 'time', name: 'time', class: "text-left", orderable: false, searchable: false,},
            {data: 'price', name: 'price', class: "text-left", orderable: false, searchable: false,},
            {data: 'estatus', name: 'estatus', orderable: false, searchable: false, class: "text-center"},
            {data: 'created_at', name: 'created_at', searchable: false, class: "text-left"},
            {data: 'action', name: 'action', orderable: false, searchable: false, class: "text-center"},
        ]
    });
}

function chageExperienceStatus(experience_id) {
    $.ajax({
        type: 'GET',
        url: "{{ url('admin/changeexperiencestatus') }}" +'/' + experience_id,
        success: function (res) {
            if(res.status == 200 && res.action=='deactive'){
                $("#ExperienceStatuscheck_"+experience_id).val(2);
                $("#ExperienceStatuscheck_"+experience_id).prop('checked',false);
                toastr.success("Experience Deactivated",'Success',{timeOut: 5000});
            }
            if(res.status == 200 && res.action=='active'){
                $("#ExperienceStatuscheck_"+experience_id).val(1);
                $("#ExperienceStatuscheck_"+experience_id).prop('checked',true);
                toastr.success("Experience activated",'Success',{timeOut: 5000});
            }
        },
        error: function (data) {
            toastr.error("Please try again",'Error',{timeOut: 5000});
        }
    });
}



$('body').on('click', '#deleteExperienceBtn', function (e) {
    // e.preventDefault();
    var experience_id = $(this).attr('data-id');
    $("#DeleteExperienceModal").find('#RemoveExperienceSubmit').attr('data-id',experience_id);
});

$('body').on('click', '#RemoveExperienceSubmit', function (e) {
    $('#RemoveExperienceSubmit').prop('disabled',true);
    $(this).find('.removeloadericonfa').show();
    e.preventDefault();
    var tab_type = get_experience_page_tabType();
    var experience_id = $(this).attr('data-id');
    $.ajax({
        type: 'GET',
        url: "{{ url('admin/experience') }}" +'/' + experience_id +'/delete',
        success: function (res) {
            if(res.status == 200){
                $("#DeleteExperienceModal").modal('hide');
                $('#RemoveExperienceSubmit').prop('disabled',false);
                $("#RemoveExperienceSubmit").find('.removeloadericonfa').hide();
                experience_table(tab_type);
                toastr.success("Experience Deleted",'Success',{timeOut: 5000});
            }

            if(res.status == 400){
                $("#DeleteExperienceModal").modal('hide');
                $('#RemoveExperienceSubmit').prop('disabled',false);
                $("#RemoveExperienceSubmit").find('.removeloadericonfa').hide();
                experience_table(tab_type);
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        },
        error: function (data) {
            $("#DeleteExperienceModal").modal('hide');
            $('#RemoveExperienceSubmit').prop('disabled',false);
            $("#RemoveExperienceSubmit").find('.removeloadericonfa').hide();
            experience_table(tab_type);
            toastr.error("Please try again",'Error',{timeOut: 5000});
        }
    });
});

$('#DeleteExperienceModal').on('hidden.bs.modal', function () {
    $(this).find("#RemoveExperienceSubmit").removeAttr('data-id');
});

$('body').on('click', '#editExperienceBtn', function () {
    var experience_id = $(this).attr('data-id');
    var url = "{{ url('admin/experience') }}" + "/" + experience_id + "/edit";
    window.open(url,"_blank");
});

function removeuploadedimg(divId ,inputId, imgId){
    if(confirm("Are you sure you want to remove this file?")){
        $("#"+divId).remove();
        $("#"+inputId).removeAttr('value');
        var ImageUrl = $("#web_url").val() + "/admin/";
        var removableFile = imgId;
		jQuery.post(ImageUrl+'experience/removefile?action=removeCatIcon&imgId='+ removableFile, {'_token': $('meta[name="csrf-token"]').attr('content')});
        //var filerKit = $("#catIconFiles").prop("jFiler");
        //filerKit.reset();
    }
}

$(document).on('change', '#is_bring_item', function() {
    if ($(this).is(':checked')) {
        var value =$(this).val();
       if(value != 1){
        $(".BringItem").hide();
       }else{
        $(".BringItem").show();
       }
        
        
    }
   
});

$('body').on('click', '#ApproveExperienceBtn', function () {
    var tab_type = get_experience_page_tabType();
    var experience_id = $(this).attr('data-id');
    $.ajax ({
        type:"POST",
        url: '{{ url("admin/change_experience_status") }}',
        data: {experience_id: experience_id, action: 'approve',  "_token": "{{csrf_token()}}"},
        success: function(res) {
            if(res['status'] == 200){
                toastr.success("Experience Approved",'Success',{timeOut: 5000});
            } else {
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        },
        complete: function(){
            experience_table(tab_type);
        },
        error: function() {
            toastr.error("Please try again",'Error',{timeOut: 5000});
        }
    });
});

$('body').on('click', '#RejectExperienceBtn', function () {
    var tab_type = get_experience_page_tabType();
    var experience_id = $(this).attr('data-id');
    $.ajax ({
        type:"POST",
        url: '{{ url("admin/change_experience_status") }}',
        data: {experience_id: experience_id, action: 'reject',  "_token": "{{csrf_token()}}"},
        success: function(res) {
            if(res['status'] == 200){
                toastr.success("Experience Rejected",'Success',{timeOut: 5000});
            } else {
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        },
        complete: function(){
            experience_table(tab_type);
        },
        error: function() {
            toastr.error("Please try again",'Error',{timeOut: 5000});
        }
    });
});

</script>
<!-- category JS end -->
@endsection

