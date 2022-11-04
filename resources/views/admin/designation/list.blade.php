@extends('admin.layout')

@section('content')
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Designation List</a></li>
            </ol>
        </div>
    </div>
    <!-- row -->

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        {{--<h4 class="card-title">User List</h4>--}}

                        <div class="action-section row">
                            <div class="col-lg-8 col-md-8 col-sm-12">
                                <?php $page_id = \App\Models\ProjectPage::where('route_url',\Illuminate\Support\Facades\Route::currentRouteName())->pluck('id')->first(); ?>
                                @if(getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id)) )
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#DesignationModal" id="AddDesignationBtn"><i class="fa fa-plus" aria-hidden="true"></i></button>
                                @endif
                            </div>
                           
                        </div>

                        <div class="tab-pane fade show active table-responsive" id="all_designation_tab">
                            <table id="all_designations" class="table zero-configuration customNewtable" style="width:100%">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Created Date</th>
                                    <th>Other</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th>No</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Created Date</th>
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

    <div class="modal fade" id="DesignationModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form class="form-valide" action="" id="designationform" method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formtitle">Add Designation</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="attr-cover-spin" class="cover-spin"></div>
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label class="col-form-label" for="title">Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control input-flat" id="title" name="title" placeholder="">
                            <div id="title-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                        
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="designation_id" id="designation_id">
                        <button type="button" class="btn btn-outline-primary" id="save_newDesignationBtn">Save & New <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                        <button type="button" class="btn btn-primary" id="save_closeDesignationBtn">Save & Close <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="DeleteDesignationModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Remove Designation</h5>
                </div>
                <div class="modal-body">
                    Are you sure you wish to remove this Designation?
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal" type="button">Cancel</button>
                    <button class="btn btn-danger" id="RemoveDesignationSubmit" type="submit">Remove <i class="fa fa-circle-o-notch fa-spin removeloadericonfa" style="display:none;"></i></button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<!-- user list JS start -->
