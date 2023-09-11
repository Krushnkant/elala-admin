@extends('admin.layout')

@section('content')
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Policy </a></li>
            </ol>
        </div>
    </div>
    <!-- row -->

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <div class="action-section">
                            <?php $page_id = \App\Models\ProjectPage::where('route_url',\Illuminate\Support\Facades\Route::currentRouteName())->pluck('id')->first(); ?>
                            @if(getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id)) )
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#PolicyModal" id="AddBtn_Policy"><i class="fa fa-plus" aria-hidden="true"></i></button>
                            @endif
                        </div>

                       

                        <div class="tab-pane fade show active table-responsive" id="policy_tab">
                            <table id="policy_page_table" class="table zero-configuration customNewtable" style="width:100%">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th>No</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="PolicyModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form class="form-valide" action="" id="policyform" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formtitle">Add New Policy</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="attr-cover-spin" class="cover-spin"></div>
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label class="col-form-label" for="title" id="label_title">Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control input-flat" id="title" name="title" placeholder="">
                            <div id="title-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label" for="description" id="">Description <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control input-flat" id="description" name="description" ></textarea>
                            <div id="description-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                       
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="policy_id" id="policy_id">
                        <button type="button" class="btn btn-outline-primary" id="save_newPolicyBtn">Save & New <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                        <button type="button" class="btn btn-primary" id="save_closePolicyBtn">Save & Close <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="DeletePolicyModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Remove Policy</h5>
                </div>
                <div class="modal-body">
                    Are you sure you wish to remove this Policy?
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal" type="button">Cancel</button>
                    <button class="btn btn-danger" id="Removepolicysubmit" type="submit">Remove <i class="fa fa-circle-o-notch fa-spin removeloadericonfa" style="display:none;"></i></button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <!-- Policy JS start -->
    <script type="text/javascript">
        function get_policy_page_tabType(){
            var tab_type;
            $('.policy_page_tabs').each(function() {
                var thi = $(this);
                if($(thi).find('a').hasClass('show')){
                    tab_type = $(thi).attr('data-tab');
                }
            });
            return tab_type;
        }

        function save_policy(btn,btn_type){
            $(btn).prop('disabled',true);
            $(btn).find('.loadericonfa').show();
            var formData = $("#policyform").serializeArray();

            var tab_type = get_policy_page_tabType();

            formData.push({ name: "tab_type", value: tab_type });

            $.ajax({
                type: 'POST',
                url: "{{ url('admin/addorupdatepolicy') }}",
                data: formData,
                success: function (res) {
                    if(res.status == 'failed'){
                        $(btn).find('.loadericonfa').hide();
                        $(btn).prop('disabled',false);
                        if (res.errors.title) {
                            $('#title-error').show().text(res.errors.title);
                        } else {
                            $('#title-error').hide();
                        }
                    }

                    if(res.status == 200){
                        if(btn_type == 'save_close'){
                            $("#PolicyModal").modal('hide');
                            $(btn).find('.loadericonfa').hide();
                            $(btn).prop('disabled',false);
                            if(res.action == 'add'){
                                policy_page_tabs(tab_type,true);
                                toastr.success("Policy Added",'Success',{timeOut: 5000});
                            }
                            if(res.action == 'update'){
                                policy_page_tabs(tab_type);
                                toastr.success("Policy Updated",'Success',{timeOut: 5000});
                            }
                        }

                        if(btn_type == 'save_new'){
                            $(btn).find('.loadericonfa').hide();
                            $(btn).prop('disabled',false);
                            $("#PolicyModal").find('form').trigger('reset');
                            $('#policy_id').val("");
                            $('#title-error').html("");
                            $("#PolicyModal").find("#save_newPolicyBtn").removeAttr('data-action');
                            $("#PolicyModal").find("#save_closePolicyBtn").removeAttr('data-action');
                            $("#PolicyModal").find("#save_newPolicyBtn").removeAttr('data-id');
                            $("#PolicyModal").find("#save_closePolicyBtn").removeAttr('data-id');
                            $("#title").focus();
                            if(res.action == 'add'){
                                policy_page_tabs(tab_type,true);
                                toastr.success("Policy Added",'Success',{timeOut: 5000});
                            }
                            if(res.action == 'update'){
                                policy_page_tabs(tab_type);
                                toastr.success("Policy Updated",'Success',{timeOut: 5000});
                            }
                        }
                    }

                    if(res.status == 400){
                        $("#PolicyModal").modal('hide');
                        $(btn).find('.loadericonfa').hide();
                        $(btn).prop('disabled',false);
                        policy_page_tabs(tab_type);
                        toastr.error("Please try again",'Error',{timeOut: 5000});
                    }
                },
                error: function (data) {
                    $("#PolicyModal").modal('hide');
                    $(btn).find('.loadericonfa').hide();
                    $(btn).prop('disabled',false);
                    policy_page_tabs(tab_type);
                    toastr.error("Please try again",'Error',{timeOut: 5000});
                }
            });
        }

        $('body').on('click', '#save_newPolicyBtn', function () {
            save_policy($(this),'save_new');
        });

        $('body').on('click', '#save_closePolicyBtn', function () {
            save_policy($(this),'save_close');
        });

        $('#PolicyModal').on('shown.bs.modal', function (e) {
            $("#title").focus();
        });

        $('#PolicyModal').on('hidden.bs.modal', function () {
            $(this).find('form').trigger('reset');
            $(this).find("#save_newPolicyBtn").removeAttr('data-action');
            $(this).find("#save_closePolicyBtn").removeAttr('data-action');
            $(this).find("#save_newPolicyBtn").removeAttr('data-id');
            $(this).find("#save_closePolicyBtn").removeAttr('data-id');
            $('#policy_id').val("");
            $('#title-error').html("");
        });

        $('#DeletePolicyModal').on('hidden.bs.modal', function () {
            $(this).find("#Removepolicysubmit").removeAttr('data-id');
        });

        $(document).ready(function() {
            policy_page_tabs('',true);
        });

        function policy_page_tabs(tab_type='',is_clearState=false) {
            if(is_clearState){
                $('#policy_page_table').DataTable().state.clear();
            }

            $('#policy_page_table').DataTable({
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
                    "url": "{{ url('admin/allpolicylist') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{ _token: '{{ csrf_token() }}' ,tab_type: tab_type},
                    // "dataSrc": ""
                },
                'columnDefs': [
                    { "width": "30px", "targets": 0 },
                    { "width": "150px", "targets": 1 },
                    { "width": "160px", "targets": 2 },
                    { "width": "75px", "targets": 3 },
                    { "width": "80px", "targets": 4 },
                    { "width": "80px", "targets": 5 },
                ],
                "columns": [
                    {data: 'id', name: 'id', class: "text-center" , orderable: false ,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {data: 'title', name: 'title',orderable: false, class: "text-left"},
                    {data: 'description', name: 'description',orderable: false, class: "text-left"},
                    {data: 'estatus', name: 'estatus', orderable: false, searchable: false, class: "text-center"},
                    {data: 'created_at', name: 'created_at', searchable: false, class: "text-left"},
                    {data: 'action', name: 'action', orderable: false, searchable: false, class: "text-center"},
                ]
            });
        }

        $(".policy_page_tabs").click(function() {
            var tab_type = $(this).attr('data-tab');
            policy_page_tabs(tab_type,true);
        });

        $('body').on('click', '#AddBtn_AttrSpec', function () {
            var edit_policy_id = $(this).attr('data-id');
           
        });

        $('body').on('click', '#editPolicyBtn', function () {
            var edit_policy_id = $(this).attr('data-id');
            $('#PolicyModal').find('.modal-title').html("Edit Policy");
            $.get("{{ url('admin/policy') }}" +'/' + edit_policy_id +'/edit', function (data) {
                $('#PolicyModal').find('#save_newPolicyBtn').attr("data-action","update");
                $('#PolicyModal').find('#save_closePolicyBtn').attr("data-action","update");
                $('#PolicyModal').find('#save_newPolicyBtn').attr("data-id",edit_policy_id);
                $('#PolicyModal').find('#save_closePolicyBtn').attr("data-id",edit_policy_id);
                $('#policy_id').val(data.id);
                $('#title').val(data.title);
                $('#description').val(data.description);
                
            });
        });

        $('body').on('click', '#deletePolicyBtn', function (e) {
            // e.preventDefault();
            var delete_policy_id = $(this).attr('data-id');
            $("#DeletePolicyModal").find('#Removepolicysubmit').attr('data-id',delete_policy_id);
            $('#DeletePolicyModal').find('.modal-title').html("Remove Policy");
            $('#DeletePolicyModal').find('.modal-body').html("Are you sure you wish to remove this Policy?");
           
        });

        $('body').on('click', '#Removepolicysubmit', function (e) {
            $('#Removepolicysubmit').prop('disabled',true);
            $(this).find('.removeloadericonfa').show();
            e.preventDefault();
            var remove_policy_id = $(this).attr('data-id');

            $.ajax({
                type: 'GET',
                url: "{{ url('admin/policy') }}" +'/' + remove_policy_id +'/delete',
                success: function (res) {
                    if(res.status == 200){
                        $("#DeletePolicyModal").modal('hide');
                        $('#Removepolicysubmit').prop('disabled',false);
                        $("#Removepolicysubmit").find('.removeloadericonfa').hide();
                        policy_page_tabs();
                        // redrawAfterDelete();
                        toastr.success("Policy Deleted",'Success',{timeOut: 5000});
                    }

                    if(res.status == 400){
                        $("#DeletePolicyModal").modal('hide');
                        $('#Removepolicysubmit').prop('disabled',false);
                        $("#Removepolicysubmit").find('.removeloadericonfa').hide();
                        policy_page_tabs();
                        toastr.error("Please try again",'Error',{timeOut: 5000});
                    }
                },
                error: function (data) {
                    $("#DeletePolicyModal").modal('hide');
                    $('#Removepolicysubmit').prop('disabled',false);
                    $("#Removepolicysubmit").find('.removeloadericonfa').hide();
                    policy_page_tabs();
                    toastr.error("Please try again",'Error',{timeOut: 5000});
                }
            });
        });

        function chagePolicyStatus(policy_id) {
            $.ajax({
                type: 'GET',
                url: "{{ url('admin/chagepolicystatus') }}" +'/' + policy_id,
                success: function (res) {
                    if(res.status == 200 && res.action=='deactive'){
                        $("#policystatuscheck_"+policy_id).val(2);
                        $("#policystatuscheck_"+policy_id).prop('checked',false);
                        toastr.success("Policy Deactivated",'Success',{timeOut: 5000});
                    }
                    if(res.status == 200 && res.action=='active'){
                        $("#policystatuscheck_"+policy_id).val(1);
                        $("#policystatuscheck_"+policy_id).prop('checked',true);
                        toastr.success("Policy activated",'Success',{timeOut: 5000});
                    }
                },
                error: function (data) {
                    toastr.error("Please try again",'Error',{timeOut: 5000});
                }
            });
        }

    </script>
    <!-- Policy JS end-->
@endsection

