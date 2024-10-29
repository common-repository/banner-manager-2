<?php

/**
* Rules :
*	1. Each constants path should not have trailingslash.
* 2. Each constants should use upper case.
**/

//Check if ABSPATH is already deDNned, kill all when it's not.
if (defined('ABSPATH')) {
   define( 'WP_SITES_DIR', ABSPATH);
} else {
	wp_die('You can not access this plugin directly');
}

/****************************
WORDPRESS PATH CONSTANTS
*****************************/

// get_option('siteurl'); make sure that our url according to WordPress url, 
// for single or multi site.  
if ( !defined( 'WP_ADMIN_URL' ) )
	define( 'WP_ADMIN_URL', get_admin_url() );

if ( !defined( 'WP_SITES_URL' ) )
{
	define( 'WP_SITES_URL', get_option( 'siteurl' ));
}

if ( !defined( 'WP_ADMIN_URL' ) )
{
	define( 'WP_ADMIN_URL', admin_url());
}

if ( !defined( 'WP_ADMIN_DIR' ) )
{
	define( 'WP_ADMIN_DIR', WP_SITES_DIR . 'wp-admin' );
}

if ( !defined( 'WP_INCLUDES_DIR' ) )
{
	define( 'WP_INCLUDES_DIR', WP_SITES_DIR . 'wp-includes' );
}

if ( !defined( 'WP_ADMIN_DIR' ) )
{
	define( 'WP_ADMIN_DIR', WP_SITES_DIR . 'wp-admin' );
}

if ( !defined( 'WP_CONTENT_DIR' ) )
	define( 'WP_CONTENT_DIR',  WP_SITES_DIR . 'wp-content' );	
	
if ( !defined( 'WP_PLUGIN_DIR' ) )
	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

//Get WordPress uploads information for single or multi site.
$wp_upload_dir_data = wp_upload_dir();

define('_BM', '/banner_manager');

define( 'BM_CONTENT_UPLOADS_URL' , $wp_upload_dir_data['baseurl']._BM);
define( 'BM_CONTENT_UPLOADS_DIR' , $wp_upload_dir_data['basedir']._BM);

define('_DEF', '/default');
define('_EVE', '/events');
define('_REF', '/referral');
define('_PAG', '/pages');

define('_ORI', '/ori');
define('_PRO', '/pro');
define('_TMP', '/tmp');

/*************************
PLUGGIN PATH CONSTANTS
**************************/
define('DN_PATH', WP_PLUGIN_DIR .'/banner-manager');

define('DN_CONFIG_PATH', DN_PATH.'/configs');
define('DN_LIBRARY_PATH', DN_PATH.'/libraries');
define('DN_MODEL_PATH', DN_PATH.'/models');
define('DN_VIEW_PATH', DN_PATH.'/views');
define('DN_CONTROLLER_PATH', DN_PATH.'/controllers');
define('DN_HELPER_PATH', DN_PATH.'/helpers');
define('DN_RESOURCE_FOLDER', 'resources');
define('DN_RESOURCE_PATH', DN_PATH.'/'.DN_RESOURCE_FOLDER);
