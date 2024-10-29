<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
<head>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
<title><?php bloginfo('name') ?> &rsaquo; <?php _e('Uploads'); ?> &#8212; <?php _e('WordPress'); ?></title>
<?php
wp_enqueue_style( 'global' );
wp_enqueue_style( 'wp-admin' );
wp_enqueue_style( 'colors' );
// Check callback name for 'media'
if ( ( is_array( $content_func ) && ! empty( $content_func[1] ) && 0 === strpos( (string) $content_func[1], 'media' ) ) || 0 === strpos( $content_func, 'media' ) )
	wp_enqueue_style( 'media' );
wp_enqueue_style( 'ie' );
?>
<script type="text/javascript">
//<![CDATA[
addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
var userSettings = {'url':'<?php echo SITECOOKIEPATH; ?>','uid':'<?php if ( ! isset($current_user) ) $current_user = wp_get_current_user(); echo $current_user->ID; ?>','time':'<?php echo time(); ?>'};
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>', pagenow = 'media-upload-popup', adminpage = 'media-upload-popup';
//]]>
</script>
<?php
do_action('admin_enqueue_scripts', 'media-upload-popup');
do_action('admin_print_styles-media-upload-popup');
do_action('admin_print_styles');
do_action('admin_print_scripts-media-upload-popup');
do_action('admin_print_scripts');
do_action('admin_head-media-upload-popup');
do_action('admin_head');

?>
<script type="text/javascript">if(typeof wpOnload=='function')wpOnload();</script>
<body id="bd_crop_wrap">
  <div id="dv_crop_wrap">
    <div class="crop_step">
      <h3>Crop Your Image</h3>
      <div class="crop_step_workspace">
        <img id="image_crop" src="<?php echo BM_CONTENT_UPLOADS_URL.$dir_type._ORI.'/'.$image->img_src; ?>" />
      </div>
      
      <div class="crop_step_buttons">
        <form id="crop_submit">
          <input type="hidden" name="dir_type" id="dir_type" value="<?php echo $dir_type; ?>" />
          <input type="hidden" name="x" id="x" />
          <input type="hidden" name="y" id="y" />
          <input type="hidden" name="w" id="w" />
          <input type="hidden" name="h" id="h" />
          <input type="hidden" name="src" value="<?php echo $image->img_src; ?>" />
          <input type="submit" class="button-primary" name="do_crop" id="do_crop" value="Preview Crop"> 
          <a href="javascript:void(0);" class="button" id="set_real_size" >Set to The Real Size</a>
          <a href="javascript:void(0);" class="button" id="set_free_ratio" >Set Free Ratio</a>
          <a href="javascript:void(0);" onclick="try{top.tb_remove();}catch(e){}; return false;" class="button">Cancel Crop</a>
          <div class="msg_crop fright">&nbsp;</div>
        </form>
      </div>
      
    </div>
    <div class="preview_step">
      <h3>Preview Your Banner</h3>
      <div class="preview_step_workspace">
        <div id="banner_preview"></div>
      </div>
      
      <div class="preview_step_buttons">
        <form id="crop_submit">
        <input type="button" class="button-primary" id="save_crop" value="Save Crop" />
        <a href="javascript:void(0);" class="button" id="go_to_crop_step" >Go Back to Crop Tool</a>
        <div class="msg_preview fright">&nbsp;</div>
        </form>
      </div>
      
    </div>
  </div>
</body>
</html>
<?php
do_action('admin_footer', '');
do_action('admin_print_footer_scripts');
do_action("admin_footer-" . $GLOBALS['hook_suffix']);
?>
