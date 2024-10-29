<?php
/**
 * Description...
 *
 */
require_once('../../../../wp-admin/admin.php');
require_once('../configs/DN_Paths.php');

include_once(WP_INCLUDES_DIR . '/pluggable.php');

global $dn_bm;

// Load main library
include_once(DN_PATH . '/libraries/DN_Banner_Manager.php');

$dn_bm = new DN_Banner_Manager;
$dn_bm->bootstrap();

class DN_BannerCropper extends DN_Base
{
  public function DN_BannerCropper()
  {
    // Check for current page panel
    // WordPress require us to load their css and scripts here
    $this->admin_init();
  }
  
  public function admin_init()
  {
    wp_enqueue_style('jcrop');
    
    wp_enqueue_script('jcrop', array('jquery'));
    wp_enqueue_script( 'dn_bm-cropper', DN_Base::get_static_url().'scripts/banner.manager.cropper.js', array('jcrop'));
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
    
    $this->cropper($errors, $success);
  }
  
  public function cropper($errors, $success)
  {
    global $wpdb;
    
    $date_mark = array('class' => 'orange', 'data' => $marked_dates);
    $style = plugins_url(DN_RESOURCE_FOLDER.'/static/styles/banner_manager_style.css', DN_RESOURCE_PATH);
    $id = (isset($_GET['id'])) ? $_GET['id'] : '';
    $type = (isset($_GET['type'])) ? $_GET['type'] : '';
    
    $image = $wpdb->get_row("SELECT id, img_src FROM ".$wpdb->prefix."banner_manager WHERE id = '".$id."'");
    
    switch($type){
      case 1:
      $dir_type = _DEF;
      break;
      
      case 2:
      $dir_type = _PAG;
      break;
      
      case 3:
      $dir_type = _REF;
      break;
      
      case 4:
      $dir_type = _EVE;
      break;
    }

    $this->app()->load->view('cropper', array(
      'static_url' => DN_Base::get_static_url(),
      'errors' => $errors,
      'success' => $success,
      'style' => $style,
      'image' => $image,
      'dir_type' => $dir_type
    ));
  }

}

$dn_bu = new DN_BannerCropper;
$dn_bu->init();
