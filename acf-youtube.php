<?php
/*
Plugin Name: Advanced Custom Fields: YouTube Field
Plugin URI: http://halgatewood.com/downloads/acf-youtube-field
Description: Adds the YouTube field.
Version: 2
Author: Hal Gatewood
Author URI: http://halgatewood.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/


// LANGS
load_plugin_textdomain( 'acf-youtube', false, dirname( plugin_basename(__FILE__) ) . '/lang/' ); 


// GLOBAL FUNCTIONS
include_once('acf-youtube-funcs.php');
include_once('acf-youtube-settings.php');


// VERSION 5+
function include_field_types_youtube( $version ) 
{
	include_once('acf-youtube-v5.php');	
}

add_action('acf/include_field_types', 'include_field_types_youtube');	


// VERSION 4
function register_fields_youtube() 
{
	include_once('acf-youtube-v4.php');
}

add_action('acf/register_fields', 'register_fields_youtube');