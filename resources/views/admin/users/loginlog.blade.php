@extends('admin.layout')

@section('content')
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">User List</a></li>
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
                            <table id="all_users" class="table zero-configuration customNewtable" style="width:100%">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Image</th>
                                    <th>User Info</th>
                                    <th>Contact Info</th>
                                    <th>Login Date</th>
                                 
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th>No</th>
                                    <th>Image</th>
                                    <th>User Info</th>
                                    <th>Contact Info</th>
                                    <th>Login Date</th>
                                </tr>
                                </tfoot>
                            </table>
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
    $(document).ready(function() {
        user_page_tabs('',true);

        $('#designation_id').select2({
            width: '100%',
            placeholder: "Select Designation",
            allowClear: true
        }).trigger('change');
    });

    function get_users_page_tabType(){
        var tab_type;
        $('.user_page_tabs').each(function() {
            var thi = $(this);
            if($(thi).find('a').hasClass('show')){
                tab_type = $(thi).attr('data-tab');
            }
        });
        return tab_type;
    }

   


    function user_page_tabs(tab_type='',is_clearState=false) {
       
        if(is_clearState){
            $('#all_users').DataTable().state.clear();
        }

        $('#all_users').DataTable({
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
                "url": "{{ url('admin/allloginloglist') }}",
                "dataType": "json",
                "type": "POST",
                "data":{ _token: '{{ csrf_token() }}'},
                // "dataSrc": ""
            },
            'columnDefs': [
                { "width": "50px", "targets": 0 },
                { "width": "145px", "targets": 1 },
                { "width": "145px", "targets": 2 },
                { "width": "165px", "targets": 3 },
                { "width": "230px", "targets": 4 },
            ],
            "columns": [
                {data: 'id', name: 'id', class: "text-center", orderable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {data: 'profile_pic', name: 'profile_pic', class: "text-center multirow"},
                {data: 'user_info', name: 'user_info', class: "text-left multirow", orderable: false},
                {data: 'contact_info', name: 'contact_info', class: "text-left multirow", orderable: false},
                {data: 'created_at', name: 'created_at', searchable: false, class: "text-left"},
               
            ]
        });
    }

    $(".user_page_tabs").click(function() {
        var tab_type = $(this).attr('data-tab');
        user_page_tabs(tab_type,true);
    });

</script>
<!-- user list JS end -->
@endsection

