<!-- start content-outer START -->
<div class="wrap">

  <div id="icon-edit" class="icon32"></div>
  <h2>Banner Manager - Events</h2>
  
  <?php if(!empty($errors['general'])): ?>
    <div class="error below-h2" id="fi_message-error">
      <p>
        <?php  echo $errors['general']; ?>
        <a href="javascript:void(0);" class="fi_close-error">close</a>
      </p>
    </div>
  <?php endif; ?>
  
  <?php if(!empty($success['general'])): ?>
    <div class="updated below-h2" id="fi_message-succes">
      <p>
        <?php  echo $success['general']; ?>
        <a href="javascript:void(0);" class="fi_close-succes">close</a>
      </p>
    </div>
  <?php endif; ?>
  
  <div class="bm_wrap">
    <form name="insert_event_form" id="insert_event_form" method="POST" action="" enctype="multipart/form-data">
      <table class="widefat bm">
        <thead>
          <tr>
            <th colspan="3">Events</th>
          </tr>
        </thead>
        <tfoot>
          <tr>
            <th colspan="3">
              After you have finish you can "Save" the settings
            </th>
          </tr>
        </tfoot>
        <tbody>
          <?php if(!empty($existing_image->img_src)): ?>
            <tr valign="top">
              <td width="150">Existing Image </td>
              <td>:</td>
              <td>
              
                <div class="existing_image_outer_container">
                    <div class="warning_placeholder">
                      Warning: if you replace the image from previously (or) existing image, 
                      you should save this form first before you can crop.</div>
                    <div class="existing_image_inner_container_img">
                      <img src="<?php echo BM_CONTENT_UPLOADS_URL._EVE._ORI.'/'.$existing_image->img_src; ?>" />
                    </div>
                    
                    <div class="existing_image_inner_container_button">
                      <div id="show_image_button" class="ajax_button fleft">
                        <a class="thickbox" href="<?php echo $cropper_url.'?id='.$existing_image->id.'&type=4'.$thickbox_param; ?>">
                          Show Image
                        </a>
                      </div>
                      <div class="msg_crop">&nbsp;</div>
                    </div><!-- existing_image_inner_container_img -->
                    
                </div><!-- existing_image_outer_container -->

                <input type="hidden" name="events[crop_cords]" id="bm_def_crop_cords" value="<?php echo $existing_image->crop_cords; ?>" />
                <input type="hidden" name="bm_def_width" id="bm_def_width" value="<?php echo $bm_def_width; ?>" />
                <input type="hidden" name="bm_def_height" id="bm_def_height" value="<?php echo $bm_def_height; ?>" />
                <input type="hidden" name="bm_def_repeated" id="bm_def_repeated" value="<?php echo $bm_def_repeated; ?>" />
                
              </td>
            </tr>
          <?php endif; ?>
          <tr valign="top">
            <td>Events Date</td>
            <td>:</td>
            <td>
              <input type="text" name="events[ref_data]" class="wide" id="ref_data" value="<?php echo $ref_data; ?>" readonly="readonly" />
            </td>
          </tr>
          <tr valign="top">
            <td>Image</td>
            <td width="5">:</td>
            <td><input type="file" name="image" id="image_upload" /></td>
          </tr>
          <tr valign="top">
            <td>Description (max. 250 char.)</td>
            <td>:</td>
            <td>
              <textarea name="events[description]"><?php echo $existing_image->description; ?></textarea>
            </td>
          </tr>
          <tr valign="top">
            <td>Link (using http://)</td>
            <td>:</td>
            <td>
              <input type="text" name="events[link]" class="wide" id="link" value="<?php echo $existing_image->link; ?>" />
            </td>
          </tr>
          <tr valign="top">
            <td>Active</td>
            <td>:</td>
            <td>
              <input type="checkbox" id="bm_active" name="events[active]" <?php echo ($existing_image->active)?'checked':''; ?> value="1" /> 
            </td>
          </tr>
          <tr valign="top">
            <td>&nbsp;</td>
            <td>
              <input type="hidden" name="events[id]" id="bm_id" value="<?php echo $existing_image->id; ?>" />
              <input type="hidden" name="events[file]" id="bm_file" value="<?php echo $existing_image->img_src; ?>" />
              <input type="hidden" name="events[image_status]" id="bm_image_status" value="1" />
            </td>
            <td>
              <input class="button-primary" type="submit" name="save" value="Save" />
              <a href="<?php echo get_admin_url().'admin.php?page=dn_bm-events'; ?>" class="button-primary delete"><span>Back</span></a>
              <?php wp_nonce_field('dn-bm-update-events'); ?>
            </td>
          </tr>
        </tbody>
      </table>
    </form>
  </div>
</div>
<!-- end content-outer START -->
