@extends('admin.layout')

@section('content')
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Terms & Condition</a></li>
            </ol>
        </div>
    </div>
    <!-- row -->
    <form class="form-valide" action="" id="AboutusForm" method="post">
    <div id="cover-spin" class="cover-spin"></div>
    {{ csrf_field() }}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                       
                        <div class="row col-lg-12">
                             
                            <div class="col-lg-8 col-md-8 col-sm-10 col-xs-12  justify-content-center"> 
                              
                               <div class="form-group">
                                <label class="col-form-label" for="terms_condition">Terms & Condition <span class="text-danger">*</span>
                                </label>
                                <textarea class="summernote" id="terms_condition" name="terms_condition"></textarea>
                                <div id="terms_condition-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                               </div>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12  justify-content-center">
                                <div class="col-lg-6 col-md-8 col-sm-10 col-xs-12 justify-content-center mt-4">
                                   <!-- <button type="button" class="btn btn-outline-primary" data-dismiss="modal">Close</button> -->
                                   <button type="button" class="btn btn-primary" id="saveAboutusBtn">Save <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    </form>

   
 
@endsection

@section('js')
<!-- settings JS start -->
<script type="text/javascript">
    $( document ).ready(function() {
        $.get("{{ url('admin/infopage/edit') }}", function (data) {
           $('#terms_condition').summernote('code', data.terms_condition);
             
        })
    });

    $('body').on('click', '#saveAboutusBtn', function () {
        $('#saveAboutusBtn').prop('disabled',true);
        $('#saveAboutusBtn').find('.loadericonfa').show();
        var formData = new FormData($("#AboutusForm")[0]);

        $.ajax({
            type: 'POST',
            url: "{{ url('admin/updateInfopage') }}",
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if(res.status == 'failed'){
                    $('#saveAboutusBtn').prop('disabled',false);
                    $('#saveAboutusBtn').find('.loadericonfa').hide();
                
                    if (res.errors.terms_condition) {
                        $('#terms_condition-error').show().text(res.errors.terms_condition);
                    } else {
                        $('#terms_condition-error').hide();
                    }
                   
                }

                if(res.status == 200){
                    $("#UserAboutModal").modal('hide');
                    $('#saveAboutusBtn').prop('disabled',false);
                    $('#saveAboutusBtn').find('.loadericonfa').hide();
                    $("#UserDiscountPerVal").html(res.aboutus_contant + " %");
                    toastr.success("Terms & Condition Updated Successfully",'Success',{timeOut: 5000});
                }

                if(res.status == 400){
                    $("#UserAboutModal").modal('hide');
                    $('#saveAboutusBtn').prop('disabled',false);
                    $('#saveAboutusBtn').find('.loadericonfa').hide();
                    toastr.error("Please try again",'Error',{timeOut: 5000});
                }
            },
            error: function (data) {
                $("#UserAboutModal").modal('hide');
                $('#saveAboutusBtn').prop('disabled',false);
                $('#saveAboutusBtn').find('.loadericonfa').hide();
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        });
    });
</script>
<!-- settings JS end -->
@endsection
