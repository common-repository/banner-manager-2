<!-- start content-outer START -->
<div class="wrap new-wrap">

  <div id="icon-edit" class="icon32"></div>
  <h2>Banner Manager - Event Settings</h2>
  
  <?php if(!empty($errors['general'])): ?>
    <div class="error below-h2" id="notice">
      <p>
        <?php  echo $errors['general']; ?>
      </p>
    </div>
  <?php endif; ?>
  
  <?php if(!empty($success['general'])): ?>
    <div class="updated below-h2" id="message">
      <p>
        <?php  echo $success['general']; ?>
      </p>
    </div>
  <?php endif; ?>

  <div class="nav_tab">
    Settings | 
    <a href="admin.php?page=dn_bm-page_url">Page Url</a> | 
    <a href="admin.php?page=dn_bm-referral_url">Referral Url</a> | 
    <a href="admin.php?page=dn_bm-events">Events </a>
  </div>

  <div class="bm_wrap">
    <form name="insert_event_form" id="insert_event_form" method="POST" action="" enctype="multipart/form-data">
      <table class="widefat bm">
        <thead>
          <tr>
            <th colspan="3">Settings</th>
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
          <!-- Default Banner ====================================================   -->
          <tr valign="top">
            <td colspan="3"><strong>Default Banner</strong></td>
          </tr>
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
                      
                        <?php if ( file_exists( BM_CONTENT_UPLOADS_DIR._DEF._PRO.'/'.$existing_image->img_src ) ) { ?>
                        
                            <img src="<?php echo BM_CONTENT_UPLOADS_URL._DEF._PRO.'/'.$existing_image->img_src; ?>?ver=<?php echo rand(0, 2000) ?>" />
                            
                        <?php } else { ?>
                        
                            <img src="<?php echo BM_CONTENT_UPLOADS_URL._DEF._ORI.'/'.$existing_image->img_src; ?>?ver=<?php echo rand(0, 2000) ?>" />
                            
                        <?php } ?>
                        
                    </div>
                    
                    <div class="existing_image_inner_container_button">
                      <div id="show_image_button" class="ajax_button fleft">
                        <a class="thickbox" href="<?php echo $cropper_url.'?id='.$existing_image->id.'&type=1'.$thickbox_param; ?>">
                          Show Image
                        </a>
                      </div>
                      <div class="msg_crop">&nbsp;</div>
                    </div><!-- existing_image_inner_container_img -->
                    
                </div><!-- existing_image_outer_container -->

                <input type="hidden" name="default_banner[crop_cords]" id="bm_def_crop_cords" value="<?php echo $existing_image->crop_cords; ?>" />
                
              </td>
            </tr>
          <?php endif; ?>
          <tr valign="top">
            <td>Image</td>
            <td width="5">:</td>
            <td><input type="file" name="image" id="image_upload" /></td>
          </tr>
          <!-- Display ====================================================   -->
          <tr valign="top">
            <td colspan="3"><strong>Display</strong></td>
          </tr>
          <tr valign="top">
            <td>HTML embed id</td>
            <td>:</td>
            <td>
              <input type="text" name="bm_def_embed_id" value="<?php echo $bm_def_embed_id; ?>" />
            </td>
          </tr>
          <tr valign="top">
            <td>Timezone</td>
            <td>:</td>
            <td>
              <input type="text" name="bm_def_timezone" value="<?php echo $bm_def_timezone; ?>" />
              <a href="http://www.php.net/manual/en/timezones.php" target="_blank">View Timezone References</a>
            </td>
          </tr>
          <tr valign="top">
            <td>Description (max. 250 char.)</td>
            <td>:</td>
            <td>
              <textarea name="default_banner[description]"><?php echo $existing_image->description; ?></textarea>
            </td>
          </tr>
          <tr valign="top">
            <td>Link (using http://)</td>
            <td>:</td>
            <td>
              <input type="text" name="default_banner[link]" class="wide" id="link" value="<?php echo $existing_image->link; ?>" />
            </td>
          </tr>
          <tr valign="top">
            <td>Repeated</td>
            <td>:</td>
            <td>
              <input type="checkbox" id="bm_def_repeated" name="bm_def_repeated" <?php echo ($bm_def_repeated)?'checked':''; ?> value="1" /> 
              <label for="bm_def_repeated">Yes (when the image are smaller than the container)</label>
            </td>
          </tr>
          <tr valign="top">
            <td>Width x Height (px)</td>
            <td>:</td>
            <td>
              <input type="text" name="bm_def_width" id="bm_def_width" value="<?php echo $bm_def_width; ?>" />
              <input type="text" name="bm_def_height" id="bm_def_height" value="<?php echo $bm_def_height; ?>" />
            </td>
          </tr>
          <tr valign="top">
            <td>&nbsp;</td>
            <td>
              <input type="hidden" name="default_banner[id]" id="bm_id" value="<?php echo $existing_image->id; ?>" />
              <input type="hidden" name="default_banner[file]" id="bm_file" value="<?php echo $existing_image->img_src; ?>" />
              <input type="hidden" name="default_banner[image_status]" id="bm_image_status" value="" />
            </td>
            <td>
              <button class="button-primary" type="submit" name="dn_save_nonce" value="<?php echo wp_create_nonce('dn_save_nonce') ?>" />Save</button>
              <button class="button-primary delete" type="submit" name="dn_delete_nonce" onClick="javascript:if(!confirm(CONFIRM_MSG)){return false}" value="<?php echo wp_create_nonce('dn_delete_nonce') ?>" />Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </form>
  </div>
</div>
<!-- end content-outer START -->
