jQuery(document).ready(function(){
  //-----------
  //-----------

  
  //-----------
  //-----------
});

/*
function User(){

  var getDateData = function(dateData){
    
    jQuery('#bm_id').val('');
    jQuery('#bm_date').val('');
    jQuery('#bm_name').val('');
    jQuery('#bm_description').val('');
    jQuery('#bm_file').val('');
    jQuery('#readable_date').html('');
    
    jQuery.ajax({
        url: ajaxurl,
        type:'POST',
        dataType: 'json',
        data:'action=getDateData&dateData='+dateData,
        success:function(response){
          jQuery('#insert_event_form').slideDown();
          jQuery('#bm_id').val(response.id);
          jQuery('#bm_date').val(response.date);
          jQuery('#bm_name').val(response.name);
          jQuery('#bm_description').val(response.description);
          jQuery('#bm_file').val(response.img_src);
          jQuery('#readable_date').html(response.r_date);
          
          if(response.img_src != undefined){
            jQuery('.existing_file').slideDown();
            jQuery('#delete_container').html('<input class="button-primary" type="submit"  onClick="javascript:if(!confirm(CONFIRM_MSG)){return false}" name="delete" value="Delete" />');
          }
          
          jQuery('#existing_file').attr('src', base_url+'/wp-content/uploads/banner_manager/'+response.img_src);
          
          jQuery('.calendars').slideDown();
          jQuery('body').scrollTo(130, 100);
        }
    });
  }
  
  this.cropzoom = function(){
    var img = jQuery('#existing_file');
    var sWidth = jQuery('#bm_def_width');
    var sHeight = jQuery('#bm_def_height');

    var cropzoom_res = jQuery('.existing_file_container').cropzoom({
        bgColor: '#CCC',
        enableRotation:false,
        enableZoom:true,
        zoomSteps:10,
        rotationSteps:90,
        expose:{
            slidersOrientation: 'horizontal',
            zoomElement: '#zoom',
        },
        selector:{
          width:sWidth.val(),
          height:sHeight.val(),
          aspectRatio:true,
          centered:true,
          borderColor:'yelow',
          borderColorHover:'#000',
          startWithOverlay: true,
          hideOverlayOnDragAndResize: true,
          showPositionsOnDrag: false,
          showDimetionsOnDrag: false       
        },
        image:{
            source:img.attr('src'),
            width:img.width(),
            height:img.height(),
            minZoom:50,
            maxZoom:150,
            startZoom:0,
            useStartZoomAsMinZoom:true,
            snapToContainer:false
        }
    });
    jQuery('#restore').click(function(){
      cropzoom_res.restore();
    });
    jQuery('#crop').click(function(){
      cropzoom_res.send(ajaxurl,'POST',{'action' : 'getCropZoom'},function(rta){
          jQuery('.result').find('img').remove();
          var img = jQuery('<img />').attr('src',rta);
          jQuery('.result').find('.txt').hide().end().append(img);
      });
    });
  }
  
  this.clickDate = function(){
    jQuery('.cal_date_i').click(function(){
      var cd = getDateData(this.id);
    });
  }


  this.cancelAddEdit = function(){
    jQuery('#cancel_add_edit').click(function(){
      jQuery('#insert_event_form').slideUp();
      jQuery('.existing_file').slideUp();
    });
  }

};

jQuery(document).ready(function(){
  var user = new User();
  
  user.clickDate();
  user.cancelAddEdit();
  user.cropzoom();
  
  jQuery('#insert_event_form').slideUp();
});

*/
