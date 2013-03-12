<?php
/*
 * Plugin Name:   TXTAsPost
 * Version:       3.0
 * Plugin URI:    http://www.wpactions.com
 * Description:	  Convert TXT into wordpress posts
 * Author:        Jesse
 * Author URI:    http://www.wpactions.com
 */
require_once(dirname(__FILE__).'/txtaspost.php');
register_activation_hook(__FILE__, array(&$TXTAsPost, 'install'));
//register_deactivation_hook(__FILE__, array(&$TXTAsPost, 'uninstall'));
?>
