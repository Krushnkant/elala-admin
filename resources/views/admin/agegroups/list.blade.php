@extends('admin.layout')

@section('content')
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Age Group</a></li>
            </ol>
        </div>
    </div>
    <!-- row -->

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <div class="action-section row">
                            <div class="col-lg-8 col-md-8 col-sm-12">
                                <?php $page_id = \App\Models\ProjectPage::where('route_url',\Illuminate\Support\Facades\Route::currentRouteName())->pluck('id')->first(); ?>
                                @if(getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id)) )
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#AgeGroupModel" id="AddAgeGroupBtn"><i class="fa fa-plus" aria-hidden="true"></i></button>
                                @endif
                                
                            </div>
                            
                        </div>

                        <div class="tab-pane fade show active table-responsive" id="all_user_tab">
                            <table id="all_agegroup" class="table zero-configuration customNewtable" style="width:100%">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>From Age</th>
                                    <th>To Age</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Other</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                <th>No</th>
                                    <th>From Age</th>
                                    <th>To Age</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Other</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="AgeGroupModel">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form class="form-valide" action="" id="agegroupform" method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formtitle">Add Age Group</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="attr-cover-spin" class="cover-spin"></div>
                        {{ csrf_field() }}
                        <div class="form-group ">
                            <label class="col-form-label" for="from_age">From Age <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control input-flat" id="from_age" name="from_age" min="0" onkeypress="return isNumber(event)" placeholder="">
                            <div id="from_age-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                        <div class="form-group ">
                            <label class="col-form-label" for="to_age">To Age <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control input-flat" id="to_age" name="to_age" min="0" onkeypress="return isNumber(event)"  placeholder="">
                            <div id="to_age-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                        
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="agegroup_id" id="agegroup_id">
                        <button type="button" class="btn btn-outline-primary" id="save_newAgeGroupBtn">Save & New <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                        <button type="button" class="btn btn-primary" id="save_closeAgeGroupBtn">Save & Close <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="DeleteAgeGroupModel">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Remove Age Group</h5>
                </div>
                <div class="modal-body">
                    Are you sure you wish to remove this Age Group?
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal" type="button">Cancel</button>
                    <button class="btn btn-danger" id="RemoveAgeGroupSubmit" type="submit">Remove <i class="fa fa-circle-o-notch fa-spin removeloadericonfa" style="display:none;"></i></button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<!-- user list JS start -->
