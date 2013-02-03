<?php
/* 
 * Plugin Name:   TXTAsPost
 * Version:       0.1
 * Plugin URI:    http://www.wordpressplugindeveloper.com
 * Description:	  Coded By http://www.wordpressplugindeveloper.com
 * Author:        Jesse
 * Author URI:    http://www.wordpressplugindeveloper.com
 */
require_once(dirname(__FILE__).'/txtaspost.php');
register_activation_hook(__FILE__, array(&$TXTAsPost, 'install'));
//register_deactivation_hook(__FILE__, array(&$TXTAsPost, 'uninstall'));
?>