<script type="text/javascript">
    $(document).ready(function() {
        table_designation(true);
    });

    function get_users_page_tabType(){
        var tab_type;
        $('.table_designation').each(function() {
            var thi = $(this);
            if($(thi).find('a').hasClass('show')){
                tab_type = $(thi).attr('data-tab');
            }
        });
        return tab_type;
    }

    function save_designation(btn,btn_type){
        $(btn).prop('disabled',true);
        $(btn).find('.loadericonfa').show();

        var action  = $(btn).attr('data-action');

        var formData = new FormData($("#designationform")[0]);

        formData.append('action',action);

        $.ajax({
            type: 'POST',
            url: "{{ url('admin/addorupdatedesignation') }}",
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if(res.status == 'failed'){
                    $(btn).prop('disabled',false);
                    $(btn).find('.loadericonfa').hide();
                    
                    if (res.errors.title) {
                        $('#title-error').show().text(res.errors.title);
                    } else {
                        $('#title-error').hide();
                    }

                   
                }

                if(res.status == 200){
                    if(btn_type == 'save_close'){
                        $("#DesignationModal").modal('hide');
                        $(btn).prop('disabled',false);
                        $(btn).find('.loadericonfa').hide();
                        if(res.action == 'add'){
                            table_designation(true);
                            toastr.success("Designation Added",'Success',{timeOut: 5000});
                        }
                        if(res.action == 'update'){
                            table_designation();
                            toastr.success("Designation Updated",'Success',{timeOut: 5000});
                        }
                    }

                    if(btn_type == 'save_new'){
                        $(btn).prop('disabled',false);
                        $(btn).find('.loadericonfa').hide();
                        $("#DesignationModal").find('form').trigger('reset');
                        $("#DesignationModal").find("#save_newDesignationBtn").removeAttr('data-action');
                        $("#DesignationModal").find("#save_closeDesignationBtn").removeAttr('data-action');
                        $("#DesignationModal").find("#save_newDesignationBtn").removeAttr('data-id');
                        $("#DesignationModal").find("#save_closeDesignationBtn").removeAttr('data-id');
                        $('#designation_id').val("");
                        $('#profilepic-error').html("");
                        $('#fullname-error').html("");
                        $('#mobileno-error').html("");
                        $('#email-error').html("");
                        $('#password-error').html("");
                        $('#dob-error').html("");
                        $('#gender-error').html("");
                        var default_image = "{{ asset('images/default_avatar.jpg') }}";
                        $('#profilepic_image_show').attr('src', default_image);
                        $("#full_name").focus();
                        if(res.action == 'add'){
                            table_designation(tab_type,true);
                            toastr.success("User Added",'Success',{timeOut: 5000});
                        }
                        if(res.action == 'update'){
                            table_designation(tab_type);
                            toastr.success("User Updated",'Success',{timeOut: 5000});
                        }
                    }
                }

                if(res.status == 400){
                    $("#DesignationModal").modal('hide');
                    $(btn).prop('disabled',false);
                    $(btn).find('.loadericonfa').hide();
                    table_designation(tab_type);
                    toastr.error("Please try again",'Error',{timeOut: 5000});
                }
            },
            error: function (data) {
                $("#DesignationModal").modal('hide');
                $(btn).prop('disabled',false);
                $(btn).find('.loadericonfa').hide();
                table_designation(tab_type);
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        });
    }

    $('body').on('click', '#save_newDesignationBtn', function () {
        save_designation($(this),'save_new');
    });

    $('body').on('click', '#save_closeDesignationBtn', function () {
        save_designation($(this),'save_close');
    });

    $('#DesignationModal').on('shown.bs.modal', function (e) {
        $("#title").focus();
    });

    $('#profile_pic').change(function(){
        $('#profilepic-error').hide();
        var file = this.files[0];
        var fileType = file["type"];
        var validImageTypes = ["image/jpeg", "image/png", "image/jpg"];
        if ($.inArray(fileType, validImageTypes) < 0) {
            $('#profilepic-error').show().text("Please provide a Valid Extension Image(e.g: .jpg .png)");
            var default_image = "{{ asset('images/default_avatar.jpg') }}";
            $('#profilepic_image_show').attr('src', default_image);
        }
        else {
            let reader = new FileReader();
            reader.onload = (e) => {
                $('#profilepic_image_show').attr('src', e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    $('#DesignationModal').on('hidden.bs.modal', function () {
        $(this).find('form').trigger('reset');
        $(this).find("#save_newDesignationBtn").removeAttr('data-action');
        $(this).find("#save_closeDesignationBtn").removeAttr('data-action');
        $(this).find("#save_newDesignationBtn").removeAttr('data-id');
        $(this).find("#save_closeDesignationBtn").removeAttr('data-id');
        $('#designation_id').val("");
        $('#profilepic-error').html("");
        $('#fullname-error').html("");
        $('#mobileno-error').html("");
        $('#email-error').html("");
        $('#password-error').html("");
        $('#dob-error').html("");
        $('#gender-error').html("");
        var default_image = "{{ asset('images/default_avatar.jpg') }}";
        $('#profilepic_image_show').attr('src', default_image);
    });

    $('#DeleteDesignationModal').on('hidden.bs.modal', function () {
        $(this).find("#RemoveDesignationSubmit").removeAttr('data-id');
    });

    function table_designation(is_clearState=false) {
       
        if(is_clearState){
            $('#all_designations').DataTable().state.clear();
        }

        $('#all_designations').DataTable({
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
                "url": "{{ url('admin/alldesignationslist') }}",
                "dataType": "json",
                "type": "POST",
                "data":{ _token: '{{ csrf_token() }}'},
                // "dataSrc": ""
            },
            'columnDefs': [
                { "width": "50px", "targets": 0 },
                { "width": "230px", "targets": 1 },
                { "width": "75px", "targets": 2 },
                { "width": "120px", "targets": 3 },
                { "width": "115px", "targets": 4 },
            ],
            "columns": [
                {data: 'id', name: 'id', class: "text-center", orderable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {data: 'title', name: 'title', class: "text-left multirow", orderable: false},
                {data: 'estatus', name: 'estatus', orderable: false, searchable: false, class: "text-center"},
                {data: 'created_at', name: 'created_at', searchable: false, class: "text-left"},
                {data: 'action', name: 'action', orderable: false, searchable: false, class: "text-center"},
            ]
        });
    }


    function changeDesignationStatus(designation_id) {
        

        $.ajax({
            type: 'GET',
            url: "{{ url('admin/changedesignationstatus') }}" +'/' + designation_id,
            success: function (res) {
                if(res.status == 200 && res.action=='deactive'){
                    $("#Designationstatuscheck_"+designation_id).val(2);
                    $("#Designationstatuscheck_"+designation_id).prop('checked',false);
                    table_designation();
                    toastr.success("Designation Deactivated",'Success',{timeOut: 5000});
                }
                if(res.status == 200 && res.action=='active'){
                    $("#Designationstatuscheck_"+designation_id).val(1);
                    $("#Designationstatuscheck_"+designation_id).prop('checked',true);
                    table_designation();
                    toastr.success("Designation activated",'Success',{timeOut: 5000});
                }
            },
            error: function (data) {
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        });
    }

    $('body').on('click', '#AddUserBtn', function (e) {
        $("#DesignationModal").find('.modal-title').html("Add User");
    });

    $('body').on('click', '#editDesignationBtn', function () {
        
        var designation_id = $(this).attr('data-id');
       
        $.get("{{ url('admin/designation') }}" +'/' + designation_id +'/edit', function (data) {
            $('#DesignationModal').find('.modal-title').html("Edit Designation");
            $('#DesignationModal').find('#save_closeDesignationBtn').attr("data-action","update");
            $('#DesignationModal').find('#save_newDesignationBtn').attr("data-action","update");
            $('#DesignationModal').find('#save_closeDesignationBtn').attr("data-id",designation_id);
            $('#DesignationModal').find('#save_newDesignationBtn').attr("data-id",designation_id);
            $('#designation_id').val(data.id);
           
            $('#title').val(data.title);
            
        })
    });

    $('body').on('click', '#deleteDesignationBtn', function (e) {
        // e.preventDefault();
        var delete_designation_id = $(this).attr('data-id');
        $("#DeleteDesignationModal").find('#RemoveDesignationSubmit').attr('data-id',delete_designation_id);
    });

    $('body').on('click', '#RemoveDesignationSubmit', function (e) {
        $('#RemoveDesignationSubmit').prop('disabled',true);
        $(this).find('.removeloadericonfa').show();
        e.preventDefault();
        var remove_designation_id = $(this).attr('data-id');

        $.ajax({
            type: 'GET',
            url: "{{ url('admin/designation') }}" +'/' + remove_designation_id +'/delete',
            success: function (res) {
                if(res.status == 200){
                    $("#DeleteDesignationModal").modal('hide');
                    $('#RemoveDesignationSubmit').prop('disabled',false);
                    $("#RemoveDesignationSubmit").find('.removeloadericonfa').hide();
                    table_designation();
                    toastr.success("Designation Deleted",'Success',{timeOut: 5000});
                }

                if(res.status == 400){
                    $("#DeleteDesignationModal").modal('hide');
                    $('#RemoveDesignationSubmit').prop('disabled',false);
                    $("#RemoveDesignationSubmit").find('.removeloadericonfa').hide();
                    table_designation();
                    toastr.error("Please try again",'Error',{timeOut: 5000});
                }
            },
            error: function (data) {
                $("#DeleteDesignationModal").modal('hide');
                $('#RemoveDesignationSubmit').prop('disabled',false);
                $("#RemoveDesignationSubmit").find('.removeloadericonfa').hide();
                table_designation();
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        });
    });

    $('body').on('click', '#permissionDesignationBtn', function (e) {
        // e.preventDefault();
        var designation_id = $(this).attr('data-id');
        var url = "{{ url('admin/designation') }}" + "/" + designation_id + "/permission";
        window.open(url,"_blank");
    });
</script>
<!-- user list JS end -->
@endsection

