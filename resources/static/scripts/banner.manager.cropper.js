jQuery(document).ready(function(){
  //-----------
  //-----------
  
  var img_crop;
  
  var ratio_w = jQuery(top.document).contents().find('#bm_def_width').val();
  var ratio_h = jQuery(top.document).contents().find('#bm_def_height').val();
  
  var crop_cords = jQuery(top.document).contents().find('#bm_def_crop_cords').val().split(",");

  var select_x = crop_cords[0];
  var select_y = crop_cords[1];
  var select_w = crop_cords[2];
  var select_h = crop_cords[3];
  var select_x2 = parseInt(select_x) + parseInt(select_w);
  var select_y2 = parseInt(select_y) + parseInt(select_h);
  var ratio = 'noRatio';

  setTimeout(function(){loadJCrop('noRatio')}, 1000);

  function loadJCrop(ratio){
    var set_crop_ratio = ratio_w / ratio_h;
    var set_crop_select = [ select_x, select_y, select_x2, select_y2 ];
    

    if(select_w < 1 && select_h < 1){
      set_crop_select = [0, 0, ratio_w, ratio_h];
    }else if(set_crop_ratio == ''){
      ratio = 'withRatio';
    }
    
    if(ratio == 'noRatio'){
      set_crop_ratio = 0 / 0;
    }
    
    img_crop = jQuery.Jcrop('#image_crop', {
      onChange: showCoords,
      onSelect: showCoords,
      boxWidth: 1010, 
      boxHeight: 435,
      aspectRatio : set_crop_ratio,
      setSelect: set_crop_select
    });
  }
  
  jQuery('#set_real_size').click(function() {
    img_crop.destroy();
    loadJCrop();
    img_crop.animateTo([0,0,ratio_w,ratio_h]);
  });
  
  jQuery('#set_free_ratio').click(function() {
    img_crop.destroy();
    ratio = 'noRatio';
    loadJCrop('noRatio');
  });

  jQuery('#go_to_crop_step').click(function() {
    jQuery('.crop_step').slideDown();
    jQuery('.preview_step').slideUp();
  });

  function showCoords(c){
    jQuery('#x').val(c.x);
    jQuery('#y').val(c.y);
    jQuery('#w').val(c.w);
    jQuery('#h').val(c.h);
  };
  
  jQuery('#crop_submit').submit(function() {
    var repeated = 'no-repeat';
    var w = jQuery('#w').val();
    var h = jQuery('#h').val();
    var repeat_check = jQuery(top.document).contents().find('#bm_def_repeated:checked').val();
    
    if(w > 0 || h > 0){
      jQuery('.msg_crop').html('Sending ...');
      jQuery('.msg_crop').addClass('yellow');
      jQuery.ajax({ url:ajaxurl, type:'POST',
        data:'action=crop_image&'+jQuery('#crop_submit').serialize(),
        success:function(response){
          if(repeat_check == 1){
            repeated = 'repeat';
          }
          
          var ver = Math.floor(Math.random()*11);
          jQuery('.crop_step').slideUp();
          jQuery('.preview_step').slideDown();
          jQuery('#banner_preview').css("", "");
          jQuery('#banner_preview').css({
            'width' : ratio_w+'px',
            'height' : ratio_h+'px',
            'background' : '#1F2437 url('+response+'?ver='+ver+') '+repeated+' top center',
            'margin' : '0 auto'
          });
          jQuery('.msg_crop').html('');
          jQuery('.msg_crop').removeClass('yellow');
        }
      });
    }else{
      jQuery('.msg_crop').html('Please done some cropping before save or cancel the crop tool.');
      jQuery('.msg_crop').addClass('red');
    }
    return false;
  });
  
  jQuery('#save_crop').click(function(){
    var xywh = new Array(jQuery('#x').val(), jQuery('#y').val(), jQuery('#w').val(), jQuery('#h').val());

    top.store_crop_cords(xywh);
    top.set_crop_msg('Some fields relate with crop tool is disable, or <a href="javascript:void(0);" id="cancel_crop">cancel crop.</a>', 'red');
    top.tb_remove();
  });
  
  //-----------
  //-----------
});
/*
onSelect 	callback 	Called when selection is completed
onChange 	callback 	Called when the selection is moving
aspectRatio 	decimal 	Aspect ratio of w/h (e.g. 1 for square) 	n/a
minSize 	array [ w, h ] 	Minimum width/height, use 0 for unbounded dimension 	n/a
maxSize 	array [ w, h ] 	Maximum width/height, use 0 for unbounded dimension 	n/a
setSelect 	array [ x, y, x2, y2 ] 	Set an initial selection area 	n/a
bgColor 	color value 	Set color of background container 	'black'
bgOpacity 	decimal 0 - 1 	Opacity of outer image when cropping 	.6 
*/
