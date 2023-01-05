@extends('admin.layout')

@section('content')
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">About Us</a></li>
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
                            <div class="col-lg-3 col-md-8 col-sm-10 col-xs-12  justify-content-center">
                              <div class="form-group">
                               <label class="col-form-label" for="Logo"> Image <span class="text-danger">*</span>
                               </label>
                               <input type="file" class="form-control-file" id="about_image" name="about_image" placeholder="">
                              <div id="first_section-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                                  <img src="{{ url('images/placeholder_image.png') }}" class="" id="about_image_show" height="200px" width="200px"   style="margin-top: 5px">
                              </div>
                            </div> 
                            <div class="col-lg-6 col-md-8 col-sm-10 col-xs-12  justify-content-center"> 
                              
                               <div class="form-group">
                                <label class="col-form-label" for="about_description">Description <span class="text-danger">*</span>
                                </label>
                                <textarea class="summernote" id="about_description" name="about_description"></textarea>
                                <div id="about_description-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
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
           $('#about_description').summernote('code', data.about_description);
            if(data.about_image==null){
                var default_image = "{{ url('images/placeholder_image.png') }}";
                $('#about_image_show').attr('src', default_image);
            }
            else{
                var about_image = "{{ url('images/infopage') }}" +"/" + data.about_image;
                $('#about_image_show').attr('src', about_image);
            } 
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
                    

                    if (res.errors.about_description) {
                        $('#about_description-error').show().text(res.errors.about_description);
                    } else {
                        $('#about_description-error').hide();
                    }
                    
                    if (res.errors.about_image) {
                        $('#about_image-error').show().text(res.errors.about_image);
                    } else {
                        $('#about_image-error').hide();
                    }

                   
                }

                if(res.status == 200){
                    $("#UserAboutModal").modal('hide');
                    $('#saveAboutusBtn').prop('disabled',false);
                    $('#saveAboutusBtn').find('.loadericonfa').hide();
                    $("#UserDiscountPerVal").html(res.aboutus_contant + " %");
                    toastr.success("About Us Updated Successfully",'Success',{timeOut: 5000});
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


    $('#about_image').change(function(){
        
        $('#about_image-error').hide();
        var file = this.files[0];
        var fileType = file["type"];
        var validImageTypes = ["image/jpeg", "image/png", "image/jpg"];
        if ($.inArray(fileType, validImageTypes) < 0) {
            $('#about_image-error').show().text("Please provide a Valid Extension First Section Image(e.g: .jpg .png)");
            var default_image = "{{ url('public/images/placeholder_image.png') }}";
            $('#about_image_show').attr('src', default_image);
        }
        else {
            let reader = new FileReader();
            reader.onload = (e) => {
                $('#about_image_show').attr('src', e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
        }
    });



    
</script>
<!-- settings JS end -->
@endsection
