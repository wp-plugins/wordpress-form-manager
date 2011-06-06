<?php
/* translators: the following are from the form's advanced section */

global $fmdb;
global $fm_display;
global $fm_templates;
global $fm_form_behavior_types;

global $fm_DEBUG;
global $fm_MEMBERS_EXISTS;

/////////////////////////////////////////////////////////////////////////////////////
// Process settings changes

if(isset($_POST['submit-form-settings'])){
	$form = $fmdb->getForm($_POST['fm-form-id']);
	
	$formInfo = array();	
	
	$formInfo['items'] = $form['items'];
	foreach($form['items'] as $index => $item){
		$formInfo['items'][$index]['nickname'] = sanitize_title($_POST[$item['unique_name'].'-nickname']);		
	}
	$fmdb->updateForm($_POST['fm-form-id'], $formInfo);
}

/////////////////////////////////////////////////////////////////////////////////////

$form = null;
if($_REQUEST['id']!="")
	$form = $fmdb->getForm($_REQUEST['id']);
	
/////////////////////////////////////////////////////////////////////////////////////

$fm_globalSettings = $fmdb->getGlobalSettings();

?>

<form name="fm-main-form" id="fm-main-form" action="" method="post">
<input type="hidden" value="1" name="message" id="message-post" />
<input type="hidden" value="<?php echo $form['ID'];?>" name="fm-form-id" />

<div class="wrap" style="padding-top:15px;">

<div style="float:right;">
<input type="submit" name="submit-form-settings" id="submit" class="button-primary" value="<?php _e("Save Changes", 'wordpress-form-manager');?>"  />&nbsp;&nbsp;

</div>

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

<table>
<tr><td colspan="2"><span class="description"><?php _e("Giving a nickname to form items makes it easier to access their information within custom e-mail notifications and templates", 'wordpress-form-manager');?></span></td></tr>
</table>
<br />
<table class="form-table">
<tr><th><strong><?php _e("Item Label", 'wordpress-form-manager');?></strong></th><th><strong><?php _e("Nickname", 'wordpress-form-manager');?></strong></th></tr>
<?php foreach($form['items'] as $item){
	if($item['type'] != 'separator' && $item['type'] != 'note' && $item['type'] != 'recaptcha')
		helper_text_field($item['unique_name'].'-nickname', $item['label'], $item['nickname']);
} ?>
</table>

<p class="submit"><input type="submit" name="submit-form-settings" id="submit" class="button-primary" value="<?php _e("Save Changes", 'wordpress-form-manager');?>"  /></p>

</div>

</form>