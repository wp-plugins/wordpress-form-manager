<?php
global $fmdb;
global $fm_display;
global $fm_templates;
global $fm_form_behavior_types;

global $fm_DEBUG;


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
	
	$formInfo['items'] = $form['items'];
	foreach($form['items'] as $index => $item){
		$formInfo['items'][$index]['nickname'] = sanitize_title($_POST[$item['unique_name'].'-nickname']);		
	}
	$fmdb->updateForm($_POST['fm-form-id'], $formInfo);
}


// Process an updated form definition
if($fm_DEBUG && isset($_POST['form-definition'])){	
	$formDef = new fm_form_definition_class(); 
	
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
<h2><?php echo $form['title'];?> - Advanced</h2>

<div style="float:right;">
<input type="submit" name="submit-form-settings" id="submit" class="button-primary" value="Save Changes"  />&nbsp;&nbsp;
<a class="preview button" href="<?php echo get_admin_url(null, 'admin.php')."?page=fm-edit-form&id=".$form['ID'];?>" >Edit Form</a>
</div>

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

<h3>Behavior</h3>
<table class="form-table">
<?php
$behaviorList = array();
foreach($fm_form_behavior_types as $desc => $val)
	$behaviorList[$val] = $desc;
helper_option_field('behaviors', "Behavior Type", $behaviorList, $form['behaviors'], "Behavior types other than 'Default' require a registered user");
?>
</table>

<h3>Templates</h3>
<table class="form-table">
<?php 
helper_option_field('form_template', "Form Display", array_merge(array( '' => "(use default)"), $templateList['form']), $form['form_template']);
helper_option_field('email_template', "E-Mail Notifications", array_merge(array( '' => "(use default)"), $templateList['email']), $form['email_template']);
helper_option_field('summary_template', "Data Summary", array_merge(array( '' => "(use default)"), $templateList['summary']), $form['summary_template']);
?>
</table>

<h3>Custom E-Mail Notifications</h3>
<table>
<tr><td width="300px">Use custom e-mail notifications</td><td align="left"><input type="checkbox" name="use_advanced_email" <?php echo ($form['use_advanced_email'] == 1 ? "checked=\"checked\"" : ""); ?> ? /></td></tr>
<tr><td colspan="2"><span class="description">This will override the 'E-Mail Notifications' settings in both the main editor and the plugin settings page with the information entered below</span></td></tr>
</table>
<textarea name="advanced_email" rows="15" style="width:80%" ><?php echo $form['advanced_email']; ?></textarea>

<h3>Form Item Nicknames</h3>
<table>
<tr><td colspan="2"><span class="description">Giving a nickname to form items makes it easier to access their information within custom e-mail notifications and templates</span></td></tr>
</table>
<br />
<table class="form-table">
<tr><th><strong>Item Label</strong></th><th><strong>Nickname</strong></th></tr>
<?php foreach($form['items'] as $item){
	if($item['type'] != 'separator' && $item['type'] != 'note' && $item['type'] != 'recaptcha')
		helper_text_field($item['unique_name'].'-nickname', $item['label'], $item['nickname']);
} ?>
</table>

<p class="submit"><input type="submit" name="submit-form-settings" id="submit" class="button-primary" value="Save Changes"  /></p>

</div>

</form>

<?php if($fm_DEBUG): ?>
<h3>Edit Form Definition:</h3>
<form name="fm-definition-form" action="" method="post">
	<input type="hidden" value="<?php echo $form['ID'];?>" name="fm-form-id" />
	<textarea name="form-definition" rows="20" cols="80"><?php echo $formDef->printFormAtts($form['items']); ?></textarea>
	<p class="submit"><input type="submit" name="submit-form-definition" class="button-primary" value="Update Form" /></p>
</form>
<?php endif; ?>
