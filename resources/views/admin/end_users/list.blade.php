@extends('admin.layout')

@section('content')
<div class="row page-titles mx-0">
    <div class="col p-md-0">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active"><a href="javascript:void(0)">Customer List</a></li>
        </ol>
    </div>
</div>
<!-- row -->

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    {{-- <h4 class="card-title">Customer List</h4> --}}
                    <div class="action-section">
                        <?php $page_id = \App\Models\ProjectPage::where('route_url', \Illuminate\Support\Facades\Route::currentRouteName())->pluck('id')->first(); ?>
                        @if(getUSerRole()==1 || (getUSerRole()!=1 && is_write($page_id)) )
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#EndUserModal" id="AddEndUserBtn"><i class="fa fa-plus" aria-hidden="true"></i></button>
                        @endif
                    </div>

                    <div class="custom-tab-1">
                        <ul class="nav nav-tabs mb-3">
                            <li class="nav-item end_user_page_tabs" data-tab="all_end_user_tab"><a class="nav-link active show" data-toggle="tab" href="">All</a>
                            </li>

                            <li class="nav-item end_user_page_tabs" data-tab="active_end_user_tab"><a class="nav-link" data-toggle="tab" href="">Active</a>
                            </li>
                            <li class="nav-item end_user_page_tabs" data-tab="deactive_end_user_tab"><a class="nav-link" data-toggle="tab" href="">Deactive</a>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-pane fade show active table-responsive" id="all_end_user_tab">
                        <table id="all_end_users" class="table zero-configuration customNewtable" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Profile</th>
                                    <th>Contact Info</th>
                                    <th>Login Info</th>
                                    <th>User Status</th>
                                    <th>Registration Date</th>
                                    <th>Other</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>No</th>
                                    <th>Profile</th>
                                    <th>Contact Info</th>
                                    <th>Login Info</th>
                                    <th>User Status</th>
                                    <th>Registration Date</th>
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

<div class="modal fade" id="EndUserModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form class="form-valide" action="" id="enduserform" method="post" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="formtitle">Add User</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="attr-cover-spin" class="cover-spin"></div>
                    {{ csrf_field() }}
                    <div class="form-group ">
                        <label class="col-form-label" for="profilePic">Profile Image
                        </label>
                        <input type="file" class="form-control-file" id="profile_pic" onchange="" name="profile_pic">
                        <div id="profilepic-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        <img src="{{ asset('images/default_avatar.jpg') }}" class="" id="profilepic_image_show" height="50px" width="50px" style="margin-top: 5px;width:50px;">
                    </div>
                    <div class="form-group ">
                        <label class="col-form-label" for="full_name">Full Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control input-flat" id="full_name" name="full_name" placeholder="">
                        <div id="fullname-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                    </div>
                    <div class="form-group ">
                        <label class="col-form-label" for="mobile_no">Mobile No <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control input-flat" id="mobile_no" name="mobile_no" placeholder="">
                        <div id="mobileno-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                    </div>
                    <div class="form-group ">
                        <label class="col-form-label" for="email">E-mail <span class="text-danger">*</span>
                        </label>
                        <input type="email" class="form-control input-flat" id="email" name="email" placeholder="">
                        <div id="email-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                    </div>
                    <div class="form-group ">
                        <label class="col-form-label" for="password">Password <span class="text-danger">*</span>
                        </label>
                        <input type="password" class="form-control input-flat" id="password" name="password" placeholder="">
                        <div id="password-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                    </div>
                    <div class="form-group ">
                        <label class="col-form-label" for="gender">Gender
                        </label>
                        <div>
                            <label class="radio-inline mr-3"><input type="radio" name="gender" value="1" checked> Female</label>
                            <label class="radio-inline mr-3"><input type="radio" name="gender" value="2"> Male</label>
                            <label class="radio-inline mr-3"><input type="radio" name="gender" value="3"> Other</label>
                        </div>
                        <div id="gender-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                    </div>
                    <div class="form-group ">
                        <label class="col-form-label" for="dob">Date of Birth <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control custom_date_picker" id="dob" name="dob" placeholder="yyyy-mm-dd" data-date-format="yyyy-mm-dd" data-date-end-date="0d"> <span class="input-group-append"><span class="input-group-text"><i class="mdi mdi-calendar-check"></i></span></span>
                            <div id="dob-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="form-group ">
                        <label class="col-form-label" for="bio">Bio <span class="text-danger">*</span>
                        </label>
                        <textarea class="summernote" class="form-control" id="bio" name="bio"></textarea>
                        <div id="bio-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                    </div>

                </div>
                <div class="modal-footer">
                    <input type="hidden" name="user_id" id="user_id">
                    <button type="button" class="btn btn-outline-primary" id="save_newEndUserBtn">Save & New <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                    <button type="button" class="btn btn-primary" id="save_closeEndUserBtn">Save & Close <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="DeleteEndUserModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Remove Customer</h5>
            </div>
            <div class="modal-body">
                Are you sure you wish to remove this customer?
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal" type="button">Cancel</button>
                <button class="btn btn-danger" id="RemoveEndUserSubmit" type="submit">Remove <i class="fa fa-circle-o-notch fa-spin removeloadericonfa" style="display:none;"></i></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="UserFriendListModal">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formtitle">Friend List User</h5>
                <button type="button" class="close" data-dismiss="modal"><span>×</span>
                </button>
            </div>
            <div class="modal-body" id="friend-list-users">
                <div id="attr-cover-spin" class="cover-spin"></div>
            </div>
            <div class="modal-footer">
                <table id="all_friends" class="table zero-configuration customNewtable" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Image</th>
                            <th>User Info</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>No</th>
                            <th>Image</th>
                            <th>User Info</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<!-- end_user list JS start -->
