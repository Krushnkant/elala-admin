@extends('admin.layout')

@section('content')
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Category Attribute</a></li>
            </ol>
        </div>
    </div>
<div>
   
    <div class="container-fluid pt-0 custom-form-design">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card ">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Edit Category Attribute</h4>
                        {{-- <p><b>Note: </b> All Fields Are Mandatory</p> --}}
                        <div class="form-validation">
                            <form class="form-valide" action="" mathod="POST" id="form_attribute_add" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <input type="hidden" value="{{ $id }}" name="category_id">
                                <div class="row">
                                    <div class="col-md-10  col-xl-6">
                                        <div class="row">
                                                <label class="col-12 col-form-label px-sm-3" for="name">Field Select <span class="text-danger">*</span>
                                                </label>
                                            <div class="form-group col-9 col-sm-10 px-sm-0">
                                                <div class="col-lg-12 px-0 px-sm-3">
                                                    <div class="position-relative">
                                                   
                                                        <select class="form-control select-box" id="field" name="field">
                                                            <option value="">--Select--</option>
                                                            <option value="1">Text Box</option>
                                                            <option value="2">Check Box</option>
                                                            <option value="3">Radio Box</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group col-3 px-0 px-sm-3 col-sm-2 text-center">
                                                <div class="ml-auto ">
                                                    <button type="button" class="plus_btn btn btn-info field_btn" id="Add">Add</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-10 col-xl-6 add-value-main px-0">
                                        @foreach($already as $key => $data)
                                        
                                        <?php
                                           
                                            $title = $data->id."_title[]"; 
                                            $key_type = $data->id."_main_type[]"; 
                                            $key_name_id = $data->id."_main_name"; 
                                        ?>
                                            @if($data->field_id == 1)
                                            <div class="row mt-3 mx-0"> 
                                                <input type="hidden" value="{{$data->id}}" class="form-control input-flat pe-none" name="allreadycateids[]" />
                                                <div class="col-12 col-sm-10 mb-3 mb-sm-0">
                                                    <h4 class="col-12 pl-0">Text Box</h4>
                                                </div>
                                                <div class="col-12 col-sm-10 mb-3 mb-sm-0">
                                                    <input type="text" placeholder="Title" data="specific" data-id="{{ $key }}" id="{{$key_name_id}}" value="{{$data->title}}" class="form-control input-flat specReq" data-name="title" name="old_title[]" />
                                                    <label id="title-error" class="error invalid-feedback animated fadeInDown" for=""></label>
                                                </div>
                                                <div class="">
                                                    <input type="hidden" value="{{$data->field_id}}" class="form-control input-flat pe-none" name="old_field_type[]" />
                                                </div>
                                               
                                                <div class="col-2 col-sm-2 text-center">
                                                    <button type="button"  class="minus_btn bplus_btn btn  btn-gray field_btn btn-sm text-danger"><i class="fa fa-trash-o"></i></button>
                                                </div>
                                               
                                            </div><hr class="mb-4 mt-4">
                                            @elseif($data->field_id == 2 || $data->field_id == 3)
                                            <div class="row mt-3 mx-0"> 
                                                <input type="hidden" value="{{$data->id}}" class="form-control input-flat pe-none" name="allreadycateids[]" />
                                                @if($data->field_id == 2)
                                                <div class="col-12 col-sm-10 mb-3 mb-sm-0">
                                                    <h4 class="col-12 pl-0">Check Box</h4>
                                                </div>
                                                @else
                                                <div class="col-12 col-sm-10 mb-3 mb-sm-0">
                                                    <h4 class="col-12 pl-0">Radio Box</h4>
                                                </div>
                                                @endif
                                                
                                                <div class="col-12 col-sm-10 mb-3 mb-sm-0">
                                                    <input type="text" placeholder="Title" data="specific" data-id="{{ $key }}" id="{{$key_name_id}}" value="{{$data->title}}" class="form-control input-flat specReq myClass" data-name="title" name="old_title[]" />
                                                    <label id="title-error" class="error invalid-feedback animated fadeInDown" for=""></label>
                                                </div>
                                                <div class="col-2 col-sm-2 text-center">
                                                    <button type="button"  class="minus_btn bplus_btn btn  btn-gray field_btn btn-sm text-danger"><i class="fa fa-trash-o"></i></button>
                                                </div>
                                                <div class="">
                                                    <input type="hidden" value="{{$data->field_id}}" class="form-control input-flat pe-none" name="old_field_type[]" />
                                                </div>
                                                <div class="optiondiv mt-3 col-12 col-sm-10 pl-0">
                                                    <div class="row  mx-0 ">
                                                        <div class="col-2 col-sm-2 mt-2">
                                                            <button type="button" class="plus_btn btn btn-info" id="AddOption">Add Option</button>
                                                        </div>
                                                    </div>
                                                    @foreach($data->attr_optioin as $option)
                                                    
                                                    <div class="row  mx-0 ">
                                                        <div class="col-8 col-sm-8 mt-2">
                                                            <input type="text" class="form-control input-flat pe-none" value="{{$option->option_value}}"  id="" name="old_field_options_{{ $key }}[]" placeholder="option value" />
                                                            <input type="hidden" value="{{$option->id}}" class="" name="old_field_options_ids_{{ $key }}[]" />
                                                        </div>
                                                        <div class="ml-auto col-2 col-sm-2 mt-2">
                                                            <button type="button"  class="minus_btn bplus_btn btn  btn-gray field_btn btn-sm text-danger"><i class="fa fa-trash-o"></i></button>
                                                        </div>
                                                        <div class="ml-auto col-2 col-sm-2 ">
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                 
                                                    
                                                </div>
                                            </div><hr class="mb-4 mt-4">
                                            
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                {{-- <div class="row">
                                    <div class="form-group col-md-10 col-xl-6 add-value px-0">
                                    
                                    </div>
                                </div> --}}
                                
                                <div class="row">
                                    <div class="form-group col-md-6 mt-3">
                                        <div class="">
                                            <button type="button" id="submit_form_attribute" class="btn btn-primary">Submit</button>
                                        </div>
                    
                                    </div>
                                </div>
                            </form>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
