<?php 
/* translators: the following are from the settings page (for the plugin) */

global $fmdb;
global $fm_globalSettings;
global $fm_templates;

/////////////////////////////////////////////////////////////////////////////////////
// Process settings changes

if(isset($_POST['submit-settings'])){
	$fmdb->setGlobalSetting('title', $_POST['title']);
	$fmdb->setGlobalSetting('submitted_msg', $_POST['submitted_msg']);
	$fmdb->setGlobalSetting('required_msg', $_POST['required_msg']);
	$fmdb->setGlobalSetting('recaptcha_public', $_POST['recaptcha_public']);
	$fmdb->setGlobalSetting('recaptcha_private', $_POST['recaptcha_private']);
	$fmdb->setGlobalSetting('recaptcha_theme', $_POST['recaptcha_theme']);
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
<h2><?php _e("Form Manager Settings", 'wordpress-form-manager');?></h2>

<a class="preview button" href="<?php echo get_admin_url(null, 'admin.php')."?page=fm-global-settings-advanced";?>" ><?php _e("Advanced", 'wordpress-form-manager');?></a>

	<div id="message-container"><?php 
	if(isset($_POST['message']))
		switch($_POST['message']){
			case 1: ?><div id="message-success" class="updated"><p><strong><?php _e("Settings Saved.", 'wordpress-form-manager');?> </strong></p></div><?php break;
			case 2: ?><div id="message-error" class="error"><p><?php _e("Save failed.", 'wordpress-form-manager');?> </p></div><?php break;
			default: ?>
				<?php if(isset($_POST['message']) && trim($_POST['message']) != ""): ?>
				<div id="message-error" class="error"><p><?php echo stripslashes($_POST['message']);?></p></div>
				<?php endif; ?>
			<?php
		} 
	?></div>

<h3>Global E-Mail Notifications</h3>
<table class="form-table">
<tr><td colspan="2"><?php _e("These settings will be applied to every form you create.", 'wordpress-form-manager');?></td></tr>
<?php helper_checkbox_field('email_admin', __("Send to Administrator", 'wordpress-form-manager')." (".get_option('admin_email').")", ($fm_globalSettings['email_admin'] == "YES")); ?>
<?php helper_checkbox_field('email_reg_users', __("Registered Users", 'wordpress-form-manager'), ($fm_globalSettings['email_reg_users'] == "YES"), __("A confirmation e-mail will be sent to a registered user only when they submit a form", 'wordpress-form-manager')); ?>
</table>

<h3>Default Form Settings</h3>
<table class="form-table">
<?php helper_text_field('title', __("Form Title", 'wordpress-form-manager'), htmlspecialchars($fm_globalSettings['title'])); ?>
<?php helper_text_field('submitted_msg', __("Submit Acknowledgment", 'wordpress-form-manager'), htmlspecialchars($fm_globalSettings['submitted_msg'])); ?>
<?php helper_text_field('required_msg', __("Required Item Message", 'wordpress-form-manager'), htmlspecialchars($fm_globalSettings['required_msg']), __("This is displayed when a user fails to input a required item.  Include '%s' in the message where you would like the item's label to appear.", 'wordpress-form-manager')); ?>
</table>

<h3>reCAPTCHA Settings</h3>
<span class="description">API Keys for reCAPTCHA can be acquired (for free) by visiting <a target="_blank" href="https://www.google.com/recaptcha">www.google.com/recaptcha</a>.</span>
<table class="form-table">
<?php helper_text_field('recaptcha_public', __("reCAPTCHA Public Key", 'wordpress-form-manager'), htmlspecialchars($fm_globalSettings['recaptcha_public'])); ?>
<?php helper_text_field('recaptcha_private', __("reCAPTCHA Private Key", 'wordpress-form-manager'), htmlspecialchars($fm_globalSettings['recaptcha_private'])); ?>
<?php helper_option_field('recaptcha_theme', __("Color Scheme", 'wordpress-form-manager'), array('red' => __("Red", 'wordpress-form-manager'), 'white' => __("White", 'wordpress-form-manager'), 'blackglass' => __("Black", 'wordpress-form-manager'), 'clean' => __("Clean", 'wordpress-form-manager')), $fm_globalSettings['recaptcha_theme']); ?>
</table>

</div>

<p class="submit"><input type="submit" name="submit-settings" id="submit" class="button-primary" value="<?php _e("Save Changes", 'wordpress-form-manager');?>"  /></p>
</form>
