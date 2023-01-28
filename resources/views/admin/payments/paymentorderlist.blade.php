@extends('admin.layout')

@section('content')
<style>
    table#Order td.text-center span.label {
    display: block;
    width: max-content;
    margin: 0 auto;
    margin-bottom: 5px;
}
</style>
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Payment Order List</a></li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Payment Order List</h4>


                        <div class="tab-pane fade show active table-responsive" id="ALL_payments_tab">
                            <table id="Payment" class="table zero-configuration customNewtable" style="width:100%">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Booking Id</th>
                                    <th>Amount</th>
                                    <th>Payment Date</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th>No</th>
                                    <th>Booking Id</th>
                                    <th>Amount</th>
                                    <th>Payment Date</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>
                    <div id="ordercoverspin" class="cover-spin"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ReturnReqVideoModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                </div>
                <div class="modal-body">
                    {{--<video width="400" controls>
                        <source src="" type="video/mp4" id="ReturnReqVideo">
                        Your browser does not support HTML video.
                    </video>--}}
                    <iframe id="ReturnReqVideo" class="embed-responsive-item" width="450" height="315" src="" allowfullscreen></iframe>
                </div>
                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="TrackingModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form class="form-valide" action="" id="trackingform" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formtitle">Add Tracking URL</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="attr-cover-spin" class="cover-spin"></div>
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label class="col-form-label" for="tracking_url" >Tracking URL <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control input-flat" id="tracking_url" name="tracking_url" placeholder="">
                            <div id="tracking_url-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                       
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="order_id" id="order_id">
                        <button type="button" class="btn btn-outline-primary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="AddTrackingBtn">Save <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
<!-- payments JS start -->
<script type="text/javascript">
var table;

function get_payments_page_tabType(){
    var tab_type;
    $('.payment_page_tabs').each(function() {
        var thi = $(this);
        if($(thi).find('a').hasClass('show')){
            tab_type = $(thi).attr('data-tab');
        }
    });
    return tab_type;
}

$(document).ready(function() {
    
    payment_table('next_payments_tab',true);
    $('#host_filter').select2({
        width: '100%',
        placeholder: "Select User",
        allowClear: true
    });


    $('#Payment tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row( tr );

        if ( row.child.isShown() ) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        } else {
            // Open this row
            row.child( format(row.data()) ).show();
            tr.addClass('shown');
        }
    });


});

function format ( d ) {
    // `d` is the original data object for the row
    return d.table1;
}

function payment_table(tab_type='',is_clearState=false){
    if(is_clearState){
        $('#Payment').DataTable().state.clear();
    }

    var host_filter = $("#host_filter").val();
    var start_date = $("#start_date").val();
    var end_date = $("#end_date").val();
    var payment_id = "{{ $id }}";

    table = $('#Payment').DataTable({
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
            "url": "{{ url('admin/allpaymentorderslist') }}",
            "dataType": "json",
            "type": "POST",
            "data":{ _token: '{{ csrf_token() }}',tab_type,host_filter,start_date,end_date,payment_id},
            // "dataSrc": ""
        },
        'columnDefs': [
            { "width": "20px", "targets": 0 },
            { "width": "150px", "targets": 1 },
            { "width": "130px", "targets": 2 },
            { "width": "110px", "targets": 3 },
       
        ],
        "columns": [
            {data: 'id', name: 'id', class: "text-center", orderable: false ,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {data: 'booking_id', name: 'booking_id', orderable: false, class: "text-left multirow"},
            {data: 'amount', name: 'amount', orderable: false, class: "text-left multirow"},
            {data: 'created_at', name: 'created_at', orderable: false, class: "text-left multirow"},
        ]
    });
}

$('body').on('change', '.comman-filter', function () {
    $('.payment_page_tabs').each(function() {
        var thi = $(this);
        if($(thi).find('a').hasClass('show')){
            tab_type = $(thi).attr('data-tab');
        }
    });
    payment_table(tab_type,true);
});



$('body').on('click', '.payment_page_tabs', function () {
    var tab_type = $(this).attr('data-tab');
    payment_table(tab_type,true);
});

$('body').on('click', '#ApproveReturnRequestBtn', function () {
    $('#ordercoverspin').show();
    var tab_type = get_payments_page_tabType();
    var order_id = $(this).attr('data-id');

    $.ajax ({
        type:"POST",
        url: '{{ url("admin/change_order_status") }}',
        data: {order_id: order_id, action: 'approve',  "_token": "{{csrf_token()}}"},
        success: function(res) {
            if(res['status'] == 200){
                toastr.success("Order Returned",'Success',{timeOut: 5000});
            } else {
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        },
        complete: function(){
            $('#ordercoverspin').hide();
            payment_table(tab_type);
        },
        error: function() {
            toastr.error("Please try again",'Error',{timeOut: 5000});
        }
    });
});



</script>
<!-- payments JS end -->
@endsection