<script type="text/javascript">
    $(document).ready(function() {
        agegroup_page_tabs('',true);
    });

    function save_agegroup(btn,btn_type){
        $(btn).prop('disabled',true);
        $(btn).find('.loadericonfa').show();

        var action  = $(btn).attr('data-action');

        var formData = new FormData($("#agegroupform")[0]);

        formData.append('action',action);

        $.ajax({
            type: 'POST',
            url: "{{ url('admin/addorupdateagegroups') }}",
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if(res.status == 'failed'){
                    $(btn).prop('disabled',false);
                    $(btn).find('.loadericonfa').hide();
                    
                    if (res.errors.from_age) {
                        $('#from_age-error').show().text(res.errors.from_age);
                    } else {
                        $('#from_age-error').hide();
                    }

                    if (res.errors.to_age) {
                        $('#to_age-error').show().text(res.errors.to_age);
                    } else {
                        $('#to_age-error').hide();
                    }
                }

                if(res.status == 200){
                    if(btn_type == 'save_close'){
                        $("#AgeGroupModel").modal('hide');
                        $(btn).prop('disabled',false);
                        $(btn).find('.loadericonfa').hide();
                        if(res.action == 'add'){
                            agegroup_page_tabs();
                            toastr.success("Age Group Added",'Success',{timeOut: 5000});
                        }
                        if(res.action == 'update'){
                            agegroup_page_tabs();
                            toastr.success("Age Group Updated",'Success',{timeOut: 5000});
                        }
                    }

                    if(btn_type == 'save_new'){
                        $(btn).prop('disabled',false);
                        $(btn).find('.loadericonfa').hide();
                        $("#AgeGroupModel").find('form').trigger('reset');
                        $("#AgeGroupModel").find("#save_newAgeGroupBtn").removeAttr('data-action');
                        $("#AgeGroupModel").find("#save_closeAgeGroupBtn").removeAttr('data-action');
                        $("#AgeGroupModel").find("#save_newAgeGroupBtn").removeAttr('data-id');
                        $("#AgeGroupModel").find("#save_closeAgeGroupBtn").removeAttr('data-id');
                        $('#agegroup_id').val("");
                        $('#from_age-error').html("");
                        $('#to_age-error').html("");
                      
                    
                        $("#from_age").focus();
                        if(res.action == 'add'){
                            agegroup_page_tabs();
                            toastr.success("Age Group Added",'Success',{timeOut: 5000});
                        }
                        if(res.action == 'update'){
                            agegroup_page_tabs();
                            toastr.success("Age Group Updated",'Success',{timeOut: 5000});
                        }
                    }
                }

                if(res.status == 400){
                    $("#AgeGroupModel").modal('hide');
                    $(btn).prop('disabled',false);
                    $(btn).find('.loadericonfa').hide();
                    agegroup_page_tabs();
                    if(res.message == ""){
                      toastr.error("Please try again",'Error',{timeOut: 5000});
                    }else{
                        toastr.error(res.message,'Error',{timeOut: 5000});  
                    }
                }
            },
            error: function (data) {
                $("#AgeGroupModel").modal('hide');
                $(btn).prop('disabled',false);
                $(btn).find('.loadericonfa').hide();
                agegroup_page_tabs();
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        });
    }

    $('body').on('click', '#save_newAgeGroupBtn', function () {
        save_agegroup($(this),'save_new');
    });

    $('body').on('click', '#save_closeAgeGroupBtn', function () {
        save_agegroup($(this),'save_close');
    });

    $('#AgeGroupModel').on('shown.bs.modal', function (e) {
        $("#from_age").focus();
    });

   

    $('#AgeGroupModel').on('hidden.bs.modal', function () {
        $(this).find('form').trigger('reset');
        $(this).find("#save_newAgeGroupBtn").removeAttr('data-action');
        $(this).find("#save_closeAgeGroupBtn").removeAttr('data-action');
        $(this).find("#save_newAgeGroupBtn").removeAttr('data-id');
        $(this).find("#save_closeAgeGroupBtn").removeAttr('data-id');
        $('#agegroup_id').val("");
        $('#from_age-error').html("");
        $('#to_age-error').html("");
        
    });

    $('#DeleteAgeGroupModel').on('hidden.bs.modal', function () {
        $(this).find("#RemoveAgeGroupSubmit").removeAttr('data-id');
    });

    function agegroup_page_tabs(tab_type='',is_clearState=false) {
        if(is_clearState){
            $('#all_agegroup').DataTable().state.clear();
        }

        $('#all_agegroup').DataTable({
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
                "url": "{{ url('admin/allagegroupslist') }}",
                "dataType": "json",
                "type": "POST",
                "data":{ _token: '{{ csrf_token() }}'},
                // "dataSrc": ""
            },
            'columnDefs': [
                { "width": "50px", "targets": 0 },
                { "width": "145px", "targets": 1 },
                { "width": "165px", "targets": 2 },
                { "width": "75px", "targets": 3 },
                { "width": "120px", "targets": 4 },
                { "width": "115px", "targets": 5 },
            ],
            "columns": [
                {data: 'id', name: 'id', class: "text-center", orderable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {data: 'from_age', name: 'from_age', class: "text-center multirow"},
                {data: 'to_age', name: 'to_age', class: "text-left multirow"},
                {data: 'estatus', name: 'estatus', orderable: false, searchable: false, class: "text-center"},
                {data: 'created_at', name: 'created_at', searchable: false, class: "text-left"},
                {data: 'action', name: 'action', orderable: false, searchable: false, class: "text-center"},
            ]
        });
    }


    function changeAgegroupStatus(agegroup_id) {
        //var tab_type = get_users_page_tabType();
       
        $.ajax({
            type: 'GET',
            url: "{{ url('admin/changeagegroupstatus') }}" +'/' + agegroup_id,
            success: function (res) {
                if(res.status == 200 && res.action=='deactive'){
                    $("#agegroupstatuscheck_"+agegroup_id).val(2);
                    $("#agegroupstatuscheck_"+agegroup_id).prop('checked',false);
                    agegroup_page_tabs();
                    toastr.success("Age Group Deactivated",'Success',{timeOut: 5000});
                }
                if(res.status == 200 && res.action=='active'){
                    $("#agegroupstatuscheck_"+agegroup_id).val(1);
                    $("#agegroupstatuscheck_"+agegroup_id).prop('checked',true);
                    agegroup_page_tabs();
                    toastr.success("Age Group activated",'Success',{timeOut: 5000});
                }
            },
            error: function (data) {
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        });
    }

    $('body').on('click', '#AddAgeGroupBtn', function (e) {
        $("#AgeGroupModel").find('.modal-title').html("Add Age Group");
    });

    $('body').on('click', '#editAgeGroupBtn', function () {
        var agegroup_id = $(this).attr('data-id');
        $.get("{{ url('admin/agegroups') }}" +'/' + agegroup_id +'/edit', function (data) {
            $('#AgeGroupModel').find('.modal-title').html("Edit Age Group");
            $('#AgeGroupModel').find('#save_closeAgeGroupBtn').attr("data-action","update");
            $('#AgeGroupModel').find('#save_newAgeGroupBtn').attr("data-action","update");
            $('#AgeGroupModel').find('#save_closeAgeGroupBtn').attr("data-id",agegroup_id);
            $('#AgeGroupModel').find('#save_newAgeGroupBtn').attr("data-id",agegroup_id);
            $('#agegroup_id').val(data.id);
            
            $('#from_age').val(data.from_age);
            $('#to_age').val(data.to_age);
            
        })
    });

    $('body').on('click', '#deleteAgeGroupBtn', function (e) {
        var delete_agegroup_id = $(this).attr('data-id');
        $("#DeleteAgeGroupModel").find('#RemoveAgeGroupSubmit').attr('data-id',delete_agegroup_id);
    });

    $('body').on('click', '#RemoveAgeGroupSubmit', function (e) {
        $('#RemoveAgeGroupSubmit').prop('disabled',true);
        $(this).find('.removeloadericonfa').show();
        e.preventDefault();
        var remove_agegroup_id = $(this).attr('data-id');
          

        $.ajax({
            type: 'GET',
            url: "{{ url('admin/agegroups') }}" +'/' + remove_agegroup_id +'/delete',
            success: function (res) {
                if(res.status == 200){
                    $("#DeleteAgeGroupModel").modal('hide');
                    $('#RemoveAgeGroupSubmit').prop('disabled',false);
                    $("#RemoveAgeGroupSubmit").find('.removeloadericonfa').hide();
                    agegroup_page_tabs();
                    toastr.success("Age Group Deleted",'Success',{timeOut: 5000});
                }

                if(res.status == 400){
                    $("#DeleteAgeGroupModel").modal('hide');
                    $('#RemoveAgeGroupSubmit').prop('disabled',false);
                    $("#RemoveAgeGroupSubmit").find('.removeloadericonfa').hide();
                    agegroup_page_tabs();
                    toastr.error("Please try again",'Error',{timeOut: 5000});
                }
            },
            error: function (data) {
                $("#DeleteAgeGroupModel").modal('hide');
                $('#RemoveAgeGroupSubmit').prop('disabled',false);
                $("#RemoveAgeGroupSubmit").find('.removeloadericonfa').hide();
                agegroup_page_tabs();
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        });
    });

    
    

    function isNumber(evt) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    }
</script>
<!-- user list JS end -->
@endsection

