<?php
/**
 * Description...
 *
 */

class DN_Settings extends DN_Base
{
  public function DN_Settings()
  {
    // Check for current page panel
    // WordPress require us to load their css and scripts here
    $current_page = (isset($_GET['page'])) ? $_GET['page'] : '';
    if ($current_page == 'dn_bm' || $current_page == 'dn_bm-settings')
    {
      $this->admin_init();
    }
  }
  
  public function admin_init()
  {
    wp_enqueue_script( 'dn_bm-settings', DN_Base::get_static_url().'scripts/banner.manager.settings.js', array('jquery'));
  }
  
  public function admin_head()
  {
    ?>
    <script type="text/javascript">
    //<![CDATA[
      var ADMIN_URL = "<?php echo WP_ADMIN_URL; ?>";
    	var STATIC_URL = "<?php echo $this->get_static_url(); ?>";
    	var CURRENT_URL = "<?php echo WP_ADMIN_URL; ?>admin.php?page=dn_bm";
    	var ERROR_AJAX = "<?php _e('There was a problem while fetching data, please try agin.'); ?>";
			var CONFIRM_MSG = "<?php _e("You are about to permanently delete the selected items. 'Cancel' to stop, 'OK' to delete.", "dn_bm"); ?>";
	  //]]>
    </script>
    <?php
  }
  
  public function init()
  {
    
  	require_once(DN_CONFIG_PATH.'/DN_Locales.php');
    $this->app()->load->library('DN_Utility');
    $this->app()->load->model('DN_Settings_Model');
    $this->app()->load->library('DN_Validation');
    
    $save_nonce = (isset($_POST['dn_save_nonce'])) ? $this->app()->dn_utility->xss_clean($_POST['dn_save_nonce']) : FALSE;
    $delete_nonce = (isset($_POST['dn_delete_nonce'])) ? $this->app()->dn_utility->xss_clean($_POST['dn_delete_nonce']) : FALSE;
    
    if(wp_verify_nonce($save_nonce, 'dn_save_nonce'))
    {
      
      $default_banner_posts_data = $this->app()->dn_utility->xss_clean($_POST['default_banner']);
      
      if(empty( $default_banner_posts_data['id'] ))
      {
        $this->app()->dn_settings_model->saving_new_mode($default_banner_posts_data);
      }
      elseif(!empty( $default_banner_posts_data['id'] ))
      {
        $this->app()->dn_settings_model->saving_edit_mode($default_banner_posts_data);
      }
        
      update_option( 'bm_def_repeated', $this->app()->dn_utility->xss_clean($_POST['bm_def_repeated']) );
      update_option( 'bm_def_width', $this->app()->dn_utility->xss_clean($_POST['bm_def_width']) );
      update_option( 'bm_def_height', $this->app()->dn_utility->xss_clean($_POST['bm_def_height']) );
      update_option( 'bm_def_timezone', $this->app()->dn_utility->xss_clean($_POST['bm_def_timezone']) );
      update_option( 'bm_def_embed_id', $this->app()->dn_utility->xss_clean($_POST['bm_def_embed_id']) );
      
      ?>
		  <script type="text/javascript">/* <![CDATA[ */
		  document.location.href = "<?php echo WP_ADMIN_URL; ?>admin.php?page=dn_bm";
		  /* ]]> */</script>
		  <?php
		  
		  exit();
      
    }
    
    if(wp_verify_nonce($delete_nonce, 'dn_delete_nonce'))
    {
      $this->app()->dn_settings_model->delete_default_data();
      
      ?>
		  <script type="text/javascript">/* <![CDATA[ */
		  document.location.href = "<?php echo WP_ADMIN_URL; ?>admin.php?page=dn_bm";
		  /* ]]> */</script>
		  <?php
		  
		  exit();
      
    }
    
    $default_data = $this->app()->dn_settings_model->default_data();
    $date_selected = date('F d, Y h:i:s A');
    $selected = ' selected="selected"';
    
    $cropper_url = plugins_url( 'DN_Banner_Cropper.php', __FILE__ );
    $thickbox_param = '&TB_iframe=true&height=500&width=1000';

    // Check if we having some error messages from submission
    // Each fetched messages will automatically removed from cache
    $errors = array();
    if ($this->app()->dn_validation->has_error_message('dn_bm'))
    {
      $errors = $this->app()->dn_validation->get_error('dn_bm');
    }

    // Check if we having some success messages from submission
    // Each fetched messages will automatically removed from cache
    $success = array();
    if ($this->app()->dn_validation->has_success_message('dn_bm'))
    {
      $success = $this->app()->dn_validation->get_success('dn_bm');
    }

    // Load View and assign variable values
    $this->app()->load->view('settings', array(
      'static_url' => DN_Base::get_static_url(),
      'errors' => $errors,
      'success' => $success,
      'locale_list' => $locale_list,
      'existing_image' => $default_data,
      'bm_def_repeated' => get_option('bm_def_repeated'),
      'bm_def_width' => get_option('bm_def_width'),
      'bm_def_height' => get_option('bm_def_height'),
      'bm_def_timezone' => get_option('bm_def_timezone'),
      'bm_def_embed_id' => get_option('bm_def_embed_id'),
      'selected' => $selected,
      'date_selected' => $date_selected,
      'cropper_url' => $cropper_url,
      'thickbox_param' => $thickbox_param
    ));

  }
  
  
}
