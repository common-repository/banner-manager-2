jQuery(document).ready(function(){
  //-----------
  //-----------
  
  jQuery('#cancel_crop').live('click', function(){
    lock_unlock_crop_deps('false');
    jQuery('#bm_image_status').val('0');
    set_crop_msg('Crop tool canceled.', 'yellow')
  });
  

  
  //-----------
  //-----------
});

function store_crop_cords(xywh){
  jQuery('#bm_def_crop_cords').val(xywh);
  lock_unlock_crop_deps('true');
  jQuery('#bm_image_status').val('1');
}

function set_crop_msg(msg, color){
  jQuery('.msg_crop').html(msg);
  jQuery('.msg_crop').addClass(color);
}

function lock_unlock_crop_deps(state){
  jQuery('#image_upload').attr('readonly', state);
  jQuery('#bm_def_repeated').attr('readonly', state);
  jQuery('#bm_def_width').attr('readonly', state);
  jQuery('#bm_def_height').attr('readonly', state);
}
