<?php
/**
 * Description...
 *
 */

class DN_Referral_Url extends DN_Base
{
  public function DN_Referral_Url()
  {
    // Check for current page panel
    // WordPress require us to load their css and scripts here
    $current_page = (isset($_GET['page'])) ? $_GET['page'] : '';
    if ($current_page == 'dn_bm-referral_url')
    {
      $this->admin_init();
    }
  }
  
  public function admin_init()
  {
    wp_enqueue_script( 'dn_bm-referral_url', DN_Base::get_static_url().'scripts/banner.manager.referral_url.js', array('jquery'));
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
    if ($this->app()->dn_validation->has_error_message('referral_url'))
    {
      $errors = $this->app()->dn_validation->get_error('referral_url');
    }

    // Check if we having some success messages from submission
    // Each fetched messages will automatically removed from cache
    $success = array();
    if ($this->app()->dn_validation->has_success_message('referral_url'))
    {
      $success = $this->app()->dn_validation->get_success('referral_url');
    }
    
    $action = $this->app()->dn_utility->xss_clean($_GET['act']);
    
    switch ($action)
    {
      case 'edit':
        $this->referral_url_edit_new($errors, $success);
      break;
      case 'new':
        $this->referral_url_edit_new($errors, $success);
      break;
      case 'del':
        $this->referral_url_del($errors, $success);
      break;
      break;
      case 'act':
        $this->referral_url_act($errors, $success);
      break;
      default:
        $this->referral_url($errors, $success);
      break;
    }
  }
  
  private function referral_url($errors, $success)
  {
    global $wpdb;
    $this->app()->load->library('DN_Pagination');
    
    $link_per_page = 10;
    $page = isset( $_GET['p'] ) ? absint( $_GET['p'] ) : 1;
        
    $ori_referral_url_list = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'banner_manager WHERE type = 4');
    
    $this->app()->dn_pagination->Items(count($ori_referral_url_list));
    $this->app()->dn_pagination->limit($link_per_page);
    $this->app()->dn_pagination->adjacents(1);
    $this->app()->dn_pagination->currentPage($page);
    $this->app()->dn_pagination->parameterName('p');
    $this->app()->dn_pagination->target(WP_ADMIN_URL.'admin.php?page=dn_bm-referral_url');
    $this->app()->dn_pagination->nextLabel('');
    $this->app()->dn_pagination->prevLabel('');
    $this->app()->dn_pagination->nextIcon('<img src="'.DN_Base::get_static_url().'images/table/paging_right.gif" alt="" />');
    $this->app()->dn_pagination->prevIcon('<img src="'.DN_Base::get_static_url().'images/table/paging_left.gif" alt="" />');
    
    $paged = ($page - 1 ) * $link_per_page;
    
