<form class="form-valide" action="" id="ExperienceCreateForm" method="post" enctype="multipart/form-data">

    <div id="attr-cover-spin" class="cover-spin"></div>
    {{ csrf_field() }}
    <div class="col-lg-12 col-md-8 col-sm-10 col-xs-12">
        <input type="hidden" name="experience_id" value="{{ isset($experience)?($experience->id):'' }}">
        <div class="form-group">
            <label class="col-form-label" for="Serial_No">What kind of experience do you want to host? <span class="text-danger">*</span>
            </label><br>
            <label class="radio-inline mr-3">
                <input type="radio" name="type" value="1" {{ ($experience->type == '1') ? "checked" : ""  }}> In person </label>
            <label class="radio-inline mr-3">
                <input type="radio" name="type" value="2" {{ ($experience->type == '2') ? "checked" : ""  }}> Online Only </label>
        </div>
   </div>
   
    <div class="col-lg-12 col-md-8 col-sm-10 col-xs-12">
        <div class="row">
            <div class="col-lg-6 col-md-8 col-sm-10 col-xs-12">
                <h4 class="card-title mt-4">Experience</h4>
                <div class="form-group">
                    <label class="col-form-label" for="category">Category <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control input-flat" id="category" name="category" value="{{ isset($experience)?($experience->category->category_name):'' }}" readonly>
                    <div id="category-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                </div>
                <div class="form-group">
                    <label class="col-form-label" for="title">Title <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control input-flat" id="title" name="title" value="{{ isset($experience)?($experience->title):'' }}">
                    <div id="title-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                </div>
        
                <div class="form-group">
                    <label class="col-form-label" for="description">Details <span class="text-danger">*</span>
                    </label>
                    <textarea class="summernote" id="description" name="description" class="form-control input-flat">{{ isset($experience)?($experience->description):'' }}</textarea>
                    <div id="description-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                </div>
        
                <div class="form-group">
                    <label class="col-form-label" for="Thumbnail">Media  <span class="text-danger">*</span>
                    </label>
                    
                    <input type="hidden" name="expImg" id="expImg" value="">
                    <input type="file" name="files[]" id="experienceIconFiles" multiple="multiple">
                    <?php
                    if(isset($experience) && isset($experience->media)){
                    ?>    
                        <div class="jFiler-items jFiler-row ">
                        <ul class="jFiler-items-list jFiler-items-grid">
                    <?php 
                    foreach ($experience->media as $image) {   
                        $idmodel = "myModal".$image->id; 
                    ?>
                        @if($image->type == 'img')
                        <li id="ImgBox" class="jFiler-item" data-jfiler-index="1" style="">
                            <div class="jFiler-item-container">
                                <div class="jFiler-item-inner">
                                    <div class="jFiler-item-thumb" data-toggle="modal" data-target="#{{ $idmodel }}">
                                        <div class="jFiler-item-status"></div>
                                        <div class="jFiler-item-thumb-overlay"></div>
                                        <div class="jFiler-item-thumb-image">
                                            <img src="{{ url('images/experience_images/'.$image->thumb) }}" draggable="false" class="set_img" data-toggle="modal" data-target="#{{ $idmodel }}" > 
                                        </div>
                                    </div>
                                    <div class="jFiler-item-assets jFiler-row">
                                        <ul class="list-inline pull-right">
                                            <li><a class="icon-jfi-trash jFiler-item-trash-action" onclick="removeuploadedimg('ImgBox', 'expImg','<?php echo $image->id;?>');"></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </li>
        
                        <div class="modal fade" id="{{ $idmodel }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                            
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                </div>
                                <div class="modal-body"><img src="{{ url('images/experience_images/'.$image->thumb) }}" style="height: 300px;width: 100%;"></div>
                                
                            </div>
                            </div>
                        </div>
                    
                    
                        @else
                        <li class="jFiler-item jFiler-no-thumbnail" data-jfiler-index="0" style="">				
                            <div class="jFiler-item-container">					
                                <div class="jFiler-item-inner">			
                                    <div class="jFiler-item-thumb" data-toggle="modal" data-target="#{{ $idmodel }}">				
                                        <div class="jFiler-item-status"></div>		
                                        <div class="jFiler-item-thumb-overlay">	
                                            <div class="jFiler-item-info">	
                                                <div style="display:table-cell;vertical-align: middle;">
                                                    {{-- <span class="jFiler-item-title">
                                                        <b title="">{{ url($image->thumb) }}</b>
                                                    </span>	 --}}
                                                
                                                </div>		
                                            </div>		
                                        </div>	 
                                        <div class="jFiler-item-thumb-image">
                                            <span class="jFiler-icon-file f-video"><i class="icon-jfi-file-video"></i></span>
                                        </div>				
                                        </div>								
                                        <div class="jFiler-item-assets jFiler-row">						              											
                                            <ul class="list-inline pull-right">		                                                              								
                                                <li><a class="icon-jfi-trash jFiler-item-trash-action"></a>
                                                </li>                                                                                           								
                                            </ul>							                                                                                                                          	
                                        </div>							                                                                                                                       
                                    </div>				                                                                                                                        		
                                </div>	                                                                                                                               				
                            </li>
        
        
                            <div class="modal fade" id="{{ $idmodel }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    </div>
                                    <div class="modal-body">
                                        <video width="320" height="240" controls style="height: 300px;width: 100%;">
                                    <source src="{{ url('images/experience_videos/'.$image->thumb) }}" type="video/mp4">
                                    <source src="movie.ogg" type="video/ogg">
                                
                                    </video>  
                                </div>
                                
                                </div>
                                </div>
                            </div>
                        @endif
                        <?php 
                        } 
                        ?>
                    </ul>
                    </div>
                    <?php } ?>
                </div>
                <h4 class="card-title mt-4">Timings</h4> 
                <div class="form-group">
                    <label class="col-form-label" for="title">Time Seletion <span class="text-danger">*</span>
                    </label>
                    <div class="input-group mb-2">
                        
                        <input type="number" class="form-control input-flat" id="duration" name="duration" value="{{ isset($experience)?($experience->duration):'' }}">
                        <div class="input-group-prepend">
                            <div class="input-group-text">min</div>
                        </div>
                    </div>

                        @if(isset($experience->scheduletime) && !empty($experience->scheduletime))
                        <div class="form-group">
                            <label class="col-form-label" for="">Experience start time
                            </label><br>
                            <ul>
                            @foreach($experience->scheduletime as $scheduletime)
                                <li>{{ $scheduletime->day }} :  {{  date('h:i A', strtotime($scheduletime->time)); }}</li>
                            @endforeach
                            </ul>
                        </div>
                        @endif
                    
                    <div id="duration-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                </div>
                <h4 class="card-title mt-4">Pricing</h4>
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label class="col-form-label" for="max_member_public_group_size">Public Groups <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control input-flat" id="max_member_public_group_size" name="max_member_public_group_size" value="{{ isset($experience)?($experience->max_member_public_group_size):'' }}">
                            <div id="max_member_public_group_size-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label class="col-form-label" for="max_member_private_group_size">Private Groups <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control input-flat" id="max_member_private_group_size" name="max_member_private_group_size" value="{{ isset($experience)?($experience->max_member_private_group_size):'' }}">
                            <div id="max_member_private_group_size-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label class="col-form-label" for="individual_rate">Individual Rate <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control input-flat" id="individual_rate" name="individual_rate" value="{{ isset($experience)?($experience->individual_rate):'' }}">
                            <div id="individual_rate-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label class="col-form-label" for="min_private_group_rate">Private Groups Rate<span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control input-flat" id="min_private_group_rate" name="min_private_group_rate" value="{{ isset($experience)?($experience->min_private_group_rate):'' }}">
                            <div id="min_private_group_rate-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                    </div>
                </div>
                
                @if(isset($experience->discountrate) && !empty($experience->discountrate))
                <div class="form-group">
                    <label class="col-form-label" for="">Offer group rates and get more bookings
                    </label><br>
                <ul>
                @foreach($experience->discountrate as $discountrate)
                    <li>{{ $discountrate->from_member }} to  {{ $discountrate->to_member }} = {{ $discountrate->discount }}%
                    </li>
            
                @endforeach
                </ul>
                </div>
                @endif


                @if(isset($cancellationpolicy) && !empty($cancellationpolicy))
                    <div class="form-group">
                        <label class="col-form-label" for="parent_category_id">Chosse a cancellation policy <span class="text-danger">*</span>
                        </label><br>
                        @foreach($cancellationpolicy as $policy)
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="cancellation_policy_id" value="{{ $policy['id'] }}" {{ ($experience->cancellation_policy_id == $policy['id'] ) ? "checked" : ""  }}>{{ $policy['title'] }}
                            </label><br>
                        </div>
                        @endforeach
                        
                    </div>
                @endif
            </div>
            <div class="col-lg-6 col-md-8 col-sm-10 col-xs-12">
                <h4 class="card-title mt-4">Venue</h4> 
                <div class="form-group">
                    <label class="col-form-label" for="location">Location <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control input-flat" id="location" name="location" value="{{ isset($experience)?($experience->location):'' }}">
                    <div id="location-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                </div>
                @if(isset($languages) && !empty($languages))
                    <div class="form-group">
                        <label class="col-form-label" for="language_id">Language
                        </label>
                        <select id='language_id' name="language_id[]" class="form-control" multiple>
                            @foreach($languages as $lag)
                                <option value="{{ $lag['id'] }}" {{ (in_array($lag['id'],$experiencelanguage)) ? "selected" : "" }} >{{ $lag['title'] }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <h4 class="card-title mt-4">Detail</h4>
                @if(isset($agegroups) && !empty($agegroups))
                    <div class="form-group">
                        <label class="col-form-label" for="parent_category_id">Age Seletion
                        </label><br>
                        <?php $age_ids = explode(',',$experience->age_limit); ?> 
                        @foreach($agegroups as $age)
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" name="age_limit[]" {{ (in_array($age['id'],$age_ids)) ? "checked" : "" }} value="{{ $age['id'] }}">{{ $age['from_age'] }} to {{ $age['to_age'] }}
                            </label>
                        </div>
                        @endforeach
                        
                    </div>
                @endif
                <div class="form-group">
                    <label class="col-form-label" for="title">Provide Item <span class="text-danger">*</span>
                    </label><br>
                    <input type="text" data-role="tagsinput" class="form-control input-flat" id="provide_item" name="provide_item" value="{{ isset($experience)?($provideitems):'' }}">
                    <div id="provide_item-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                </div>

                <div class="form-group">
                    <label class="col-form-label" for="Serial_No">Will guests need to bring anything to the experience? <span class="text-danger">*</span>
                    </label><br>
                    <label class="radio-inline mr-3">
                        <input type="radio" name="is_bring_item" id="is_bring_item" value="1" {{ ($experience->is_bring_item == '1') ? "checked" : ""  }}> Yes </label>
                    <label class="radio-inline mr-3">
                        <input type="radio" name="is_bring_item" id="is_bring_item" value="2" {{ ($experience->is_bring_item == '2') ? "checked" : ""  }}> No </label>
                </div>

                <div class="form-group BringItem">
                    <label class="col-form-label" for="title">Bring Item <span class="text-danger">*</span>
                    </label><br>
                    <input type="text" data-role="tagsinput" class="form-control input-flat" id="bring_item" name="bring_item" value="{{ isset($experience)?($brinditems):'' }}">
                    <div id="bring_item-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                </div>
                <h4 class="card-title mt-4">Meetup</h4>
                <div class="form-group">
                    <label class="col-form-label" for="meet_address">Street address <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control input-flat" id="meet_address" name="meet_address" value="{{ isset($experience)?($experience->meet_address):'' }}">
                    <div id="meet_address-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                </div>
            
                <div class="form-group">
                    <label class="col-form-label" for="meet_address_flat_no">Flat, Suit, Bldg 
                    </label>
                    <input type="text" class="form-control input-flat" id="meet_address_flat_no" name="meet_address_flat_no" value="{{ isset($experience)?($experience->meet_address_flat_no):'' }}">
                    <div id="meet_address_flat_no-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label class="col-form-label" for="meet_city">City <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control input-flat" id="meet_city" name="meet_city" value="{{ isset($experience)?($experience->meet_city):'' }}">
                            <div id="meet_city-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                      
                        <div class="form-group">
                            <label class="col-form-label" for="meet_state">State <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control input-flat" id="meet_state" name="meet_state" value="{{ isset($experience)?($experience->meet_state):'' }}">
                            <div id="meet_state-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label class="col-form-label" for="meet_country">Country <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control input-flat" id="meet_country" name="meet_country" value="{{ isset($experience)?($experience->meet_country):'' }}">
                            <div id="meet_country-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label class="col-form-label" for="pine_code">Pine Code <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control input-flat" id="pine_code" name="pine_code" value="{{ isset($experience)?($experience->pine_code):'' }}">
                            <div id="pine_code-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                    </div>
                </div>
                <h4 class="card-title mt-4">Extra Detail</h4>
                    @if(isset($categoryattributes) && !empty($categoryattributes))
                        @foreach($categoryattributes as $attribute)
                        
                        <div class="form-group">
                            <label class="col-form-label" for="">{{ $attribute['title'] }}  <span class="text-danger">*</span>
                            </label><br>
                            <?php  
                                $submenupages = \App\Models\ExperienceCategoryAttribute::where('experience_id',$experience->id)->get()->toArray();
                                $value = searchForId($attribute['id'], $submenupages); 
                            ?>
                            <input type="hidden" class="form-control input-flat"  name="tagid[]" value="{{ $attribute['id'] }}">
                            <input type="hidden" class="form-control input-flat"  name="tagid[]" value="{{ $attribute['id'] }}">
                            @if($attribute['field_id']  == 1)
                            <input type="text" class="form-control input-flat"  name="tagvalue{{ $attribute['id'] }}[]" value="{{ $value }}">
                            @elseif($attribute['field_id']  == 2)
                                <?php 
                                    if($value != ""){
                                        $valuearray = explode(',',$value); 
                                    }else{
                                        $valuearray = [];
                                    }
                                ?>
                                @foreach ($attribute['attr_optioin'] as $optioin)
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" name="tagvalue{{ $attribute['id'] }}[]" value="{{ $optioin['id'] }}" {{ (in_array($optioin['id'],$valuearray)) ? "checked" : "" }}>{{ $optioin['option_value'] }} 
                                        </label>
                                    </div>
                                @endforeach
                            @else
                                @foreach ($attribute['attr_optioin'] as $optioin)
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" name="tagvalue{{ $attribute['id'] }}[]"  value="{{ $optioin['id'] }}" {{ ($value == $optioin['id']) ? "checked" : ""  }}>{{ $optioin['option_value'] }}
                                    </label>
                                </div>
                                @endforeach
                            @endif
                        </div>
                        @endforeach
                    @endif 
            </div>
        </div>
        {{-- <button type="button" class="btn btn-outline-primary mt-4" id="save_newExperienceBtn" data-action="update">Save & New <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>&nbsp;&nbsp; --}}
        <button type="button" class="btn btn-primary mt-4" id="save_closeExperienceBtn" data-action="update">Save & Close <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
    </div>        
        


   
    </div>
</form>
<?php 
function searchForId($id, $array) {
    foreach($array as $key => $val) {
        if($val['cat_attr_id'] == $id) {
            return $val['value'];
        }
    }
    return null;
 }

 ?>