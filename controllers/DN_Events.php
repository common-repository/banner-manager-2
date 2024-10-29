<?php
/**
 * Description...
 *
 */

class DN_events extends DN_Base
{
  public function DN_events()
  {
    // Check for current page panel
    // WordPress require us to load their css and scripts here
    $current_page = (isset($_GET['page'])) ? $_GET['page'] : '';
    if ($current_page == 'dn_bm-events')
    {
      $this->admin_init();
    }
  }
  
  public function admin_init()
  {
    wp_enqueue_script( 'dn_bm-events', DN_Base::get_static_url().'scripts/banner.manager.events.js', array('jquery'));
  }
  
  public function admin_head()
  {
    ?>
    <script type="text/javascript">
    //<![CDATA[
      var ADMIN_URL = "<?php echo WP_ADMIN_URL; ?>";
			var STATIC_URL = "<?php echo DN_Base::get_static_url(); ?>";
			var CONFIRM_MSG = "<?php _e("You are about to permanently delete the selected items. 'Cancel' to stop, 'OK' to delete.", "dn_bm"); ?>";
	  //]]>
    </script>
    <?php
  }
  
  public function init()
  {
    $this->app()->load->library('DN_Validation');
    $this->app()->load->library('DN_Utility');
    $this->app()->load->library('DN_Upload');
    
    // Check if we having some error messages from submission
    // Each fetched messages will automatically removed from cache
    $errors = array();
    if ($this->app()->dn_validation->has_error_message('events'))
    {
      $errors = $this->app()->dn_validation->get_error('events');
    }

    // Check if we having some success messages from submission
    // Each fetched messages will automatically removed from cache
    $success = array();
    if ($this->app()->dn_validation->has_success_message('events'))
    {
      $success = $this->app()->dn_validation->get_success('events');
    }
    
    $action = $this->app()->dn_utility->xss_clean($_GET['act']);
    
    switch ($action)
    {
      case 'edit':
        $this->events_edit_new($errors, $success);
      break;
      case 'new':
        $this->events_edit_new($errors, $success);
      break;
      case 'del':
        $this->events_del($errors, $success);
      break;
      break;
      case 'act':
        $this->events_act($errors, $success);
      break;
      default:
        $this->events($errors, $success);
      break;
    }
  }
  
  private function events($errors, $success)
  {
    global $wpdb;
    $this->app()->load->library('DN_Pagination');
    $this->app()->load->library('DN_Calendar');
    
    $link_per_page = 10;
    $page = isset( $_GET['p'] ) ? absint( $_GET['p'] ) : 1;
        
    $ori_events_list = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'banner_manager WHERE type = 2');
    
    $this->app()->dn_pagination->Items(count($ori_events_list));
    $this->app()->dn_pagination->limit($link_per_page);
    $this->app()->dn_pagination->adjacents(1);
    $this->app()->dn_pagination->currentPage($page);
    $this->app()->dn_pagination->parameterName('p');
    $this->app()->dn_pagination->target(WP_ADMIN_URL.'admin.php?page=dn_bm-events');
    $this->app()->dn_pagination->nextLabel('');
    $this->app()->dn_pagination->prevLabel('');
    $this->app()->dn_pagination->nextIcon('<img src="'.DN_Base::get_static_url().'images/table/paging_right.gif" alt="" />');
    $this->app()->dn_pagination->prevIcon('<img src="'.DN_Base::get_static_url().'images/table/paging_left.gif" alt="" />');
    
    $paged = ($page - 1 ) * $link_per_page;
    
