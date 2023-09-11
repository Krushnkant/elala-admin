<form class="form-valide" action="" id="postCreateForm" method="post" enctype="multipart/form-data">

    <div id="attr-cover-spin" class="cover-spin"></div>
    {{ csrf_field() }}
    <div class="col-lg-6 col-md-8 col-sm-10 col-xs-12 container justify-content-center">
        <input type="hidden" name="post_id" value="{{ isset($post)?($post->id):'' }}">
    <div class="form-group">
        <label class="col-form-label" for="description">Description <span class="text-danger">*</span>
        </label>
        <textarea class="form-control input-flat" id="description" name="description" >{{ isset($post)?($post->description):'' }}</textarea>
        <div id="description-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
    </div>


    @if(isset($users) && !empty($users))
    <div class="form-group">
        <label class="col-form-label" for="tag_friends">Tag Friends 
        </label>
        <?php 
         $tag_frinds = \App\Models\PostTag::where('post_id',$post->id)->pluck('user_id')->toArray();
        ?>
        <select id='tag_friends' name="tag_friends[]" class="form-control" multiple>
            @foreach($users as $user)
                <option value="{{ $user['id'] }}" @if(in_array($user["id"],$tag_frinds)) selected @endif>{{ $user['full_name'] }}</option>
            @endforeach
        </select>
        
    </div>
    @endif

    @if(isset($users) && !empty($users))
    <div class="form-group">
        <label class="col-form-label" for="host_tag"> Tag Host 
        </label>
        <select id='host_tag'  name="host_tag" class="form-control">
            <option></option>
            @foreach($users as $user)
                <option value="{{ $user['id'] }}" @if(isset($post) && $post->host_tag == $user['id']) selected @endif>{{ $user['full_name'] }}</option>
            @endforeach
        </select>
    </div>
    @endif

    <div class="form-group">

        <label class="col-form-label" for="Thumbnail">Thumbnail  <span class="text-danger">*</span>
        </label>
        <input type="file" name="files[]" id="postIconFiles" multiple="multiple">
        <?php 
         $post_media = \App\Models\PostMedia::where('post_id',$post->id)->pluck('name')->toArray();
         $post_media_string = implode(",",$post_media);
        ?>
        <input type="hidden" name="postImg" id="postImg" value="{{ $post_media_string }}">

       

        <?php
        if( isset($post->postmedia)){
     
        ?>

        <div class="jFiler-items jFiler-row oldImgDisplayBox">
            <?php $vcnt = 1; ?>
            @foreach($post->postmedia as $key => $v_img)
        
                <ul class="jFiler-items-list jFiler-items-grid">
                    <li id="ImgBox" class="jFiler-item" data-jfiler-index="1" style="">
                        <div class="jFiler-item-container">
                            <div class="jFiler-item-inner">
                                <div class="jFiler-item-thumb">
                                    <div class="jFiler-item-status"></div>
                                    <div class="jFiler-item-thumb-overlay"></div>
                                    <div class="jFiler-item-thumb-image"><img src="{{ url($v_img->name) }}" draggable="false"></div>
                                </div>
                                <div class="jFiler-item-assets jFiler-row">
                                    <ul class="list-inline pull-right">
                                        <li><a class="icon-jfi-trash jFiler-item-trash-action" onclick="removeuploadedimg('oldImgDisplayBox', 'userImg','<?php echo $v_img->name;?>');"></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            <?php $vcnt++; ?>
            @endforeach
        </div>
        <?php } ?>
        <div id="postthumb-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
    </div>

    <div class="form-group">
        <input type="checkbox"  id="is_private" name="is_private"  @if(isset($post) && $post->is_private == 1) checked @endif>  &nbsp; Is Private
       
        
        <div id="is_private-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
    </div>

    <button type="button" class="btn btn-outline-primary mt-4" id="save_newpostBtn" data-action="add">Save & New <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>&nbsp;&nbsp;
    <button type="button" class="btn btn-primary mt-4" id="save_closepostBtn" data-action="add">Save & Close <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>

    </div>
</form>




