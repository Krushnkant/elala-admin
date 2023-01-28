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
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Payment List</a></li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Payment List</h4>

                        <div class="custom-tab-1">
                            <ul class="nav nav-tabs mb-3">
                                <li class="nav-item payment_page_tabs" data-tab="next_payments_tab"><a class="nav-link active show" data-toggle="tab" href="">Next Payment</a>
                                </li>
                                <li class="nav-item payment_page_tabs" data-tab="last_payments_tab"><a class="nav-link" data-toggle="tab" href="">Last Payment</a>
                                </li>
                                <li class="nav-item payment_page_tabs" data-tab="past_payments_tab"><a class="nav-link" data-toggle="tab" href="">Past Payment</a>
                                </li>
                                <li class="nav-item payment_page_tabs" data-tab="upcoming_payments_tab"><a class="nav-link" data-toggle="tab" href="">Upcoming Payment</a>
                                </li>
                               
                              
                            </ul>
                        </div>
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
                                    <input type="text" class="form-control custom_date_picker comman-filter" id="start_date" name="start_date" placeholder="Start Date" data-date-format="yyyy-mm-dd" > <span class="input-group-append"><span class="input-group-text"><i class="mdi mdi-calendar-check"></i></span></span>
                                </div>
                                <div class="col-md-3 input-group">
                                    <input type="text" class="form-control custom_date_picker comman-filter" id="end_date" name="end_date" placeholder="End Date" data-date-format="yyyy-mm-dd" > <span class="input-group-append"><span class="input-group-text"><i class="mdi mdi-calendar-check"></i></span></span>
                                </div>
                                <div class="col-md-3 input-group">
                                <button style="margin-bottom: 10px" class="btn btn-primary delete_all" data-url="{{ url('admin/paymentsuccess') }}">Payment All Selected</button> 
                            </div>
                        </div>
                        </div>
                        

                        <div class="tab-pane fade show active table-responsive" id="ALL_payments_tab">
                            <table id="Payment" class="table zero-configuration customNewtable" style="width:100%">
                                <thead>
                                <tr>
                                    <th><input type="checkbox" id="master"></th>
                                    <th>No</th>
                                    <th>User</th>
                                    <th>Amount</th>
                                    <th>Payment Date</th>
                                    <th>Order</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th></th>
                                    <th>No</th>
                                    <th>User</th>
                                    <th>Amount</th>
                                    <th>Payment Date</th>
                                    <th>Order</th>
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

<script type="text/javascript">  
    $(document).ready(function () {  
  
        $('#master').on('click', function(e) {  
         if($(this).is(':checked',true))    
         {  
            $(".sub_chk").prop('checked', true);    
         } else {    
            $(".sub_chk").prop('checked',false);    
         }    
        });  
  
        $('.delete_all').on('click', function(e) {  
  
            var allVals = [];    
            $(".sub_chk:checked").each(function() {    
                allVals.push($(this).attr('data-id'));  
            });    
  
            if(allVals.length <=0)    
            {    
                alert("Please select row.");    
            }  else {    
  
                var check = confirm("Are you sure you want to delete this row?");    
                if(check == true){    
  
                    var join_selected_values = allVals.join(",");   
                   
                    $.ajax({  
                        url: $(this).data('url'),  
                        type: 'post',  
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},  
                        data: 'ids='+join_selected_values,  
                        success: function (data) {  
                            console.log($data);
                            
                            if(data.status == 200){
                 
                                payment_table("",true);
                            // redrawAfterDelete();
                            toastr.success("Payment Successfully",'Success',{timeOut: 5000});
                    }
                        },  
                        error: function (data) {  
                            console.log(data.responseText); 
                        }  
                    });  
  
                  $.each(allVals, function( index, value ) {  
                      $('table tr').filter("[data-row-id='" + value + "']").remove();  
                  });  
                }    
            }    
        });  
  
        // $('[data-toggle=confirmation]').confirmation({  
        //     rootSelector: '[data-toggle=confirmation]',  
        //     onConfirm: function (event, element) {  
        //         element.trigger('confirm');  
        //     }  
        // });  
  
        $(document).on('confirm', function (e) {  
            var eele = e.target;  
            e.preventDefault();  
  
            $.ajax({  
                url: ele.href,  
                type: 'Post',  
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},  
                success: function (data) {  
                    if (data['success']) {  
                        $("#" + data['tr']).slideUp("slow");  
                        alert(data['success']);  
                    } else if (data['error']) {  
                        alert(data['error']);  
                    } else {  
                        alert('Whoops Something went wrong!!');  
                    }  
                },  
                error: function (data) {  
                    alert(data.responseText);  
                }  
            });  
  
            return false;  
        });  
    });  
</script>  
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
            "url": "{{ url('admin/allpaymentslist') }}",
            "dataType": "json",
            "type": "POST",
            "data":{ _token: '{{ csrf_token() }}',tab_type,host_filter,start_date,end_date},
            // "dataSrc": ""
        },
        'columnDefs': [
            { "width": "20px", "targets": 0 },
            { "width": "150px", "targets": 1 },
            { "width": "130px", "targets": 2 },
            { "width": "110px", "targets": 3 },
            { "width": "110px", "targets": 4 },
            { "width": "110px", "targets": 5 },
       
        ],
        "columns": [
            {data: 'checkbox', name: 'checkbox', orderable: false, class: "text-left multirow"},
            {data: 'id', name: 'id', class: "text-center", orderable: false ,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            
            {data: 'user', name: 'user', orderable: false, class: "text-left multirow"},
            {data: 'amount', name: 'amount', orderable: false, class: "text-left multirow"},
            {data: 'created_at', name: 'created_at', orderable: false, class: "text-left multirow"},
            {data: 'action', name: 'action', orderable: false, class: "text-left multirow"},
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

$('body').on('click', '#RejectReturnRequestBtn', function () {
    $('#ordercoverspin').show();
    var tab_type = get_payments_page_tabType();
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
            payment_table(tab_type);
        },
        error: function() {
            toastr.error("Please try again",'Error',{timeOut: 5000});
        }
    });
});

$('body').on('click', '#viewOrderBtn', function () {
    var payment_id = $(this).attr('data-id');
    var url = "{{ url('admin/payment') }}" + "/" + payment_id + "/view";
    window.open(url,"_blank");
});
</script>



<!-- payments JS end -->
@endsection