<script type="text/javascript">
    $(document).ready(function() {
        end_user_page_tabs('', true);
    });

    function get_end_users_page_tabType() {
        var tab_type;
        $('.end_user_page_tabs').each(function() {
            var thi = $(this);
            if ($(thi).find('a').hasClass('show')) {
                tab_type = $(thi).attr('data-tab');
            }
        });
        return tab_type;
    }

    function save_user(btn, btn_type) {
        $(btn).prop('disabled', true);
        $(btn).find('.loadericonfa').show();

        var action = $(btn).attr('data-action');

        var formData = new FormData($("#enduserform")[0]);

        formData.append('action', action);

        var tab_type = get_end_users_page_tabType();

        $.ajax({
            type: 'POST',
            url: "{{ url('admin/addorupdateEnduser') }}",
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status == 'failed') {
                    $(btn).prop('disabled', false);
                    $(btn).find('.loadericonfa').hide();
                    if (res.errors.profile_pic) {
                        $('#profilepic-error').show().text(res.errors.profile_pic);
                    } else {
                        $('#profilepic-error').hide();
                    }

                    if (res.errors.profile_pic) {
                        $('#profilepic-error').show().text(res.errors.profile_pic);
                    } else {
                        $('#profilepic-error').hide();
                    }

                    if (res.errors.adhar_front) {
                        $('#adhar-front-error').show().text(res.errors.adhar_front);
                    } else {
                        $('#adhar-front-error').hide();
                    }
                    if (res.errors.adhar_back) {
                        $('#adhar-back-error').show().text(res.errors.adhar_back);
                    } else {
                        $('#adhar-back-error').hide();
                    }

                    if (res.errors.adhar_card_no) {
                        $('#adhar_card_no-error').show().text(res.errors.adhar_card_no);
                    } else {
                        $('#adhar_card_no-error').hide();
                    }

                    if (res.errors.first_name) {
                        $('#first_name-error').show().text(res.errors.first_name);
                    } else {
                        $('#first_name-error').hide();
                    }

                    if (res.errors.last_name) {
                        $('#last_name-error').show().text(res.errors.last_name);
                    } else {
                        $('#last_name-error').hide();
                    }

                    if (res.errors.mobile_no) {
                        $('#mobileno-error').show().text(res.errors.mobile_no);
                    } else {
                        $('#mobileno-error').hide();
                    }

                    if (res.errors.dob) {
                        $('#dob-error').show().text(res.errors.dob);
                    } else {
                        $('#dob-error').hide();
                    }

                    if (res.errors.email) {
                        $('#email-error').show().text(res.errors.email);
                    } else {
                        $('#email-error').hide();
                    }

                    if (res.errors.password) {
                        $('#password-error').show().text(res.errors.password);
                    } else {
                        $('#password-error').hide();
                    }
                }

                if (res.status == 200) {
                    if (btn_type == 'save_close') {
                        $("#EndUserModal").modal('hide');
                        $(btn).prop('disabled', false);
                        $(btn).find('.loadericonfa').hide();
                        if (res.action == 'add') {
                            end_user_page_tabs(tab_type, true);
                            toastr.success("Customer Added", 'Success', {
                                timeOut: 5000
                            });
                        }
                        if (res.action == 'update') {
                            end_user_page_tabs(tab_type);
                            toastr.success("Customer Updated", 'Success', {
                                timeOut: 5000
                            });
                        }
                    }

                    if (btn_type == 'save_new') {
                        $(btn).prop('disabled', false);
                        $(btn).find('.loadericonfa').hide();
                        $("#EndUserModal").find('form').trigger('reset');
                        $("#EndUserModal").find("#save_newEndUserBtn").removeAttr('data-action');
                        $("#EndUserModal").find("#save_closeEndUserBtn").removeAttr('data-action');
                        $("#EndUserModal").find("#save_newEndUserBtn").removeAttr('data-id');
                        $("#EndUserModal").find("#save_closeEndUserBtn").removeAttr('data-id');
                        $('#user_id').val("");
                        $('#profilepic-error').html("");
                        $('#first_name-error').html("");
                        $('#last_name-error').html("");
                        $('#mobileno-error').html("");
                        $('#dob-error').html("");
                        $('#gender-error').html("");
                        $('#email-error').html("");
                        $('#password-error').html("");
                        var default_image = "{{ url('images/default_avatar.jpg') }}";
                        $('#profilepic_image_show').attr('src', default_image);
                        $("#first_name").focus();
                        if (res.action == 'add') {
                            end_user_page_tabs(tab_type, true);
                            toastr.success("Customer Added", 'Success', {
                                timeOut: 5000
                            });
                        }
                        if (res.action == 'update') {
                            end_user_page_tabs(tab_type);
                            toastr.success("Customer Updated", 'Success', {
                                timeOut: 5000
                            });
                        }
                    }
                }

                if (res.status == 400) {
                    $("#EndUserModal").modal('hide');
                    $(btn).prop('disabled', false);
                    $(btn).find('.loadericonfa').hide();
                    end_user_page_tabs(tab_type);
                    toastr.error("Please try again", 'Error', {
                        timeOut: 5000
                    });
                }
            },
            error: function(data) {
                $("#EndUserModal").modal('hide');
                $(btn).prop('disabled', false);
                $(btn).find('.loadericonfa').hide();
                end_user_page_tabs(tab_type);
                toastr.error("Please try again", 'Error', {
                    timeOut: 5000
                });
            }
        });
    }

    $('body').on('click', '#save_newEndUserBtn', function() {
        save_user($(this), 'save_new');
    });

    $('body').on('click', '#save_closeEndUserBtn', function() {
        save_user($(this), 'save_close');
    });

    $('#EndUserModal').on('shown.bs.modal', function(e) {
        $("#first_name").focus();
    });

    $('#profile_pic').change(function() {
        $('#profilepic-error').hide();
        var file = this.files[0];
        var fileType = file["type"];
        var validImageTypes = ["image/jpeg", "image/png", "image/jpg"];
        if ($.inArray(fileType, validImageTypes) < 0) {
            $('#profilepic-error').show().text("Please provide a Valid Extension Image(e.g: .jpg .png)");
            var default_image = "{{ url('images/default_avatar.jpg') }}";
            $('#profilepic_image_show').attr('src', default_image);
        } else {
            let reader = new FileReader();
            reader.onload = (e) => {
                $('#profilepic_image_show').attr('src', e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    $('#adhar_front').change(function() {
        $('#adhar_front-error').hide();
        var file = this.files[0];
        var fileType = file["type"];
        var validImageTypes = ["image/jpeg", "image/png", "image/jpg"];
        if ($.inArray(fileType, validImageTypes) < 0) {
            $('#adhar_front-error').show().text("Please provide a Valid Extension Image(e.g: .jpg .png)");
            var default_image = "{{ url('images/default_avatar.jpg') }}";
            $('#adhar_front_show').attr('src', default_image);
        } else {
            let reader = new FileReader();
            reader.onload = (e) => {
                $('#adhar_front_show').attr('src', e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    $('#adhar_back').change(function() {
        $('#adhar_back-error').hide();
        var file = this.files[0];
        var fileType = file["type"];
        var validImageTypes = ["image/jpeg", "image/png", "image/jpg"];
        if ($.inArray(fileType, validImageTypes) < 0) {
            $('#adhar_back-error').show().text("Please provide a Valid Extension Image(e.g: .jpg .png)");
            var default_image = "{{ url('images/default_avatar.jpg') }}";
            $('#adhar_back_show').attr('src', default_image);
        } else {
            let reader = new FileReader();
            reader.onload = (e) => {
                $('#adhar_back_show').attr('src', e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    $('#EndUserModal').on('hidden.bs.modal', function() {
        //end_user_page_tabs(tab_type,true);
        $(this).find('form').trigger('reset');
        $(this).find("#save_newEndUserBtn").removeAttr('data-action');
        $(this).find("#save_closeEndUserBtn").removeAttr('data-action');
        $(this).find("#save_newEndUserBtn").removeAttr('data-id');
        $(this).find("#save_closeEndUserBtn").removeAttr('data-id');
        $('#user_id').val("");
        $('#profilepic-error').html("");
        $('#first_name-error').html("");
        $('#last_name-error').html("");
        $('#mobileno-error').html("");
        $('#dob-error').html("");
        $('#gender-error').html("");
        $('#email-error').html("");
        $('#password-error').html("");
        $('#bio-error').html("");


        var default_image = "{{ url('images/default_avatar.jpg') }}";
        $('#profilepic_image_show').attr('src', default_image);

        //location.reload();
        //end_user_page_tabs(tab_type,true);
    });

    $('#DeleteEndUserModal').on('hidden.bs.modal', function() {
        $(this).find("#RemoveEndUserSubmit").removeAttr('data-id');
    });

    function end_user_page_tabs(tab_type = '', is_clearState = false) {
        if (is_clearState) {
            $('#all_end_users').DataTable().state.clear();
        }

        $('#all_end_users').DataTable({
            "destroy": true,
            "processing": true,
            "serverSide": true,
            'stateSave': function() {
                if (is_clearState) {
                    return false;
                } else {
                    return true;
                }
            },
            "ajax": {
                "url": "{{ url('admin/allEnduserlist') }}",
                "dataType": "json",
                "type": "POST",
                "data": {
                    _token: '{{ csrf_token() }}',
                    tab_type: tab_type
                },
                // "dataSrc": ""
            },
            'columnDefs': [{
                    "width": "50px",
                    "targets": 0
                },
                {
                    "width": "145px",
                    "targets": 1
                },
                {
                    "width": "145px",
                    "targets": 2
                },
                {
                    "width": "200px",
                    "targets": 3
                },
                {
                    "width": "120px",
                    "targets": 4
                },
                {
                    "width": "120px",
                    "targets": 5
                },
                {
                    "width": "180px",
                    "targets": 6
                },
            ],
            "columns": [{
                    data: 'id',
                    name: 'id',
                    class: "text-center",
                    orderable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'profile_pic',
                    name: 'profile_pic',
                    class: "text-center multirow"
                },
                {
                    data: 'contact_info',
                    name: 'contact_info',
                    class: "text-left multirow",
                    orderable: false
                },
                {
                    data: 'login_info',
                    name: 'login_info',
                    class: "text-left multirow",
                    orderable: false
                },
                {
                    data: 'estatus',
                    name: 'estatus',
                    orderable: false,
                    searchable: false,
                    class: "text-center"
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    searchable: false,
                    class: "text-left"
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    class: "text-center"
                },
            ]
        });
    }

    $(".end_user_page_tabs").click(function() {
        var tab_type = $(this).attr('data-tab');
        end_user_page_tabs(tab_type, true);
    });

    function changeEndUserStatus(user_id) {
        var tab_type = get_end_users_page_tabType();

        $.ajax({
            type: 'GET',
            url: "{{ url('admin/changeEnduserstatus') }}" + '/' + user_id,
            success: function(res) {
                if (res.status == 200 && res.action == 'deactive') {
                    $("#EndUserstatuscheck_" + user_id).val(2);
                    $("#EndUserstatuscheck_" + user_id).prop('checked', false);
                    end_user_page_tabs(tab_type);
                    toastr.success("Customer Deactivated", 'Success', {
                        timeOut: 5000
                    });
                }
                if (res.status == 200 && res.action == 'active') {
                    $("#EndUserstatuscheck_" + user_id).val(1);
                    $("#EndUserstatuscheck_" + user_id).prop('checked', true);
                    end_user_page_tabs(tab_type);
                    toastr.success("Customer activated", 'Success', {
                        timeOut: 5000
                    });
                }
            },
            error: function(data) {
                toastr.error("Please try again", 'Error', {
                    timeOut: 5000
                });
            }
        });
    }

    $('body').on('click', '#AddEndUserBtn', function(e) {
        $("#EndUserModal").find('.modal-title').html("Add Customer");
    });

    $('body').on('click', '#editEndUserBtn', function() {
        var user_id = $(this).attr('data-id');

        //$("#parent_user_id option[value='"+ user_id +"']").remove();

        $.get("{{ url('admin/users') }}" + '/' + user_id + '/edit', function(data) {

            $('#EndUserModal').find('.modal-title').html("Edit Customer");
            $('#EndUserModal').find('#save_closeEndUserBtn').attr("data-action", "update");
            $('#EndUserModal').find('#save_newEndUserBtn').attr("data-action", "update");
            $('#EndUserModal').find('#save_closeEndUserBtn').attr("data-id", user_id);
            $('#EndUserModal').find('#save_newEndUserBtn').attr("data-id", user_id);
            $('#user_id').val(data.id);
            if (data.profile_pic == null) {
                var default_image = "{{ url('images/default_avatar.jpg') }}";
                $('#profilepic_image_show').attr('src', default_image);
            } else {
                var profile_pic = data.profile_pic;
                $('#profilepic_image_show').attr('src', profile_pic);
            }

            $('#full_name').val(data.full_name);
            $('#mobile_no').val(data.mobile_no);
            $('#dob').val(data.dob);
            $('#email').val(data.email);
            $('#password').val(data.decrypted_password);
            //$('#bio').val(data.bio);
            $('#bio').summernote('code', data.bio);
            $("input[name=gender][value=" + data.gender + "]").prop('checked', true);
        })
    });

    $('body').on('click', '#deleteEndUserBtn', function(e) {
        // e.preventDefault();
        var delete_user_id = $(this).attr('data-id');
        $("#DeleteEndUserModal").find('#RemoveEndUserSubmit').attr('data-id', delete_user_id);
    });

    $('body').on('click', '#RemoveEndUserSubmit', function(e) {
        $('#RemoveEndUserSubmit').prop('disabled', true);
        $(this).find('.removeloadericonfa').show();
        e.preventDefault();
        var remove_user_id = $(this).attr('data-id');

        var tab_type = get_end_users_page_tabType();

        $.ajax({
            type: 'GET',
            url: "{{ url('admin/end_users') }}" + '/' + remove_user_id + '/delete',
            success: function(res) {
                if (res.status == 200) {
                    $("#DeleteEndUserModal").modal('hide');
                    $('#RemoveEndUserSubmit').prop('disabled', false);
                    $("#RemoveEndUserSubmit").find('.removeloadericonfa').hide();
                    end_user_page_tabs(tab_type);
                    toastr.success("Customer Deleted", 'Success', {
                        timeOut: 5000
                    });
                }

                if (res.status == 400) {
                    $("#DeleteEndUserModal").modal('hide');
                    $('#RemoveEndUserSubmit').prop('disabled', false);
                    $("#RemoveEndUserSubmit").find('.removeloadericonfa').hide();
                    end_user_page_tabs(tab_type);
                    toastr.error("Please try again", 'Error', {
                        timeOut: 5000
                    });
                }
            },
            error: function(data) {
                $("#DeleteEndUserModal").modal('hide');
                $('#RemoveEndUserSubmit').prop('disabled', false);
                $("#RemoveEndUserSubmit").find('.removeloadericonfa').hide();
                end_user_page_tabs(tab_type);
                toastr.error("Please try again", 'Error', {
                    timeOut: 5000
                });
            }
        });
    });

    $('#bio').summernote({
        height: 50
    });

    $('body').on('click', '#friendList', function(e) {
        let id = $(this).attr('data-id')

        $('#all_friends').DataTable({
            "destroy": true,
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ url('admin/allfriendlist') }}/" + id,
                "dataType": "json",
                "type": "POST",
                "data": {
                    _token: '{{ csrf_token() }}',
                },
                // "dataSrc": ""
            },
            'columnDefs': [{
                    "width": "50px",
                    "targets": 0
                },
                {
                    "width": "145px",
                    "targets": 1
                },
                {
                    "width": "145px",
                    "targets": 2
                },
            ],
            "columns": [{
                    data: 'id',
                    name: 'id',
                    class: "text-center",
                    orderable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'profile_pic',
                    name: 'profile_pic',
                    class: "text-center multirow",
                },
                {
                    data: 'user_info',
                    name: 'user_info',
                    class: "text-left multirow",
                    orderable: false
                },
            ]
        });

    })
</script>
<!-- end_user list JS end -->
@endsection