    $events_list = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'banner_manager WHERE type = 2 LIMIT '.$paged.', '.$link_per_page );
    
    $mark_events_list = $wpdb->get_results('SELECT ref_data FROM '.$wpdb->prefix.'banner_manager WHERE type = 2', ARRAY_N);
    
    $this->app()->dn_calendar->marker(array( 'data' => $mark_events_list, 'class' => 'orange' ) );
    
    $this->app()->load->view('events', array(
      'static_url' => DN_Base::get_static_url(),
      'errors' => $errors,
      'pagination' => $this->app()->dn_pagination->getOutput(),
      'success' => $success,
      'events_list' => $events_list,
      'calendar' => $this->app()->dn_calendar->show_year()
    ));
  }
      
  private function events_edit_new($errors, $success)
  {
    $ref_data = (isset($_GET['ref_data'])) ? $_GET['ref_data'] : '';
    if(!$ref_data){
      $this->app()->dn_validation->set_error('events', 'general', __('Process not identified.', 'dn_bm'));
      ?>
      <script type="text/javascript">
      //<![CDATA[
      document.location.href = "<?php echo WP_ADMIN_URL; ?>admin.php?page=dn_bm-events";
      //]]>
      </script>
      <?php
    }

    global $wpdb;
    $this->app()->load->library('DN_Image');
    
    $uploader_args = array(
      'field' => 'image',
      'upload_path' => _BM._EVE._ORI.'/'
    );
    
    $this->app()->dn_upload->initialize($uploader_args);
    
    $save = (isset($_POST['save'])) ? TRUE : FALSE;
    
    if ($save)
    {
      $post = $this->app()->dn_utility->xss_clean($_POST['events']);

      if (wp_verify_nonce($this->app()->dn_utility->xss_clean($_POST['_wpnonce']), 'dn-bm-update-events'))
      {
        if(empty($post['id']))
        {
          /* new mode */
          if($_FILES['image']['size'] != 0)
          {
            /* upload mode */
            $new_file = $this->app()->dn_upload->upload();
            if(empty($new_file)){
              $this->app()->dn_validation->set_error('events', 'general', __('Process not identified.'.$this->app()->dn_upload->errors, 'dn_bm'));
            }else{
              $mini_image_config['source_image'] =  BM_CONTENT_UPLOADS_DIR._EVE._ORI.'/'.$new_file['filename'];
              $mini_image_config['new_image'] =  BM_CONTENT_UPLOADS_DIR._EVE._TMP.'/mini_'.$new_file['filename'];
              $mini_image_config['width'] = 78;
              $mini_image_config['height'] = 78;
              $mini_image_config['maintain_ratio'] = TRUE;

              $this->app()->dn_image->initialize($mini_image_config);
              $this->app()->dn_image->resize();
              
              /* save to database */
              $wpdb->insert($wpdb->prefix.'banner_manager', array(
                'ref_data' => $post['ref_data'],
                'description' => $post['description'],
                'link' => $post['link'],
                'img_src' => $new_file['filename'],
                'crop_cords' => $post['crop_cords'],
                'type' => 2,
                'active' => $post['active']
              )); 
              $this->app()->dn_validation->set_success('events', 'general', __('Saved', 'dn_bm'));
            }
          }else{
            $this->app()->dn_validation->set_error('events', 'general', __('Image File Cannot Be Empty', 'dn_bm'));
          }
        }
        elseif(!empty($post['id']))
        {
          /* edit mode */
          $re_upload = FALSE;
          if($_FILES['image']['size'] != 0)
          {
            @unlink( BM_CONTENT_UPLOADS_DIR._EVE._ORI.'/'.$post['file'] );
            @unlink( BM_CONTENT_UPLOADS_DIR._EVE._PRO.'/'.$post['file'] );
            @unlink( BM_CONTENT_UPLOADS_DIR._EVE._TMP.'/'.$post['file'] );
            @unlink( BM_CONTENT_UPLOADS_DIR._EVE._TMP.'/mini_'.$post['file'] );
            $new_file = $this->app()->dn_upload->upload();
            $re_upload = TRUE;
          }
          
          if($re_upload){
            $img_src = $new_file['filename'];
          }elseif(!$re_upload){
            $img_src = $post['file'];
          }
            
          if($post['image_status'] == 1){
            if(!is_dir(BM_CONTENT_UPLOADS_DIR._EVE._PRO.'/')){
              mkdir(BM_CONTENT_UPLOADS_DIR._EVE._PRO.'/', 0777, TRUE);
            }
            @copy(BM_CONTENT_UPLOADS_DIR._EVE._TMP.'/'.$img_src, BM_CONTENT_UPLOADS_DIR._EVE._PRO.'/'.$img_src);
            $post['image_status'] = 2;
          }
            
          /* save to database */
          $wpdb->update($wpdb->prefix.'banner_manager', array(
            'ref_data' => $post['ref_data'],
            'description' => $post['description'],
            'link' => $post['link'],
            'img_src' => $img_src,
            'crop_cords' => $post['crop_cords'],
            'type' => 2,
            'active' => $post['active']
          ), array('id' => $post['id']));

          $this->app()->dn_validation->set_success('events', 'general', __('Saved', 'dn_bm'));
        }
        
        ?>
        <script type="text/javascript">
        //<![CDATA[
        document.location.href = "<?php echo WP_ADMIN_URL; ?>admin.php?page=dn_bm-events&act=edit<?php echo (!empty($post['ref_data'])) ? '&ref_data='.$post['ref_data'] : ''; ?>";
        //]]>
        </script>
        <?php
        
        exit();
        
      }
    }
    
    $bm_id = (isset($_GET['bm_id'])) ? $this->app()->dn_utility->xss_clean($_GET['bm_id']) : 0;
    
    if($ref_data){
      $existing_image = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."banner_manager WHERE ref_data = '".$ref_data."'");
    }
    
    $cropper_url = plugins_url( 'DN_Banner_Cropper.php', __FILE__ );
    $thickbox_param = '&TB_iframe=true&height=500&width=1000';
    
    $this->app()->load->view('events_form', array(
      'static_url' => DN_Base::get_static_url(),
      'errors' => $errors,
      'success' => $success,
      'existing_image' => $existing_image,
      'cropper_url' => $cropper_url,
      'thickbox_param' => $thickbox_param,
      'bm_def_repeated' => get_option('bm_def_repeated'),
      'bm_def_width' => get_option('bm_def_width'),
      'bm_def_height' => get_option('bm_def_height'),
      'ref_data' => $ref_data
    ));
  }
  
  private function events_del($errors, $success)
  {
    global $wpdb;
    
    $bm_id = (isset($_GET['bm_id'])) ? $this->app()->dn_utility->xss_clean($_GET['bm_id']) : 0;

    if (!empty($bm_id) && wp_verify_nonce($this->app()->dn_utility->xss_clean($_GET['_wpnonce']), 'dn-bm-delete-events'))
    {
      
      $img_src = $wpdb->get_row('SELECT img_src FROM '.$wpdb->prefix.'banner_manager WHERE id = "'.$bm_id.'"');
      $img_src = $img_src->img_src;

      if(!empty($img_src))
      {
        @unlink(BM_CONTENT_UPLOADS_DIR._EVE._ORI.'/'.$img_src);
        @unlink(BM_CONTENT_UPLOADS_DIR._EVE._TMP.'/'.$img_src);
        @unlink(BM_CONTENT_UPLOADS_DIR._EVE._TMP.'/mini_'.$img_src);
        @unlink(BM_CONTENT_UPLOADS_DIR._EVE._PRO.'/'.$img_src);
      }
      
      $wpdb->query('DELETE FROM '.$wpdb->prefix.'banner_manager WHERE id = "'.$bm_id.'"');
      $this->app()->dn_validation->set_success('events', 'general', __('Deleted', 'dn_bm'));
      
    }
    else
    {
      $this->app()->dn_validation->set_error('events', 'general', __('Process not identified.', 'dn_bm'));
    }
    
    ?>
    <script type="text/javascript">
    //<![CDATA[
    document.location.href = "<?php echo WP_ADMIN_URL; ?>admin.php?page=dn_bm-events";
    //]]>
    </script>
    <?php
    
    exit();

  }
  
  private function events_act($errors, $success)
  {
    global $wpdb;
    
    $bm_id = (isset($_GET['bm_id'])) ? $this->app()->dn_utility->xss_clean($_GET['bm_id']) : 0;
    $action = (isset($_GET['action'])) ? $this->app()->dn_utility->xss_clean($_GET['action']) : 0;

    if (!empty($bm_id) && wp_verify_nonce($this->app()->dn_utility->xss_clean($_GET['_wpnonce']), 'dn-bm-active-events'))
    {
      if($action == 1)
      {
        $wpdb->query('UPDATE '.$wpdb->prefix.'banner_manager SET active = 0 WHERE id = "'.$bm_id.'"');
        $this->app()->dn_validation->set_success('events', 'general', __('Inactived', 'dn_bm'));
      }
      elseif($action == 0)
      {
        $wpdb->query('UPDATE '.$wpdb->prefix.'banner_manager SET active = 1 WHERE id = "'.$bm_id.'"');
        $this->app()->dn_validation->set_success('events', 'general', __('Actived', 'dn_bm'));
      }
    }
    else
    {
      $this->app()->dn_validation->set_error('events', 'general', __('Process not identified.', 'dn_bm'));
    }
    
    ?>
    <script type="text/javascript">
    //<![CDATA[
    document.location.href = "<?php echo WP_ADMIN_URL; ?>admin.php?page=dn_bm-events";
    //]]>
    </script>
    <?php
    
    exit();

  }
  
}

