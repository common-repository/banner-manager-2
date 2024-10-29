<?php
/**
 * Description...
 *
 */
include_once(DN_PATH . '/libraries/DN_Base.php');

class DN_Banner_Manager extends DN_Base
{
  /**
  * List of links.
  *
  * @var array
  */
  //private $links = array();
  
  public function bootstrap()
  {
    // Load language
    
    if ($this->load)
    {
      // Load needed controllers
      add_action('wp_head', array(&$this, 'front_banner_manager'));
      add_action('init', array(&$this, 'banner_manager_front_loader'));
      if ( is_admin() )
      {
        // Load needed controllers
        $this->load->controller('DN_Settings', 'dn_settings_ctrl');
        $this->load->controller('DN_Events', 'dn_events_ctrl');
        $this->load->controller('DN_Page_Url', 'dn_page_url_ctrl');
        $this->load->controller('DN_Referral_Url', 'dn_referral_url_ctrl');
        $this->load->controller('DN_Events', 'dn_events_ctrl');
        
        add_action('admin_menu', array(&$this, 'admin_menu'));
        add_action('wp_print_scripts', array(&$this, 'js_admin_inject_code'));
        add_action('admin_init', array(&$this, 'banner_manager_admin_loader'));
        add_action('wp_ajax_crop_image', array(&$this, 'crop_image'));
      }
    }
  }
  
  public function admin_menu()
  {
    $page = add_menu_page('Banner Manager', 'Banner', 'manage_options', 'dn_bm', array($this->dn_settings_ctrl, 'init'));
    add_action( 'admin_head-'. $page, array($this->dn_settings_ctrl, 'admin_head') );
    
    $page = add_submenu_page('dn_bm', 'Settings', 'Settings', 'manage_options', 'dn_bm', array($this->dn_settings_ctrl, 'init'));
    add_action( 'admin_head-'. $page, array($this->dn_settings_ctrl, 'admin_head') );

    $page = add_submenu_page('dn_bm', 'Page URL', 'Page URL', 'manage_options', 'dn_bm-page_url', array($this->dn_page_url_ctrl, 'init'));
    add_action( 'admin_head-'. $page, array($this->dn_page_url_ctrl, 'admin_head') );

    $page = add_submenu_page('dn_bm', 'Referral URL', 'Referral URL', 'manage_options', 'dn_bm-referral_url', array($this->dn_referral_url_ctrl, 'init'));
    add_action( 'admin_head-'. $page, array($this->dn_referral_url_ctrl, 'admin_head') );
    
    $page = add_submenu_page('dn_bm', 'Events', 'Events', 'manage_options', 'dn_bm-events', array($this->dn_events_ctrl, 'init'));
    add_action( 'admin_head-'. $page, array($this->dn_events_ctrl, 'admin_head') );
  }

  public function js_admin_inject_code()
  {
    $js_inline  = '<script type="text/javascript">'."\n";
    $js_inline .= '//<![CDATA['."\n";
    $js_inline .= ' var base_url = "'.get_bloginfo('wpurl').'";'."\n";
    $js_inline .= '//]]>'."\n";
    $js_inline .= '</script>'."\n";
    
    echo $js_inline;
  }  
  
  public function crop_image()
  {
    $this->load->library('DN_Image');
    $image_config = array(
                  'source_image' => BM_CONTENT_UPLOADS_DIR.$_POST['dir_type']._ORI.'/'.$_POST['src'],
                  'new_image' =>  BM_CONTENT_UPLOADS_DIR.$_POST['dir_type']._TMP.'/'.$_POST['src'],
                  'x_axis' => $_POST['x'],
                  'y_axis' => $_POST['y'],
                  'width' => $_POST['w'],
                  'height' => $_POST['h'],
                  'maintain_ratio' => FALSE
                  );
    $this->app()->dn_image->initialize($image_config);
    $this->app()->dn_image->crop();
    
    $user_crop = $this->app()->dn_image->new_image;
    
    $this->app()->dn_image->clear();

    $mini_image_config['source_image'] =  BM_CONTENT_UPLOADS_DIR.$_POST['dir_type']._TMP.'/'.$_POST['src'];
    $mini_image_config['new_image'] =  BM_CONTENT_UPLOADS_DIR.$_POST['dir_type']._TMP.'/mini_'.$_POST['src'];
    $mini_image_config['width'] = 78;
    $mini_image_config['maintain_ratio'] = TRUE;
                  
    $this->app()->dn_image->initialize($mini_image_config);
    $this->app()->dn_image->resize();
    
    echo str_replace(BM_CONTENT_UPLOADS_DIR, BM_CONTENT_UPLOADS_URL, $user_crop);
    die(0);
  }
  
  public function banner_manager_admin_loader() 
  {
    wp_enqueue_style('banner_manager_admin_css', DN_Base::get_static_url() . 'styles/banner_manager_style.css');
    wp_enqueue_style('thickbox');
    
    wp_enqueue_script('jquery');
    wp_enqueue_script('banner_manager_admin_js', DN_Base::get_static_url() . 'scripts/banner.manager.admin.js', array('jquery'));
    wp_enqueue_script('thickbox', array('jquery'));
  }
  
