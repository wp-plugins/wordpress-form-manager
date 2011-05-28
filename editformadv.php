<?php
/* translators: the following are from the form's advanced section */

global $fmdb;
global $fm_display;
global $fm_templates;
global $fm_form_behavior_types;

global $fm_DEBUG;
global $fm_MEMBERS_EXISTS;

include 'formdefinition.php';

/////////////////////////////////////////////////////////////////////////////////////
// Process settings changes

if(isset($_POST['submit-form-settings'])){
	$form = $fmdb->getForm($_POST['fm-form-id']);
	
	$formInfo = array();
	
	$formInfo['behaviors'] = $_POST['behaviors'];
	
	$formInfo['form_template'] = $_POST['form_template'];
	$formInfo['email_template'] = $_POST['email_template'];
	$formInfo['summary_template'] = $_POST['summary_template'];
	$formInfo['use_advanced_email'] = ($_POST['use_advanced_email']=="on"?1:0);
	$formInfo['advanced_email'] = $_POST['advanced_email'];
	$formInfo['publish_post'] = ($_POST['publish_post']=="on"?1:0);
	$formInfo['publish_post_category'] = $_POST['publish_post_category'];
	$formInfo['publish_post_title'] = $_POST['publish_post_title'];
	
	$formInfo['items'] = $form['items'];
	foreach($form['items'] as $index => $item){
		$formInfo['items'][$index]['nickname'] = sanitize_title($_POST[$item['unique_name'].'-nickname']);		
	}
	$fmdb->updateForm($_POST['fm-form-id'], $formInfo);
}


// Process an updated form definition
if($fm_DEBUG) $formDef = new fm_form_definition_class(); 
if($fm_DEBUG && isset($_POST['form-definition'])){	
	
	
	$formInfo = $formDef->createFormInfo($_POST['form-definition']);	
	$fmdb->updateForm($_POST['fm-form-id'], $formInfo);
} 

/////////////////////////////////////////////////////////////////////////////////////

$form = null;
if($_REQUEST['id']!="")
	$form = $fmdb->getForm($_REQUEST['id']);
	
$formTemplateFile = $form['form_template'];
	if($formTemplateFile == '') $formTemplateFile = $fmdb->getGlobalSetting('template_form');
	if($formTemplateFile == '') $formTemplateFile = get_option('fm-default-form-template');

$formTemplate = $fm_templates->getTemplateAttributes($formTemplateFile);
$templateList = $fm_templates->getTemplateFilesByType();

/////////////////////////////////////////////////////////////////////////////////////

$fm_globalSettings = $fmdb->getGlobalSettings();

?>

<form name="fm-main-form" id="fm-main-form" action="" method="post">
<input type="hidden" value="1" name="message" id="message-post" />
<input type="hidden" value="<?php echo $form['ID'];?>" name="fm-form-id" />

<div class="wrap">
<div id="icon-edit-pages" class="icon32"></div>
<?php /* translators: This specifies the 'advanced' settings pages */ ?>
<h2><?php echo $form['title'];?> - <?php _e("Advanced", 'wordpress-form-manager');?></h2>

<div style="float:right;">
<input type="submit" name="submit-form-settings" id="submit" class="button-primary" value="<?php _e("Save Changes", 'wordpress-form-manager');?>"  />&nbsp;&nbsp;
<?php if(!$fm_MEMBERS_EXISTS || current_user_can('form_manager_forms')): ?>
<a class="preview button" href="<?php echo get_admin_url(null, 'admin.php')."?page=fm-edit-form&id=".$form['ID'];?>" ><?php _e("Edit Form", 'wordpress-form-manager');?></a>
<?php endif; ?>
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

<h3><?php _e("Behavior", 'wordpress-form-manager');?></h3>
<table class="form-table">
<?php
$behaviorList = array();
foreach($fm_form_behavior_types as $desc => $val)
	$behaviorList[$val] = $desc;
helper_option_field('behaviors', __("Behavior Type", 'wordpress-form-manager'), $behaviorList, $form['behaviors'], __("Behavior types other than 'Default' require a registered user", 'wordpress-form-manager'));
?>
</table>

<h3>Templates</h3>
<table class="form-table">
<?php 
/* translators: the following apply to the different kinds of templates */
helper_option_field('form_template', __("Form Display", 'wordpress-form-manager'), array_merge(array( '' => __("(use default)", 'wordpress-form-manager')), $templateList['form']), $form['form_template']);
helper_option_field('email_template', __("E-Mail Notifications", 'wordpress-form-manager'), array_merge(array( '' => __("(use default)", 'wordpress-form-manager')), $templateList['email']), $form['email_template']);
helper_option_field('summary_template', __("Data Summary", 'wordpress-form-manager'), array_merge(array( '' => __("(use default)", 'wordpress-form-manager')), $templateList['summary']), $form['summary_template']);
?>
</table>

<h3><?php _e("Custom E-Mail Notifications", 'wordpress-form-manager');?></h3>
<table>
<tr><td width="350px"><?php _e("Use custom e-mail notifications", 'wordpress-form-manager');?></td><td align="left"><input type="checkbox" name="use_advanced_email" <?php echo ($form['use_advanced_email'] == 1 ? "checked=\"checked\"" : ""); ?> ? /></td></tr>
<tr><td colspan="2"><span class="description"><?php _e("This will override the 'E-Mail Notifications' settings in both the main editor and the plugin settings page with the information entered below", 'wordpress-form-manager');?></span></td></tr>
</table>
<textarea name="advanced_email" rows="15" style="width:80%" ><?php echo $form['advanced_email']; ?></textarea>

<h3><?php _e("Form Item Nicknames", 'wordpress-form-manager');?></h3>
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

<h3><?php _e("Publish Submitted Data", 'wordpress-form-manager');?></h3>
<table class="form-table">
<?php helper_checkbox_field('publish_post', __("Publish submissions as posts", 'wordpress-form-manager'), ($form['publish_post'] == 1)); ?> 
<tr><th scope="row"><label><?php _e("Post category", 'wordpress-form-manager'); ?></label></th><td><?php wp_dropdown_categories(array('hide_empty' => 0, 'name' => 'publish_post_category', 'hierarchical' => true, 'selected' => $form['publish_post_category'])); ?></td></tr>
<?php helper_text_field('publish_post_title', __("Post title", 'wordpress-form-manager'), htmlspecialchars($form['publish_post_title']), __("Include '%s' where you would like the form title to appear", 'wordpress-form-manager')); ?>
</table>

<p class="submit"><input type="submit" name="submit-form-settings" id="submit" class="button-primary" value="<?php _e("Save Changes", 'wordpress-form-manager');?>"  /></p>

</div>

</form>

<?php if($fm_DEBUG): ?>
<h3><?php _e("Edit Form Definition:", 'wordpress-form-manager');?></h3>
<form name="fm-definition-form" action="" method="post">
	<input type="hidden" value="<?php echo $form['ID'];?>" name="fm-form-id" />
	<textarea name="form-definition" rows="20" cols="80"><?php echo $formDef->printFormAtts($form['items']); ?></textarea>
	<p class="submit"><input type="submit" name="submit-form-definition" class="button-primary" value="<?php _e("Update Form", 'wordpress-form-manager');?>" /></p>
</form>
<?php endif; ?>
