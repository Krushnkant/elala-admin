@extends('admin.layout')

@section('content')
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Languages </a></li>
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
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#LanguageModal" id="AddBtn_Language"><i class="fa fa-plus" aria-hidden="true"></i></button>
                            @endif
                        </div>

                       

                        <div class="tab-pane fade show active table-responsive" id="languages_tab">
                            <table id="languages_page_table" class="table zero-configuration customNewtable" style="width:100%">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th>No</th>
                                    <th>Title</th>
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

    <div class="modal fade" id="LanguageModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form class="form-valide" action="" id="languagesform" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formtitle">Add New Language</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="attr-cover-spin" class="cover-spin"></div>
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label class="col-form-label" for="title" id="label_title">Language Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control input-flat" id="title" name="title" placeholder="">
                            <div id="title-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                       
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="language_id" id="language_id">
                        <button type="button" class="btn btn-outline-primary" id="save_newLanguageBtn">Save & New <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                        <button type="button" class="btn btn-primary" id="save_closeLanguageBtn">Save & Close <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="DeleteLanguageModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Remove Attribute</h5>
                </div>
                <div class="modal-body">
                    Are you sure you wish to remove this Attribute?
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal" type="button">Cancel</button>
                    <button class="btn btn-danger" id="Removelanguagesubmit" type="submit">Remove <i class="fa fa-circle-o-notch fa-spin removeloadericonfa" style="display:none;"></i></button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <!-- language JS start -->
    <script type="text/javascript">
        function get_languages_page_tabType(){
            var tab_type;
            $('.languages_page_tabs').each(function() {
                var thi = $(this);
                if($(thi).find('a').hasClass('show')){
                    tab_type = $(thi).attr('data-tab');
                }
            });
            return tab_type;
        }

        function save_language(btn,btn_type){
            $(btn).prop('disabled',true);
            $(btn).find('.loadericonfa').show();
            var formData = $("#languagesform").serializeArray();

            var tab_type = get_languages_page_tabType();

            formData.push({ name: "tab_type", value: tab_type });

            $.ajax({
                type: 'POST',
                url: "{{ url('admin/addorupdatelanguage') }}",
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
                            $("#LanguageModal").modal('hide');
                            $(btn).find('.loadericonfa').hide();
                            $(btn).prop('disabled',false);
                            if(res.action == 'add'){
                                language_page_tabs(tab_type,true);
                                toastr.success("Language Added",'Success',{timeOut: 5000});
                            }
                            if(res.action == 'update'){
                                language_page_tabs(tab_type);
                                toastr.success("Language Updated",'Success',{timeOut: 5000});
                            }
                        }

                        if(btn_type == 'save_new'){
                            $(btn).find('.loadericonfa').hide();
                            $(btn).prop('disabled',false);
                            $("#LanguageModal").find('form').trigger('reset');
                            $('#language_id').val("");
                            $('#title-error').html("");
                            $("#LanguageModal").find("#save_newLanguageBtn").removeAttr('data-action');
                            $("#LanguageModal").find("#save_closeLanguageBtn").removeAttr('data-action');
                            $("#LanguageModal").find("#save_newLanguageBtn").removeAttr('data-id');
                            $("#LanguageModal").find("#save_closeLanguageBtn").removeAttr('data-id');
                            $("#title").focus();
                            if(res.action == 'add'){
                                language_page_tabs(tab_type,true);
                                toastr.success("Language Added",'Success',{timeOut: 5000});
                            }
                            if(res.action == 'update'){
                                language_page_tabs(tab_type);
                                toastr.success("Language Updated",'Success',{timeOut: 5000});
                            }
                        }
                    }

                    if(res.status == 400){
                        $("#LanguageModal").modal('hide');
                        $(btn).find('.loadericonfa').hide();
                        $(btn).prop('disabled',false);
                        language_page_tabs(tab_type);
                        toastr.error("Please try again",'Error',{timeOut: 5000});
                    }
                },
                error: function (data) {
                    $("#LanguageModal").modal('hide');
                    $(btn).find('.loadericonfa').hide();
                    $(btn).prop('disabled',false);
                    language_page_tabs(tab_type);
                    toastr.error("Please try again",'Error',{timeOut: 5000});
                }
            });
        }

        $('body').on('click', '#save_newLanguageBtn', function () {
            save_language($(this),'save_new');
        });

        $('body').on('click', '#save_closeLanguageBtn', function () {
            save_language($(this),'save_close');
        });

        $('#LanguageModal').on('shown.bs.modal', function (e) {
            $("#title").focus();
        });

        $('#LanguageModal').on('hidden.bs.modal', function () {
            $(this).find('form').trigger('reset');
            $(this).find("#save_newLanguageBtn").removeAttr('data-action');
            $(this).find("#save_closeLanguageBtn").removeAttr('data-action');
            $(this).find("#save_newLanguageBtn").removeAttr('data-id');
            $(this).find("#save_closeLanguageBtn").removeAttr('data-id');
            $('#language_id').val("");
            $('#title-error').html("");
        });

        $('#DeleteLanguageModal').on('hidden.bs.modal', function () {
            $(this).find("#Removelanguagesubmit").removeAttr('data-id');
        });

        $(document).ready(function() {
            language_page_tabs('',true);
        });

        function language_page_tabs(tab_type='',is_clearState=false) {
            if(is_clearState){
                $('#languages_page_table').DataTable().state.clear();
            }

            $('#languages_page_table').DataTable({
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
                    "url": "{{ url('admin/alllanguageslist') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{ _token: '{{ csrf_token() }}' ,tab_type: tab_type},
                    // "dataSrc": ""
                },
                'columnDefs': [
                    { "width": "50px", "targets": 0 },
                    { "width": "145px", "targets": 1 },
                    { "width": "75px", "targets": 2 },
                    { "width": "120px", "targets": 3 },
                    { "width": "115px", "targets": 4 },
                ],
                "columns": [
                    {data: 'id', name: 'id', class: "text-center" , orderable: false ,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {data: 'title', name: 'title', class: "text-left"},
                    {data: 'estatus', name: 'estatus', orderable: false, searchable: false, class: "text-center"},
                    {data: 'created_at', name: 'created_at', searchable: false, class: "text-left"},
                    {data: 'action', name: 'action', orderable: false, searchable: false, class: "text-center"},
                ]
            });
        }

        $(".language_page_tabs").click(function() {
            var tab_type = $(this).attr('data-tab');
            language_page_tabs(tab_type,true);
        });

        $('body').on('click', '#AddBtn_AttrSpec', function () {
            var edit_language_id = $(this).attr('data-id');
           
        });

        $('body').on('click', '#editLanguageBtn', function () {
            var edit_language_id = $(this).attr('data-id');
            $('#LanguageModal').find('.modal-title').html("Edit Language");
            $.get("{{ url('admin/language') }}" +'/' + edit_language_id +'/edit', function (data) {
                $('#LanguageModal').find('#save_newLanguageBtn').attr("data-action","update");
                $('#LanguageModal').find('#save_closeLanguageBtn').attr("data-action","update");
                $('#LanguageModal').find('#save_newLanguageBtn').attr("data-id",edit_language_id);
                $('#LanguageModal').find('#save_closeLanguageBtn').attr("data-id",edit_language_id);
                $('#language_id').val(data.id);
                $('#title').val(data.title);
                
            });
        });

        $('body').on('click', '#deleteLanguageBtn', function (e) {
            // e.preventDefault();
            var delete_language_id = $(this).attr('data-id');
            $("#DeleteLanguageModal").find('#Removelanguagesubmit').attr('data-id',delete_language_id);
            $('#DeleteLanguageModal').find('.modal-title').html("Remove Language");
            $('#DeleteLanguageModal').find('.modal-body').html("Are you sure you wish to remove this Language?");
           
        });

        $('body').on('click', '#Removelanguagesubmit', function (e) {
            $('#Removelanguagesubmit').prop('disabled',true);
            $(this).find('.removeloadericonfa').show();
            e.preventDefault();
            var remove_language_id = $(this).attr('data-id');

            $.ajax({
                type: 'GET',
                url: "{{ url('admin/language') }}" +'/' + remove_language_id +'/delete',
                success: function (res) {
                    if(res.status == 200){
                        $("#DeleteLanguageModal").modal('hide');
                        $('#Removelanguagesubmit').prop('disabled',false);
                        $("#Removelanguagesubmit").find('.removeloadericonfa').hide();
                        language_page_tabs();
                        // redrawAfterDelete();
                        toastr.success("Language Deleted",'Success',{timeOut: 5000});
                    }

                    if(res.status == 400){
                        $("#DeleteLanguageModal").modal('hide');
                        $('#Removelanguagesubmit').prop('disabled',false);
                        $("#Removelanguagesubmit").find('.removeloadericonfa').hide();
                        language_page_tabs();
                        toastr.error("Please try again",'Error',{timeOut: 5000});
                    }
                },
                error: function (data) {
                    $("#DeleteLanguageModal").modal('hide');
                    $('#Removelanguagesubmit').prop('disabled',false);
                    $("#Removelanguagesubmit").find('.removeloadericonfa').hide();
                    language_page_tabs();
                    toastr.error("Please try again",'Error',{timeOut: 5000});
                }
            });
        });

        function chageLanguageStatus(language_id) {
            $.ajax({
                type: 'GET',
                url: "{{ url('admin/chagelanguagestatus') }}" +'/' + language_id,
                success: function (res) {
                    if(res.status == 200 && res.action=='deactive'){
                        $("#languagestatuscheck_"+language_id).val(2);
                        $("#languagestatuscheck_"+language_id).prop('checked',false);
                        toastr.success("Language Deactivated",'Success',{timeOut: 5000});
                    }
                    if(res.status == 200 && res.action=='active'){
                        $("#languagestatuscheck_"+language_id).val(1);
                        $("#languagestatuscheck_"+language_id).prop('checked',true);
                        toastr.success("Language activated",'Success',{timeOut: 5000});
                    }
                },
                error: function (data) {
                    toastr.error("Please try again",'Error',{timeOut: 5000});
                }
            });
        }

    </script>
    <!-- language JS end-->
@endsection

