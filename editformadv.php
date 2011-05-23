<?php
global $fmdb;
global $fm_display;
global $fm_templates;

$form = null;
if($_REQUEST['id']!="")
	$form = $fmdb->getForm($_REQUEST['id']);

/////////////////////////////////////////////////////////////////////////////////////
// Process settings changes

if(isset($_POST['submit-form-settings'])){

}

/////////////////////////////////////////////////////////////////////////////////////
$fm_globalSettings = $fmdb->getGlobalSettings();

?>

<form name="fm-main-form" id="fm-main-form" action="" method="post">
<input type="hidden" value="1" name="message" id="message-post" />

<div class="wrap">
<div id="icon-edit-pages" class="icon32"></div>
<h2><?php echo $form['title'];?> - Advanced</h2>

<a class="preview button" href="<?php echo get_admin_url(null, 'admin.php')."?page=fm-edit-form&id=".$form['ID'];?>" >Edit Form</a>

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

<h3>Templates</h3>
<table class="form-table">
<?php 
//helper_checkbox_field('email_admin', "Administrator (".get_option('admin_email').")", ($fm_globalSettings['email_admin'] == "YES")); 
//helper_text_field('required_msg', "Required Item Message", htmlspecialchars($fm_globalSettings['required_msg']), "This is displayed when a user fails to input a required item.  Include '%s' in the message where you would like the item's label to appear.");
//helper_option_field('recaptcha_theme', "Color Scheme", array('red' => "Red", 'white' => "White", 'blackglass' => "Black", 'clean' => "Clean"), $fm_globalSettings['recaptcha_theme']);
?>
</table>

</div>

<p class="submit"><input type="submit" name="submit-form-settings" id="submit" class="button-primary" value="Save Changes"  /></p>
</form>