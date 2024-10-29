jQuery(document).ready(function(){
  //-----------
  //-----------
  
	jQuery('.dn_icon-2').click(function(){
	  if (!window.confirm(CONFIRM_MSG)) {
	    return false;
	  }
	});
  
	jQuery(".fi_close-error").click(function () {
		jQuery("#fi_message-error").fadeOut("slow");
	});
  
	jQuery(".fi_close-succes").click(function () {
		jQuery("#fi_message-succes").fadeOut("slow");
	});
  
  jQuery('.cal_date_i').click(function() {
    document.location.href = ADMIN_URL + 'admin.php?page=dn_bm-events&act=new&ref_data='+this.id;
  });
  
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