  function front_banner_manager() {
    global $wpdb;
    
    //$yesterday = date('d/m/y', mktime(0, 0, 0, date("m") , date("d") - 1, date("Y")));
    $date_default_timezone_set = get_option('bm_def_timezone');
    
    date_default_timezone_set($date_default_timezone_set);
    $cur_date = date('n-j');
    $cur_url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
    $cur_referrer = $_SERVER['HTTP_REFERER'];
    
    $default = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."banner_manager WHERE type = 1 AND active = 1");
    $events = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."banner_manager WHERE type = 2 AND active = 1 AND ref_data = '".$cur_date."'");
    $pages = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."banner_manager WHERE type = 3 AND active = 1 AND ref_data = '".$cur_url."'");
    $referrals = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."banner_manager WHERE type = 4 AND active = 1 AND ref_data = '".$cur_referrer."'");
    
    wp_enqueue_script('jquery');
    
    $repeat = get_option('bm_def_repeated');
    (!empty($repeat)?$repeat = 'repeated':$repeat = 'no-repeat');
    
    $embed_id = get_option('bm_def_embed_id');
    
    $script = '<script type="text/javascript">'."\n";
    $script .= '//<![CDATA['."\n";
    $script .= 'jQuery(document).ready(function(){'."\n";
    $script .= '  jQuery(\'#' . $embed_id . '\').css({'."\n";
    
    if(!empty($referrals)){
      $script .= '      \'background-image\' : \'url('.BM_CONTENT_UPLOADS_URL._REF._PRO.'/'.$referrals->img_src.')\','."\n";
      $link = $referrals->link;
      $description = $referrals->description;
    }elseif(!empty($pages)){
      $script .= '      \'background-image\' : \'url('.BM_CONTENT_UPLOADS_URL._PAG._PRO.'/'.$pages->img_src.')\','."\n";
      $link = $pages->link;
      $description = $pages->description;
    }elseif(!empty($events)){
      $script .= '      \'background-image\' : \'url('.BM_CONTENT_UPLOADS_URL._EVE._PRO.'/'.$events->img_src.')\','."\n";
      $link = $events->link;
      $description = $events->description;
    }elseif(!empty($default)){
      $script .= '      \'background-image\' : \'url('.BM_CONTENT_UPLOADS_URL._DEF._PRO.'/'.$default->img_src.')\','."\n";
      $link = $default->link;
      $description = $default->description;
    }
    
    $script .= '      \'background-repeat\' : \''.$repeat.'\','."\n";
    $script .= '      \'background-position\' : \'top center\''."\n";
    $script .= '  });'."\n";
        
    if(!empty($link)){
      $script .= '  jQuery(\'.banner_manager\').click(function(){'."\n";
      $script .= '      document.location.href = "'.$link.'"'."\n";
      $script .= '  });'."\n";
    }
    
    if(!empty($description)){
      $script .= '  jQuery(\'.banner_manager\').tooltip({'."\n";
      $script .= '      \'cursor\' : \'pointer\','."\n";
      $script .= '      \'dataAttr\' : \'tooltip_bm\''."\n";
      $script .= '  });'."\n";
      echo '<div id="tooltip_bm" style="display:none">'.$description.'</div>';
    }
    
    $script .= '});'."\n";
    $script .= '//]]>'."\n";
    $script .= '</script>'."\n";
    
    echo $script;
  }
  
  public function banner_manager_front_loader(){
    wp_enqueue_script('jquery');
    wp_enqueue_script('bm_front_script-qtip', DN_Base::get_static_url() . 'scripts/jquery.qtip.js', array('jquery'));
  }
  
  public function install()
  {
    global $wpdb;

    $sql = 'CREATE TABLE `'.$wpdb->prefix.'banner_manager` (';
    $sql .= '`id` int(11) NOT NULL AUTO_INCREMENT,';
    $sql .= '`ref_data` varchar(250) NOT NULL,';
    $sql .= '`description` varchar(250) NOT NULL,';
    $sql .= '`link` varchar(250) NOT NULL,';
    $sql .= '`img_src` varchar(250) NOT NULL,';
    $sql .= '`crop_cords` varchar(100) NOT NULL COMMENT "x, y, w, h",';
    $sql .= '`type` int(11) NOT NULL COMMENT "1:def;2:events;3:pages;4:referrals;",';
    $sql .= '`active` int(1) NOT NULL,';
    $sql .= 'PRIMARY KEY (`id`)';
    $sql .= ') ENGINE = MYISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=33';
    
    $wpdb->query($sql);
  }
  
  public function uninstall()
  {
    global $wpdb;
    
    $sql = 'DROP TABLE `'.$wpdb->prefix.'banner_manager` ';
    
    $wpdb->query($sql);
  }
}
