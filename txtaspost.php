<?php

if (!class_exists("TXTAsPost")) {
	class TXTAsPost{
		var $plugin_url = '' ;
		var $plugin_path = '' ;
		var $lib_path = '';
		var $version = '3.0';
		var $setting_page = '';
		var $options = '';
		var $option_name = 'TXTAsPost';
		var $error ='';

		function __construct(){
			global $wpdb;
			$this->plugin_path = dirname(__FILE__);
			$this->lib_path = $this->plugin_path.'/lib';
			$this->plugin_url = WP_PLUGIN_URL . '/' . basename(dirname(__FILE__));
			add_action('admin_menu',array(&$this,'add_menu_pages'));
			add_action( 'admin_init', array( &$this, 'install' ) );
			add_filter('safe_style_css',array($this,'safe_style_css'));

		}

		function make_iframe_allowed_tags() {
			global $allowedtags, $allowedposttags;
			$iframe = array(
				'width'=>true,
				'height' => true,
				'src' => true,
				'frameborder'=>true,
				'allowfullscreen'=>true

			);
			$allowedtags['iframe'] = $allowedposttags['iframe'] = $iframe;
		}

		function safe_style_css($arr){
			array_push($arr, 'display');
			return $arr;
		}


		function add_menu_pages(){

			add_menu_page('TXTAsPost', 'TXTAsPost', 'manage_options','txtaspost_settings_page',  array(&$this,'settings_page'));

			$m0=add_submenu_page('txtaspost_settings_page', 'Settings Page', 'Settings Page', 'manage_options','txtaspost_settings_page',  array(&$this,'settings_page'));

			add_action( "admin_print_scripts-$m0", array($this,'js_scripts'));
			add_action( "admin_print_styles-$m0", array($this, 'css_styles') );

		}

		function css_styles(){
			wp_enqueue_style( 'txt-as-post-css',plugins_url( 'static/txtaspost.css' , __FILE__ ) );
		}

		function js_scripts(){

			 wp_enqueue_script( 'txt-as-post-js',plugins_url( 'static/txtaspost.js' , __FILE__ ),array('jquery-ui-datepicker'),'',true );

		}

		function install(){

			$o = $this->get_option();

			if(!$o['version'] || $o['version'] != $this->version){
					$o = array(
							'version'=>$this->version,
						);
				$this->update_option($o);
			}


		}

		function uninstall(){
			global $wpdb;
			foreach($this->db as $table)
				$wpdb->query(  "DROP TABLE {$table}" );
			delete_option($this->option_name);
		}


		function get_option($name=''){

			if(empty($this->options)){

				$options = get_option($this->option_name);

			}else{

				$options = $this->options;
			}
			if(!$options) return false;
			if($name)
				return $options[$name];
			return $options;
		}

		function update_option($ops){

			if(is_array($ops)){

				$options = $this->get_option();

				foreach($ops as $key => $value){

					$options[$key] = $value;

				}
				update_option($this->option_name,$options);
				$this->options = $options;
			}


		}

		function error_log($msg){
			$this->error .= "<p style='color:red;'>{$msg}</p>";
			return false;
		}

		function print_error(){
			echo $this->error;
		}


		function print_categories(){
			$categories = get_categories( 'hide_empty=0' );

			$results = '<select name="category">';

			foreach ( $categories as $cat ) {

				$option = "<option value='". $cat->cat_ID . "'>";

				$option .= $cat->cat_name;

				$option .= '</option>';

				$results .=$option;
			}

			$results .= '</select>';
			echo $results;

		}

		function insert_post($content,$category='',$time=''){
			$post = preg_split( '/(\r\n|\r|\n)/', $content );
			$post_title = trim( $post[0] );
			$post_content = trim( implode( "\n" , array_slice( $post, 1 ) ) );
			if ( !$post_title || !$post_content )
				return false;
			return wp_insert_post(
					array(
						'post_title'=>$post_title,
						'post_content'=>$post_content,
						'post_category'=>(array)$category,
						'post_date'=>date('Y-m-d H:i:s',$time),
						'post_status' => 'publish'
						)
				);
		}

		function process_zip($zip){

			$list = $zip->getList();
			shuffle( $list );
			$error = false;
			$timestamp = $_POST['start_from']?strtotime($_POST['start_from']):current_time('timestamp');
			$posting_interval = absint($_POST['posting_interval']);

			foreach ( $list as $filename => $b ) {

				if('txt' != strtolower(pathinfo($b['file_name'], PATHINFO_EXTENSION))){
					$this->error_log($b['file_name'].' is not a txt file!');
					$error = true;
					continue;
				}

				$content = $zip->unzip( $b['file_name'] );


				if ( !$content ) {
					$this->error_log($b['file_name'].' is empty');
					$error = true;
					continue;
				}

				$id = $this->insert_post( $content, $_POST['category'], $timestamp );

				if ( $id ) {
					if($posting_interval)
						$timestamp = $timestamp + ($posting_interval*HOUR_IN_SECONDS) + mt_rand(1,HOUR_IN_SECONDS);
				}else {
					$this->error_log("Fail to post {$b['file_name']}");
					$error = true;
				}

			}

			return !$error;

		}

		function handle_upload(){
			if(!$_FILES['zip'] )
				return $this->error_log('Please upload zip file!');
			if( $_FILES['zip']['error'] != 0 )
				return $this->error_log('Failed to upload zip file!');

			set_time_limit( 0 );
			include_once($this->lib_path.'/dUnzip2.inc.php');
			$zipfilename = $_FILES['zip']['tmp_name'];
			$zip = new dUnzip2( $zipfilename );
			$zip->debug = false;
			$this->make_iframe_allowed_tags();
			return $this->process_zip($zip);
		}

		function settings_page(){
			if(wp_verify_nonce( $_POST['txtaspost_settings_page'], 'txtaspost_settings_page' )){

				if($this->handle_upload())
					$this->redirect_to_current_page();
			}
			@include($this->plugin_path.'/txtaspost_settings_page.php');
		}



		function redirect_to_current_page(){

			$this->redirect_to_page(admin_url('admin.php?page='.$_REQUEST['page'].'&success'));
		}

		function redirect_to_page($redir){
			echo "<meta http-equiv='refresh' content='0;url={$redir}' />";
			exit;
		}

	}


}

if(!isset($TXTAsPost)){
		$TXTAsPost = new TXTAsPost();
}
?>
