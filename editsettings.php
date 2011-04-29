<?php 
global $fmdb;
global $fm_globalSettings;

/////////////////////////////////////////////////////////////////////////////////////
// Process settings changes

if(isset($_POST['submit-settings'])){
	$fmdb->setGlobalSetting('title', $_POST['title']);
	$fmdb->setGlobalSetting('submitted_msg', $_POST['submitted_msg']);
	$fmdb->setGlobalSetting('required_msg', $_POST['required_msg']);
	$fmdb->setGlobalSetting('recaptcha_public', $_POST['recaptcha_public']);
	$fmdb->setGlobalSetting('recaptcha_private', $_POST['recaptcha_private']);
	$fmdb->setGlobalSetting('email_admin', $_POST['email_admin'] == "on" ? "YES" : "");
	$fmdb->setGlobalSetting('email_reg_users', $_POST['email_reg_users'] == "on" ? "YES" : "");
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

<h3>E-Mail Notifications</h3>
<table class="form-table">
<?php helper_checkbox_field('email_admin', "Administrator (".get_option('admin_email').")", ($fm_globalSettings['email_admin'] == "YES")); ?>
<?php helper_checkbox_field('email_reg_users', "Registered Users ", ($fm_globalSettings['email_reg_users'] == "YES"), "A confirmation e-mail will be sent to a registered user only when they submit a form"); ?>
</table>

<h3>Default Form Settings</h3>
<table class="form-table">
<?php helper_text_field('title', "Form Title", htmlspecialchars($fm_globalSettings['title'])); ?>
<?php helper_text_field('submitted_msg', "Submit Acknowledgment", htmlspecialchars($fm_globalSettings['submitted_msg'])); ?>
<?php helper_text_field('required_msg', "Required Item Message", htmlspecialchars($fm_globalSettings['required_msg']), "This is displayed when a user fails to input a required item.  Include '%s' in the message where you would like the item's label to appear."); ?>
</table>

<h3>reCAPTCHA API Keys</h3>
<span class="description">API Keys for reCAPTCHA can be acquired (for free) by visiting <a target="_blank" href="https://www.google.com/recaptcha">www.google.com/recaptcha</a>.</span>
<table class="form-table">
<?php helper_text_field('recaptcha_public', "reCAPTCHA Public Key", htmlspecialchars($fm_globalSettings['recaptcha_public'])); ?>
<?php helper_text_field('recaptcha_private', "reCAPTCHA Private Key", htmlspecialchars($fm_globalSettings['recaptcha_private'])); ?>
</table>


</div>

<p class="submit"><input type="submit" name="submit-settings" id="submit" class="button-primary" value="Save Changes"  /></p>
</form>
