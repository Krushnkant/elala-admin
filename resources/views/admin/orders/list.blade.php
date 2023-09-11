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
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Booking List</a></li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Booking List</h4>

                        {{-- <div class="custom-tab-1">
                            <ul class="nav nav-tabs mb-3">
                                <li class="nav-item order_page_tabs" data-tab="ALL_orders_tab"><a class="nav-link active show" data-toggle="tab" href="">ALL</a>
                                </li>
                                <li class="nav-item order_page_tabs" data-tab="NewOrder_orders_tab"><a class="nav-link" data-toggle="tab" href="">New Order</a>
                                </li>
                                <li class="nav-item order_page_tabs" data-tab="OutforDelivery_orders_tab"><a class="nav-link" data-toggle="tab" href="">Shipped</a>
                                </li>
                                <li class="nav-item order_page_tabs" data-tab="Delivered_orders_tab"><a class="nav-link" data-toggle="tab" href="">Delivered</a>
                                </li>
                                <li class="nav-item order_page_tabs" data-tab="ReturnRequest_orders_tab"><a class="nav-link" data-toggle="tab" href="">Return Request</a>
                                </li>
                                <li class="nav-item order_page_tabs" data-tab="Returned_orders_tab"><a class="nav-link" data-toggle="tab" href="">Returned</a>
                                </li>
                                <li class="nav-item order_page_tabs" data-tab="Cancelled_orders_tab"><a class="nav-link" data-toggle="tab" href="">Cancelled</a>
                                </li>
                            </ul>
                        </div> --}}
                        <div class="action-section">
                            <div class="row">
                                <div class="col-md-3">
                                    <select class="form-control comman-filter" id="host_filter" name="host_filter">
                                        <option value=""></option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 input-group">
                                    <input type="text" class="form-control custom_date_picker comman-filter" id="start_date" name="start_date" placeholder="Start Date" data-date-format="yyyy-mm-dd" data-date-end-date="0d"> <span class="input-group-append"><span class="input-group-text"><i class="mdi mdi-calendar-check"></i></span></span>
                                </div>
                                <div class="col-md-3 input-group">
                                    <input type="text" class="form-control custom_date_picker comman-filter" id="end_date" name="end_date" placeholder="End Date" data-date-format="yyyy-mm-dd" data-date-end-date="0d"> <span class="input-group-append"><span class="input-group-text"><i class="mdi mdi-calendar-check"></i></span></span>
                                </div>
                                
                            </div>
                        </div>

                        <div class="tab-pane fade show active table-responsive" id="ALL_orders_tab">
                            <table id="Order" class="table zero-configuration customNewtable" style="width:100%">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Booking Id</th>
                                    <th>Booking Info</th>
                                    <th>Customer Info</th>
                                    <th>Experience Detail</th>
                                    <th>Host By</th>
                                    <th>Experience Date</th>
                                    <th>Booking Date</th>
                                </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>No</th>
                                        <th>Booking Id</th>
                                        <th>Booking Info</th>
                                        <th>Customer Info</th>
                                        <th>Experience Detail</th>
                                        <th>Host By</th>
                                        <th>Experience Date</th>
                                        <th>Booking Date</th>
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
<!-- orders JS start -->
<script type="text/javascript">
var table;

function get_orders_page_tabType(){
    var tab_type;
    $('.order_page_tabs').each(function() {
        var thi = $(this);
        if($(thi).find('a').hasClass('show')){
            tab_type = $(thi).attr('data-tab');
        }
    });
    return tab_type;
}

