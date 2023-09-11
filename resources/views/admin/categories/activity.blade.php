@extends('admin.layout')

@section('content')
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Activity Log</a></li>
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
                                <table id="Category" class="table zero-configuration customNewtable" style="width:100%">
                                    <thead>
                                    <tr>
                                        <th>Sr. No</th>
                                        <th>title</th>
                                        <th>type</th>
                                        <th>item_id</th>
                                        <th>user_id</th>
                                        <th>action</th>
                                    </tr>
                                    </thead>
                                    <tfoot>
                                    </tfoot>
                                </table>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="DeleteCategoryModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Remove Category</h5>
                </div>
                <div class="modal-body">
                    Are you sure you wish to remove this Category?
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal" type="button">Cancel</button>
                    <button class="btn btn-danger" id="RemoveCategorySubmit" type="submit">Remove <i class="fa fa-circle-o-notch fa-spin removeloadericonfa" style="display:none;"></i></button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<!-- category JS start -->
<script type="text/javascript">



function category_table(is_clearState=false){
    var cat_id = "{{ isset($id)?$id:0 }}";
    if(is_clearState){
        $('#Category').DataTable().state.clear();
    }

    $('#Category').DataTable({
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
            "url": "{{ url('admin/allactivitylist') }}",
            "dataType": "json",
            "type": "POST",
            "data":{ _token: '{{ csrf_token() }}' },
            // "dataSrc": ""
        },
        'columnDefs': [
            { "width": "50px", "targets": 0 },
            { "width": "120px", "targets": 1 },
            { "width": "120px", "targets": 2 },
            { "width": "120px", "targets": 3},
            { "width": "120px", "targets": 4 },
            { "width": "120px", "targets": 5},
        ],
        "columns": [
            {data: 'sr_no', name: 'sr_no', class: "text-center", orderable: false,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {data: 'title', name: 'title', orderable: false, searchable: false, class: "text-center"},
            {data: 'type', name: 'type', searchable: false, class: "text-left"},
            {data: 'item_id', name: 'item_id', searchable: false, class: "text-left"},
            {data: 'user_id', name: 'user_id', searchable: false, class: "text-left"},
            {data: 'action', name: 'action', orderable: false, searchable: false, class: "text-center"},
        ]
    });
}
category_table()
$(document).ready(function() {
    category_table('',true);
    });
</script>
<!-- category JS end -->
@endsection