@section('js')
<!-- category JS start -->
<script type="text/javascript">

$(document).ready(function() { 
    //$("#Add").on("click", function() {
    var count = 0;
    //var count_sm = "{{ count($already)}}" -1;
    var count_sm = -1;
    $('body').on('click', '#Add', function(){    
        var html = "";
        count_sm++;
        // var valuee = $('#field').val();
        var selected = $('#field option:selected');
        var type = selected.attr('value')
        
        var inputkey = "input_key_"+count;
        var inputkeyoption = "input_key_option_"+count;
        // var inputval = "input_val_"+count;
        // console.log(valuee)
        // if(valuee == "textbox"){
        //     type = "text";
        // }else if(valuee == "file"){
        //     type = "file";
        // }else if(valuee == "multi-file"){
        //     type = "multi-file";
        // }else if(valuee == "sub-form"){
        //     type = "sub-form";
        //     // $('#field option[value="sub-form"]').attr("disabled","disabled");
        //     $("#field option[value='sub-form']").remove();
        // }else{
        //     type = ""
        // }
        if(type == 2 || type == 3){
      
            html += '<div class="row mt-3 mx-0">'+
                        '<div class="col-12 col-sm-10 mb-3 ">'+
                            '<input type="text" placeholder="Title" data="specific" data-id="'+count+'" id="'+inputkey+'" class="form-control input-flat specReq myClass" data-name="title" name="title[]" /><label id="title-error my-0" class="error invalid-feedback animated fadeInDown" for=""></label>'+
                        '</div>'+
                        '<div class="">'+
                            '<input type="hidden" value="'+type+'" class="form-control input-flat pe-none" name="field_type[]"  />'+
                        '</div>'+
                        '<div class="col-2 col-sm-2 text-center">'+
                            '<button type="button"  class="minus_btn bplus_btn btn  btn-gray field_btn btn-sm text-danger"><i class="fa fa-trash-o"></i></button>'+
                        '</div>'+
                    
                    '<div class="optiondiv col-12 col-sm-10 pl-0">'+
                        '<div class="row  mx-0 ">'+
                            '<div class="col-8 col-sm-8">'+
                                '<input type="text" class="form-control input-flat pe-none"  id="'+inputkeyoption+'" name="field_options_'+count_sm+'[]" placeholder="option value" />'+
                            '</div>'+
                            '<div class="ml-auto col-2 col-sm-2 ">'+
                                '<button type="button" class="plus_btn btn btn-info" id="AddOption">Add Option</button>'+
                            '</div>'+
                            '<div class="ml-auto col-2 col-sm-2 ">'+
                            
                            '</div>'+
                        '</div>'+
                    '</div>'+
                    '</div><hr class="mb-4 mt-4">';
            $(".add-value-main").append(html);
           
        }else if(type == 1){
            html += '<div class="row mt-3 mx-0">'+
                    '<div class="col-12 col-sm-10 mb-3 mb-sm-0">'+
                        '<input type="text" placeholder="Title" data="specific" data-id="'+count+'" id="'+inputkey+'" class="form-control input-flat specReq myClass" data-name="title" name="title[]" /><label id="title-error my-0" class="error invalid-feedback animated fadeInDown" for=""></label>'+
                    '</div>'+
                    '<div class="">'+
                        '<input type="hidden" value="'+type+'" class="form-control input-flat pe-none" name="field_type[]"  />'+
                    '</div>'+
                    '<div class="col-2 col-sm-2 text-center">'+
                        '<button type="button"  class="minus_btn bplus_btn btn  btn-gray field_btn btn-sm text-danger"><i class="fa fa-trash-o"></i></button>'+
                    '</div>'+
                '</div><hr class="mb-4 mt-4">';

            $(".add-value-main").append(html);
        } 
        count ++; 
         
        // $(".add-value").append(html);
    });


    var count1 = 0;
   
    $('body').on('click', '#AddOption', function(){  
   
        var inputkeyoption = "ex_input_key_option_"+count1;
        //console.log($(this).parent().parent().parent().parent().find('.myClass'));
        var count_s = $(this).parent().parent().parent().parent().find('.myClass').attr('data-id');

        var next_sub_form_row = $(this).parent().parent().parent();

        var html = "";
      
        html += '<div class="row  mx-0 ">'+
                    '<div class="col-8 col-sm-8 mt-2">'+
                        '<input type="text" value="" class="form-control input-flat pe-none"  id="'+inputkeyoption+'" name="field_options_'+count_s+'[]" placeholder="option value" />'+
                    '</div>'+
                    '<div class="ml-auto col-2 col-sm-2 mt-2">'+
                        '<button type="button"  class="minus_btn bplus_btn btn btn-gray field_btn btn-sm text-danger"><i class="fa fa-trash-o"></i></button>'+
                    '</div>'+
                    '<div class="ml-auto col-2 col-sm-2 ">'+
                       
                    '</div>'+
                '</div>';
        $(next_sub_form_row).append(html);
        count1 ++;
        
    });
   
 

    $('body').on('click', '.minus_btn', function(){
        var datas = $(this).attr('data');
        if(datas == "option_section"){
            $(this).parent().parent().remove();
        }else{
            $(this).parent().parent().remove();
        }
    });


    $('body').on('click', '#submit_form_attribute', function () {
        // $(this).prop('disabled',true);
        // $(this).find('.submitloader').show();
        var btn = $(this);
        var formData = new FormData($("#form_attribute_add")[0]);
        var validation = ValidateForm()
        if(validation != false){
            $('#loader').show();
            $('#Add').prop('disabled', true);
            $.ajax({
                    type: 'POST',
                    url: "{{ route('admin.categoryattribute.store') }}",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        if(res['status']==200){
                            $('#loader').hide();
                            toastr.success("Category Attribute Added",'Success',{timeOut: 5000});
                            window.location.href = "{{ url('admin/addcategoryattribute/'.$id)}}";
                            $("#form_attribute_add")[0].reset()
                        }
                    },
                    error: function (data) {
                        $('#Add').prop('disabled', false);
                        $('#loader').hide();
                        $(btn).prop('disabled',false);
                        // $(btn).find('.submitloader').hide();
                        toastr.error("Please try again",'Error',{timeOut: 5000});
                    }
            });
        }
    });

    

}); 