$(document).ready(function() {
    order_table('',true);
    $('#host_filter').select2({
        width: '100%',
        placeholder: "Select User",
        allowClear: true
    });


    $('#Order tbody').on('click', 'td.details-control', function () {
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

    //$(".orderNoteBox").on('change cut paste', function(e) {
    $(document).on("change",".orderNoteBox",function() { 
        
        
        
        var orderNote = $(this).val();
        var orderid = $(this).attr('data-id');
        $('#ordercoverspin').show();
        $.ajax ({
            type:"POST",
            url: '{{ url("admin/updateOrdernote") }}',
            data: { _token: '{{ csrf_token() }}',orderid: orderid, orderNote: orderNote},
            
            success: function(res) {
                if(res['status'] == 200){
                    toastr.success("Order Note Updated",'Success',{timeOut: 5000});
                } else {
                    toastr.error("Please try again",'Error',{timeOut: 5000});
                }
            },
            complete: function(){
                $('#ordercoverspin').hide();
                order_table('',true);
            }
        });
    });
});

function format ( d ) {
    // `d` is the original data object for the row
    return d.table1;
}

function order_table(tab_type='',is_clearState=false){
    if(is_clearState){
        $('#Order').DataTable().state.clear();
    }

    var host_filter = $("#host_filter").val();
    var start_date = $("#start_date").val();
    var end_date = $("#end_date").val();

    table = $('#Order').DataTable({
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
            "url": "{{ url('admin/allOrderlist') }}",
            "dataType": "json",
            "type": "POST",
            "data":{ _token: '{{ csrf_token() }}',host_filter,start_date,end_date},
            // "dataSrc": ""
        },
        'columnDefs': [
            { "width": "20px", "targets": 0 },
            { "width": "80px", "targets": 1 },
            { "width": "130px", "targets": 2 },
            { "width": "110px", "targets": 3 },
            { "width": "170px", "targets": 4 },
            { "width": "110px", "targets": 5 },
            { "width": "100px", "targets": 6 },
            { "width": "80px", "targets": 7 },
        ],
        "columns": [
            {data: 'id', name: 'id', class: "text-center", orderable: false ,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {data: 'booking_id', name: 'booking_id', orderable: true, class: "text-left multirow"},
            {data: 'booking_info', name: 'booking_info', orderable: false, class: "text-left multirow"},
            {data: 'customer_info', name: 'customer_info', orderable: false, class: "text-center multirow"},
            {data: 'experience_details', name: 'experience_details', orderable: false, class: "text-left multirow"},
            {data: 'host_by', name: 'host_by', orderable: false, class: "text-center multirow"},
            {data: 'experience_date', name: 'experience_date', orderable: true, class: "text-left multirow"},
            {data: 'booking_date', name: 'booking_date', orderable: true, class: "text-left multirow"},
        ]
    });
}

$('body').on('change', '.comman-filter', function () {
    var tab_type = $(this).attr('data-tab');
    order_table(tab_type,true);
});

function editOrder(orderId) {
    var url = "{{ url('admin/viewOrder') }}" + "/" + orderId;
    window.open(url,"_blank");
}

$('body').on('click', '.order_page_tabs', function () {
    var tab_type = $(this).attr('data-tab');
    order_table(tab_type,true);
});

$('body').on('click', '#ApproveReturnRequestBtn', function () {
    $('#ordercoverspin').show();
    var tab_type = get_orders_page_tabType();
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
            order_table(tab_type);
        },
        error: function() {
            toastr.error("Please try again",'Error',{timeOut: 5000});
        }
    });
});

$('body').on('click', '#RejectReturnRequestBtn', function () {
    $('#ordercoverspin').show();
    var tab_type = get_orders_page_tabType();
    var order_id = $(this).attr('data-id');

    $.ajax ({
        type:"POST",
        url: '{{ url("admin/change_order_status") }}',
        data: {order_id: order_id, action: 'reject',  "_token": "{{csrf_token()}}"},
        success: function(res) {
            if(res['status'] == 200){
                toastr.success("Order Delivered",'Success',{timeOut: 5000});
            } else {
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        },
        complete: function(){
            $('#ordercoverspin').hide();
            order_table(tab_type);
        },
        error: function() {
            toastr.error("Please try again",'Error',{timeOut: 5000});
        }
    });
});

function getInvoiceData(order_id) {
    var url = "{{ url('admin/orders/pdf') }}" + "/" + order_id;
    window.open(url, "_blank");
}

$('body').on('click', '#VideoBtn', function () {
    var order_id = $(this).attr('data-id');
    $.get("{{ url('admin/orders') }}" +'/' + order_id +'/play_video', function (res) {
        console.log(res);
        $('#ReturnReqVideoModal').find('#ReturnReqVideo').attr('src',res['order_return_video']);
        // $('#ReturnReqVideoModal').find('#ReturnReqVideo').attr('type',res['type']);
    })
});

$('#ReturnReqVideoModal').on('hidden.bs.modal', function () {
    $(this).find("#ReturnReqVideo").attr('src','');
});

$('body').on('click', '#editTrackingBtn', function () {
    var order_id = $(this).attr('data-id');
    $('#order_id').val(order_id);
});


</script>
<!-- orders JS end -->
@endsection
