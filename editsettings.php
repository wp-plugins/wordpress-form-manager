<?php 
global $fmdb;
global $fm_globalSettings;

function helper_text_field($id, $label, $desc = ""){
	global $fm_globalSettings;
	?>
<tr valign="top">
	<th scope="row"><label for="<?php echo $id;?>"><?php echo $label;?></label></th>
	<td><input name="<?php echo $id;?>" type="text" id="<?php echo $id;?>"  value="<?php echo htmlspecialchars($fm_globalSettings[$id]);?>" class="regular-text" />
	<span class="description"><?php echo $desc;?></span>
	</td>
</tr>
<?php
}

/////////////////////////////////////////////////////////////////////////////////////
// Process settings changes

if(isset($_POST['submit-settings'])){
	$fmdb->setGlobalSetting('title', $_POST['title']);
	$fmdb->setGlobalSetting('submitted_msg', $_POST['submitted_msg']);
	$fmdb->setGlobalSetting('required_msg', $_POST['required_msg']);
	$fmdb->setGlobalSetting('recaptcha_public', $_POST['recaptcha_public']);
	$fmdb->setGlobalSetting('recaptcha_private', $_POST['recaptcha_private']);	
}


/////////////////////////////////////////////////////////////////////////////////////
$fm_globalSettings = $fmdb->getGlobalSettings();

?>
<form name="fm-main-form" id="fm-main-form" action="" method="post">
<input type="hidden" value="1" name="message" id="message-post" />

<div class="wrap">
<div id="icon-edit-pages" class="icon32"></div>
<h2>Form Manager Settings</h2>

	<div id="message-container"><?php 
	if(isset($_POST['message']))
		switch($_POST['message']){
			case 1: ?><div id="message-success" class="updated"><p><strong>Settings Saved. </strong></p></div><?php break;
			case 2: ?><div id="message-error" class="error"><p>Save failed. </p></div><?php break;
			default: ?>
				<?php if(isset($_POST['message']) && trim($_POST['message']) != ""): ?>
				<div id="message-error" class="error"><p><?php echo stripslashes($_POST['message']);?></p></div>
				<?php endif; ?>
			<?php
		} 
	?></div>


<h3>Default Form Settings</h3>
<table class="form-table">
<?php helper_text_field('title', "Form Title"); ?>
<?php helper_text_field('submitted_msg', "Submit Acknowledgment"); ?>
<?php helper_text_field('required_msg', "Required Item Message", "This is displayed when a user fails to input a required item.  Include '%s' in the message where you would like the item's label to appear."); ?>
</table>

<h3>reCAPTCHA API Keys</h3>
<span class="description">API Keys for reCAPTCHA can be acquired (for free) by visiting <a target="_blank" href="https://www.google.com/recaptcha">www.google.com/recaptcha</a>.</span>
<table class="form-table">
<?php helper_text_field('recaptcha_public', "reCAPTCHA Public Key"); ?>
<?php helper_text_field('recaptcha_private', "reCAPTCHA Private Key"); ?>
</table>


</div>

<p class="submit"><input type="submit" name="submit-settings" id="submit" class="button-primary" value="Save Changes"  /></p>
</form>