function ValidateForm() {
    var isFormValid = true;  
    var app_id = "{{$id}}";
    var specific_arr = [];
    var specific_ids = [];
    var total_specific = $("#form_attribute_add input");
  
    $(total_specific).each( function(){
        if($(this).attr('data') == "specific"){
            if($(this).val()){
                specific_arr.push($(this).val())
            }
        }
    })
    $(total_specific).each( function(){
        if($(this).attr('data') == "specific"){
            if($(this).val()){
                specific_ids.push($(this).attr('id'))
            }
        }
    })
    $("#form_attribute_add input").each(function () { 
        var regexp = /^\S*$/; 
        if($(this).attr("id") != undefined){
            var FieldId = "span_" + $(this).attr("id");
            if ($.trim($(this).val()).length == 0 || $.trim($(this).val())==0) {
                $(this).addClass("highlight");
                if ($("#" + FieldId).length == 0) {  
                        $("<span class='error-display' id='" + FieldId + "'>This Field Is Required</span>").insertAfter(this);  
                }
                if ($("#" + FieldId).css('display') == 'none'){  
                    $("#" + FieldId).fadeIn(500);  
                } 
                isFormValid = false;  
            }else{  
                // if($.trim($(this).val()).length != 0 || $.trim($(this).val()) != 0 ){
                //     const seen = new Set();
                //     const duplicates = specific_arr.filter(n => seen.size === seen.add(n).size);
             
                //     var iddd = "";
                //     var idd1 = "";
                //     $(specific_ids).each( function(item, val){
                //         var vall = $("#"+val).val();
                //         var iddds = "#"+val;
                //         if(regexp.test(vall) == false){
                //             idd1 = "#span_"+val;
                //             $(this).addClass("highlight");
                //             $(iddds).nextAll('span').remove();
                //             $("<span class='error-display other' id='"+idd1+"'>Please remove space</span>").insertAfter(iddds);
                //             isFormValid = false;  
                //         }else{
                //             $(iddds).nextAll('span').remove();
                //             if(duplicates.length > 0){
                //                 $(duplicates).each( function(item, val){
                //                     var ddd = specific_arr.indexOf(val);
                //                     iddd = "#"+specific_ids[ddd];
                //                     idd1 = specific_ids[ddd];
                                    
                //                     $(iddd).nextAll('span').remove();
                //                     $(iddd).addClass("highlight");
                //                     $("<span class='error-display other' id='" + idd1 + "'>Please enter different value</span>").insertAfter(iddd);  
                //                     isFormValid = false; 
                //                 })
                //             }
                //             // else{
                //             //     $(specific_ids).each( function(item, val){
                //             //         iddd = "#"+val;
                //             //         $(iddd).removeClass("highlight");  
                //             //         $(iddd).nextAll('span').remove();
                //             //         isFormValid = true; 
                //             //     }) 
                //             // }
                //         }
                //     })
                // } 
                $(this).removeClass("highlight");  
                if ($("#" + FieldId).length > 0) {  
                    $("#" + FieldId).fadeOut(1000);  
                }  
            }
        }
    }) 
    return isFormValid;  
 
}

</script>
<!-- category JS end -->
@endsection
