@extends('admin.layout')

@section('content')
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Review List</a></li>
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
                            <div class="d-flex">
                            <?php $page_id = \App\Models\ProjectPage::where('route_url','admin.review.list')->pluck('id')->first(); ?>
            
                            </div>
                        </div>

                        {{-- <div class="custom-tab-1">
                            <ul class="nav nav-tabs mb-3">
                                <li class="nav-item review_page_tabs" data-tab="ALL_review_tab"><a class="nav-link active show" data-toggle="tab" href="">ALL</a>
                                </li>
                                <li class="nav-item review_page_tabs" data-tab="real_review_tab"><a class="nav-link" data-toggle="tab" href="">Real Review</a>
                                </li>
                                <li class="nav-item review_page_tabs" data-tab="fake_review_tab"><a class="nav-link" data-toggle="tab" href="">Fake Review</a>
                                </li>
                                
                            </ul>
                        </div> --}}

                        @if(isset($action) && $action=='list')
                            <div class="tab-pane fade show active table-responsive">
                                <table id="Review" class="table zero-configuration customNewtable" style="width:100%">
                                    <thead>
                                    <tr>
                                        <th>Sr. No</th>
                                        <th>Experice</th>
                                        <th>User</th>
                                        <th>Review Text</th>
                                        <th>Rating</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tfoot>
                                    <tr>
                                        <th>Sr. No</th>
                                        <th>Experice</th>
                                        <th>User</th>
                                        <th>Review Text</th>
                                        <th>Rating</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endif

                        @if(isset($action) && $action=='create')
                            @include('admin.review.create')
                        @endif

                        @if(isset($action) && $action=='edit')
                            @include('admin.review.edit')
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('js')
<!-- blog JS start -->
<script type="text/javascript">

review_table(true,'');
function get_review_page_tabType(){
    var tab_type;
    $('.review_page_tabs').each(function() {
        var thi = $(this);
        if($(thi).find('a').hasClass('show')){
            tab_type = $(thi).attr('data-tab');
        }
    });
    return tab_type;
}

$('body').on('click', '.review_page_tabs', function () {
    var tab_type = $(this).attr('data-tab');
    review_table(true,tab_type);
});

function review_table(is_clearState=false,tab_type=''){
    
    if(is_clearState){
        $('#Review').DataTable().state.clear();
    }

    $('#Review').DataTable({
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
            "url": "{{ url('admin/allReviewlist') }}",
            "dataType": "json",
            "type": "POST",
            "data":{ _token: '{{ csrf_token() }}', tab_type: tab_type },
            // "dataSrc": ""
        },
        'columnDefs': [
            { "width": "20px", "targets": 0 },
            { "width": "150px", "targets": 1 },
            { "width": "70px", "targets": 2 },
            { "width": "150px", "targets": 3 },
            { "width": "20px", "targets": 4 },
            { "width": "120px", "targets": 5 },
            { "width": "120px", "targets": 6 },
        ],
        "columns": [
            {data: 'sr_no', name: 'sr_no', class: "text-center", orderable: true,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {data: 'experice', name: 'experice', orderable: false, searchable: false, class: "text-left"},
            {data: 'user', name: 'user', orderable: false, searchable: false, class: "text-left"},
            {data: 'review_text', name: 'review_text', orderable: false, searchable: false, class: "text-left"},
            {data: 'review_rating', name: 'review_rating', class: "text-center", orderable: false},
            {data: 'created_at', name: 'created_at', searchable: false, class: "text-left", orderable: true},
            {data: 'action', name: 'action', orderable: false, searchable: false, class: "text-center"},
        ]
    });
}













function rejectstatus(blog_id) {
    var tab_type = $(this).attr('data-tab');
    $.ajax({
        type: 'GET',
        url: "{{ url('admin/rejectstatus') }}" +'/' + blog_id,
        success: function (res) {
            
            toastr.success("Review Reject",'Success',{timeOut: 5000});
            review_table(true,tab_type);
           
        },
        error: function (data) {
            toastr.error("Please try again",'Error',{timeOut: 5000});
        }
    });
}

function acceptstatus(blog_id) {
    var tab_type = $(this).attr('data-tab');
    $.ajax({
        type: 'GET',
        url: "{{ url('admin/acceptstatus') }}" +'/' + blog_id,
        success: function (res) {
            
            toastr.success("Review Accept",'Success',{timeOut: 5000});
            review_table(true,tab_type);
           
        },
        error: function (data) {
            toastr.error("Please try again",'Error',{timeOut: 5000});
        }
    });
}

</script>
<!-- blog JS end -->
@endsection

