<?php
/*
Plugin Name: Banner Manager
Plugin URI: http://demos.denovembre.com/banner-manager/
Description: Banner Manager.
Version: 2.0
Author: yudhi.404@gmail.com
Author URI: http://denovembre.com
License: A "Slug" license name e.g. GPL2
*/

include_once('configs/DN_Paths.php');

include_once(WP_INCLUDES_DIR . '/pluggable.php');

global $dn_bm;

// Load main library
include_once(DN_PATH . '/libraries/DN_Banner_Manager.php');

$dn_bm = new DN_Banner_Manager;

// Register the installation and uninstallation function
register_activation_hook(__FILE__, array($dn_bm, 'install'));
register_deactivation_hook(__FILE__, array($dn_bm, 'uninstall'));

$dn_bm->bootstrap();
