<?php

/* 
 * Plugin Name:   TXTAsPost
 * Version:       2.0
 * Plugin URI:    http://www.wordpressplugindeveloper.com/projects
 * Description:   Import .txt as post in bulk
 * Author:        Jesse
 * Author URI:    http://www.wordpressplugindeveloper.com/projects
 */

require(dirname(__FILE__) . "/dUnzip2.inc.php");
class TXTAsPost {
		
		/*log*/
		var $log='';

		/*time interval retrieving from user input*/
		var $interval='';
		
		/*category retrieving from user input*/
		var $post_category='';

		var $notify = false;
		function __construct(){

			add_action('admin_menu', array(&$this,'txtaspost_menu_setup'));
		}

		function txtaspost_menu_setup() {
			add_options_page('TxtAsPost Settings', 'TXT As Post', 10, __FILE__, array(&$this,'txtaspost_menu'));
			if(wp_verify_nonce( $_POST['txtaspost_menu'], 'txtaspost_menu' )){
				if (isset($_FILES["zip"])) {
					if (is_uploaded_file($_FILES['zip']['tmp_name'])) {
						$this->txtaspost_process();
					}
					
				}
			}
		 
		}
		function print_categories($id=''){
	
			$categories=  get_categories('hide_empty=0');
			
			$results='<select name="txtaspost_category">';
			
			foreach ($categories as $cat) {

				$option = "<option value='". $cat->cat_ID . "'";

				if($id == $cat->cat_ID )
					$option .= " selected='selected' ";

				$option .= '>';

				$option .= $cat->cat_name;

				$option .= '</option>';

				$results.=$option;
			}
			
			$results .= '</select>'; 
			   
			return $results;
		}
		
		function txtaspost_process() {
		   
			set_time_limit(0);
			$zipfilename = $_FILES['zip']['tmp_name'];
			$zip = new dUnzip2($zipfilename);
			$zip->debug = false;
			$list = $zip->getList(); 
			shuffle($list);
			$this->interval=absint($_POST['time_interval']);
			$this->post_category = array($_POST['txtaspost_category']);

			$tm = ( $this->getLatestTime() + $this->interval*3600  ) ;
			foreach($list as $filename => $b) {
				
			      	$content = $zip->unzip($b['file_name']);
				if(!$content){
					$this->log .= "<p>{$b['file_name']} is empty</p>";
					continue;
				}
				$id=$this->process_content($content,$tm);
			       	if($id){
					      $tm += strtotime($id->post_date) + $this->interval*3600;
				}else{
					$this->log .= "<p>Fail to post {$b['file_name']} </p>";		
				}

		    	}
		    	$this->notify = true;
		}
		function process_content($content,$post_date){
	
			global $wpdb;
	
			$post = preg_split( '/(\r\n|\r|\n)/', $content );
	
		   	$post_title = trim($post[0]);
			$post_content = trim(implode ( "\n" , array_slice( $post,1 ) ));
		   	if (empty($post_title)||empty($post_content)) { return false; }

			$id=$this->txtaspost_post($post_title,$post_content,$post_date);
	
			return $id;

		}
		function txtaspost_post($post_title,$post_content,$post_date) {
			global $wpdb;
                        $post_date = $post_date + get_option('gmt_offset')*3600;
			$id = wp_insert_post(array(
			      "post_title" => $post_title ,
			      "post_content" => $post_content,
			      "post_category" => $this->post_category,
			      "post_status" => "publish",			
			      "post_author" => 1,
			      "post_date" => date("Y-m-d H:i:s", $post_date)
			) );

			return $id;
		}
		function getLatestTime() {
			global $wpdb;
		
			$post = $wpdb->get_results( "SELECT post_date_gmt FROM $wpdb->posts where post_status ='publish' OR post_status='future' ORDER BY post_date_gmt DESC limit 0,1" );
			$tm=strtotime($post[0]->post_date_gmt);
	
			// strtotime will return false if no post found
			if(false == $tm)
				$tm = time();
			return $tm;
		}
	
		function txtaspost_menu() {
			if( $this->notify ){
				echo	'<div id="message" class="updated fade"><p><b>Done!</b></p></div>';
			}
		   ?>
		   <div class="wrap">
		      <h2>TXT As post</h2>
		      <h3>If you need a customized version, please <a href="http://www.wordpressplugindeveloper.com/get-a-quote">get a quote here</a>. Note: it is not free.</h3>
		     <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" enctype="multipart/form-data">
		     <p><?php wp_nonce_field( 'txtaspost_menu', 'txtaspost_menu' );?> </p>

		     <p>Upload Zip: <input type="file" name="zip" /> &nbsp;Maximum file size <?php echo ini_get('upload_max_filesize');?></p>
		     <p>TXT format: <b>First line must be title and the rest should be content!</b>	</p>
		     <p>Posting Interval: <input type="text" size="2" name="time_interval" value="8"/>(Hours)</p>
		     <p>Example:</p>
		     <p style="color:red">if you already have many posts and the lastest post called A whose published date is 01:00. You want to use TxtAsPost Mod to upload two <b>zipped</b> articles B and C. Then B will be published at 09:00 and C will be 17:00 if the posting interval is 8 hours.</p>
		     <p>If no post exists, it'll be based on current server time. You can also enter 0 in posting interval field and it'll be published at the same time as post A.</p>
		     <p></p>
		     
		     <p>Category: <?php echo $this->print_categories();?></p>
		     <p><input type="submit" class="button" value="Upload" /></p>
		     <p>Error Log:</p>
		     <div id="log" style="color:red;"><?php echo $this->log;?></div>
		  </form>
		   </div>
		   <?php
		}

}
$txtaspost = & new TXTAsPost();

?>
