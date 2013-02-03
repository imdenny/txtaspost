<div class="wrap">
<?php screen_icon('options-general');?>
<?php
if(isset($_GET['success'])){?>
    <div id="message" class="updated">
        <p><b>Done!</b></p>
    </div>
<?php  }?>
<h2>Settings Page</h2>

<form method="post" enctype="multipart/form-data">
<?php wp_nonce_field( 'txtaspost_settings_page', 'txtaspost_settings_page' ); ?>
<p><strong>Zip File: </strong> <input type="file" name="zip" /></p>
<p class="howto">TXT supported only. First line should be post title while the rest is content</p>
<p class="howto"><strong>Importatnt Note: </strong> If the TXT is not in English, please make sure your TXT is saved in UTF-8.</p>
<p><strong>Posting Interval: </strong> <input size="2" type="text" name="posting_interval" value="8" /></p>
<p><strong>Start From: </strong><input type="text" id="start_from" name="start_from" value="" /> </p>
<p><strong>Category: </strong> <?php $this->print_categories();?></p>
<p><input type="submit" class="button-primary" value="Upload" /></p>
<?php $this->print_error();?>
</form>
<p class="howto">This plugin is created by <a href="http://www.wordpressplugindeveloper.com/">WordpressPluginDeveloper.com</a></p>
</div>