    $referral_url_list = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'banner_manager WHERE type = 4 LIMIT '.$paged.', '.$link_per_page );
    
    $this->app()->load->view('referral_url', array(
      'static_url' => DN_Base::get_static_url(),
      'errors' => $errors,
      'pagination' => $this->app()->dn_pagination->getOutput(),
      'success' => $success,
      'referral_url_list' => $referral_url_list
    ));
  }
      
  private function referral_url_edit_new($errors, $success)
  {
    global $wpdb;
    $this->app()->load->library('DN_Image');
    
    $uploader_args = array(
      'field' => 'image',
      'upload_path' => _BM._REF._ORI.'/'
    );
    
    $this->app()->dn_upload->initialize($uploader_args);
    
    $save = (isset($_POST['save'])) ? TRUE : FALSE;
    
    if ($save)
    {
      $post = $this->app()->dn_utility->xss_clean($_POST['referral_url']);

      if (wp_verify_nonce($this->app()->dn_utility->xss_clean($_POST['_wpnonce']), 'dn-bm-update-referral_url'))
      {
        if(empty($post['id']))
        {
          /* new mode */
          if($_FILES['image']['size'] != 0)
          {
            /* upload mode */
            $new_file = $this->app()->dn_upload->upload();
            if(empty($new_file)){
              $this->app()->dn_validation->set_error('referral_url', 'general', __('Process not identified.'.$this->app()->dn_upload->errors, 'dn_bm'));
            }else{
              $mini_image_config['source_image'] =  BM_CONTENT_UPLOADS_DIR._REF._ORI.'/'.$new_file['filename'];
              $mini_image_config['new_image'] =  BM_CONTENT_UPLOADS_DIR._REF._TMP.'/mini_'.$new_file['filename'];
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
                'type' => 4,
                'active' => $post['active']
              )); 
              $this->app()->dn_validation->set_success('referral_url', 'general', __('Saved', 'dn_bm'));
            }
          }else{
            $this->app()->dn_validation->set_error('referral_url', 'general', __('Image File Cannot Be Empty', 'dn_bm'));
          }
        }
        elseif(!empty($post['id']))
        {
          /* edit mode */
          $re_upload = FALSE;
          if($_FILES['image']['size'] != 0)
          {
            @unlink( BM_CONTENT_UPLOADS_DIR._REF._ORI.'/'.$post['file'] );
            @unlink( BM_CONTENT_UPLOADS_DIR._REF._PRO.'/'.$post['file'] );
            @unlink( BM_CONTENT_UPLOADS_DIR._REF._TMP.'/'.$post['file'] );
            @unlink( BM_CONTENT_UPLOADS_DIR._REF._TMP.'/mini_'.$post['file'] );
            $new_file = $this->app()->dn_upload->upload();
            $re_upload = TRUE;
          }
          
          if($re_upload){
            $img_src = $new_file['filename'];
          }elseif(!$re_upload){
            $img_src = $post['file'];
          }
            
          if($post['image_status'] == 1){
            if(!is_dir(BM_CONTENT_UPLOADS_DIR._REF._PRO.'/')){
              mkdir(BM_CONTENT_UPLOADS_DIR._REF._PRO.'/', 0777, TRUE);
            }
            @copy(BM_CONTENT_UPLOADS_DIR._REF._TMP.'/'.$img_src, BM_CONTENT_UPLOADS_DIR._REF._PRO.'/'.$img_src);
            $post['image_status'] = 2;
          }
            
          /* save to database */
          $wpdb->update($wpdb->prefix.'banner_manager', array(
            'ref_data' => $post['ref_data'],
            'description' => $post['description'],
            'link' => $post['link'],
            'img_src' => $img_src,
            'crop_cords' => $post['crop_cords'],
            'type' => 4,
            'active' => $post['active']
          ), array('id' => $post['id']));

          $this->app()->dn_validation->set_success('referral_url', 'general', __('Saved', 'dn_bm'));
        }
        
        ?>
        <script type="text/javascript">
        //<![CDATA[
        document.location.href = "<?php echo WP_ADMIN_URL; ?>admin.php?page=dn_bm-referral_url&act=edit<?php echo (!empty($post['id'])) ? '&bm_id='.$post['id'] : '&bm_id='.$wpdb->insert_id;; ?>";
        //]]>
        </script>
        <?php
        
        exit();
        
      }
    }
    
    $bm_id = (isset($_GET['bm_id'])) ? $this->app()->dn_utility->xss_clean($_GET['bm_id']) : 0;
    
    if($bm_id){
      $existing_image = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."banner_manager WHERE id = '".$bm_id."'");
    }
    
    $cropper_url = plugins_url( 'DN_Banner_Cropper.php', __FILE__ );
    $thickbox_param = '&TB_iframe=true&height=500&width=1000';
    
    $this->app()->load->view('referral_url_form', array(
      'static_url' => DN_Base::get_static_url(),
      'errors' => $errors,
      'success' => $success,
      'existing_image' => $existing_image,
      'cropper_url' => $cropper_url,
      'thickbox_param' => $thickbox_param,
      'bm_def_repeated' => get_option('bm_def_repeated'),
      'bm_def_width' => get_option('bm_def_width'),
      'bm_def_height' => get_option('bm_def_height')
    ));
  }
  
  private function referral_url_del($errors, $success)
  {
    global $wpdb;
    
    $bm_id = (isset($_GET['bm_id'])) ? $this->app()->dn_utility->xss_clean($_GET['bm_id']) : 0;

    if (!empty($bm_id) && wp_verify_nonce($this->app()->dn_utility->xss_clean($_GET['_wpnonce']), 'dn-bm-delete-referral_url'))
    {
      
      $img_src = $wpdb->get_row('SELECT img_src FROM '.$wpdb->prefix.'banner_manager WHERE id = "'.$bm_id.'"');
      $img_src = $img_src->img_src;

      if(!empty($img_src))
      {
        @unlink(BM_CONTENT_UPLOADS_DIR._REF._ORI.'/'.$img_src);
        @unlink(BM_CONTENT_UPLOADS_DIR._REF._TMP.'/'.$img_src);
        @unlink(BM_CONTENT_UPLOADS_DIR._REF._TMP.'/mini_'.$img_src);
        @unlink(BM_CONTENT_UPLOADS_DIR._REF._PRO.'/'.$img_src);
      }
      
      $wpdb->query('DELETE FROM '.$wpdb->prefix.'banner_manager WHERE id = "'.$bm_id.'"');
      $this->app()->dn_validation->set_success('referral_url', 'general', __('Deleted', 'dn_bm'));
      
    }
    else
    {
      $this->app()->dn_validation->set_error('referral_url', 'general', __('Process not identified.', 'dn_bm'));
    }
    
    ?>
    <script type="text/javascript">
    //<![CDATA[
    document.location.href = "<?php echo WP_ADMIN_URL; ?>admin.php?page=dn_bm-referral_url";
    //]]>
    </script>
    <?php
    
    exit();

  }
  
  private function referral_url_act($errors, $success)
  {
    global $wpdb;
    
    $bm_id = (isset($_GET['bm_id'])) ? $this->app()->dn_utility->xss_clean($_GET['bm_id']) : 0;
    $action = (isset($_GET['action'])) ? $this->app()->dn_utility->xss_clean($_GET['action']) : 0;

    if (!empty($bm_id) && wp_verify_nonce($this->app()->dn_utility->xss_clean($_GET['_wpnonce']), 'dn-bm-active-referral_url'))
    {
      if($action == 1)
      {
        $wpdb->query('UPDATE '.$wpdb->prefix.'banner_manager SET active = 0 WHERE id = "'.$bm_id.'"');
        $this->app()->dn_validation->set_success('referral_url', 'general', __('Inactived', 'dn_bm'));
      }
      elseif($action == 0)
      {
        $wpdb->query('UPDATE '.$wpdb->prefix.'banner_manager SET active = 1 WHERE id = "'.$bm_id.'"');
        $this->app()->dn_validation->set_success('referral_url', 'general', __('Actived', 'dn_bm'));
      }
    }
    else
    {
      $this->app()->dn_validation->set_error('referral_url', 'general', __('Process not identified.', 'dn_bm'));
    }
    
    ?>
    <script type="text/javascript">
    //<![CDATA[
    document.location.href = "<?php echo WP_ADMIN_URL; ?>admin.php?page=dn_bm-referral_url";
    //]]>
    </script>
    <?php
    
    exit();

  }
  
}

