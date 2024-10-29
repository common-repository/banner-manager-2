<?php
/**
 * Description...
 *
 */

include_once(DN_LIBRARY_PATH . '/DN_Exceptions.php');
include_once(DN_LIBRARY_PATH . '/DN_Loader.php');

class DN_Base
{
  private static $instance;
  
  public function DN_Base()
  {
    if (!(self::$instance instanceof self))
    {
      self::$instance =& $this;
      $this->load = new DN_Loader;
    }
  }
  
  public static function &app()
  {
    return self::$instance;
  }
  
  /**
  * Get plugin url
  *
  * @return string
  **/
  public static function get_url()
  {
    //Try to use WP API if possible, introduced in WP 2.6
    if (function_exists('plugins_url'))
    {
      $url = trailingslashit(plugins_url(basename(DN_PATH)));
    }
    else
    {
      //Try to find manually... can't work if wp-content was renamed or is redirected
      $url = str_replace("\\", "/", DN_PATH);
      $url = trailingslashit(get_bloginfo('wpurl')) . trailingslashit(substr(DN_PATH, strpos(DN_PATH, 'wp-content/')));
    }
    
    return $url;
  }
  
  /**
  * Get static url
  *
  * @return string
  **/
  public static function get_static_url()
  {
    // Get the plugin url first
    $url = DN_Base::get_url();
    
    $url .= trailingslashit(DN_RESOURCE_FOLDER).trailingslashit('static');
    
    return $url;
  }
  
  /**
  * Get current url
  *
  * @return string
  **/
  public static function get_current_url()
  {
    $pageURL = 'http';
    
    if ($_SERVER["HTTPS"] == "on")
    {
      $pageURL .= "s";
    }
    
    $pageURL .= "://";
    
    if ($_SERVER["SERVER_PORT"] != "80")
    {
      $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    }
    else
    {
      $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    }
    
    return $pageURL;
  }
}
