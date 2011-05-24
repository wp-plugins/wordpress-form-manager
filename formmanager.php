<?php
/*
Plugin Name: Form Manager
Plugin URI: http://www.campbellhoffman.com/form-manager/
Description: Create custom forms; download entered data in .csv format; validation, required fields, custom acknowledgments;
Version: 1.4.9
Author: Campbell Hoffman
Author URI: http://www.campbellhoffman.com/
License: GPL2

  Copyright 2011 Campbell Hoffman

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; version 2 of the License (GPL v2) only.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

global $fm_currentVersion;
$fm_currentVersion = "1.4.9";

global $fm_DEBUG;
$fm_DEBUG = false;

/**************************************************************/
/******* HOUSEKEEPING *****************************************/

//make sure the page wasn't accessed directly
if ( ! function_exists( 'add_action' ) ) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();	
}
// only WP 3.0+
if ( version_compare( get_bloginfo( 'version' ), '3.0.0', '<' ) ) 
	wp_die( 'Form Manager requires WordPress version 3.0 or higher' );
	
// only PHP 5.0+
if ( version_compare(PHP_VERSION, '5.0.0', '<') ) 
	wp_die( 'Form Manager requires PHP version 5.0 or higher' );

include 'helpers.php';

include 'db.php';
include 'display.php';
include 'template.php';
include 'email.php';

/**************************************************************/
/******* PLUGIN OPTIONS ***************************************/

if(get_option('fm-shortcode') === false) 
	update_option("fm-shortcode", "form");
update_option("fm-forms-table-name", "fm_forms");
update_option("fm-items-table-name", "fm_items");
update_option("fm-settings-table-name", "fm_settings");
update_option("fm-templates-table-name", "fm_templates");
update_option("fm-data-table-prefix", "fm_data");
update_option("fm-query-table-prefix", "fm_queries");
update_option("fm-default-form-template", "fm-form-default.php");
update_option("fm-default-summary-template", "fm-summary-default.php");
update_option("fm-temp-dir", "tmp");

global $wpdb;
global $fmdb;
global $fm_display;
global $fm_templates;

$fmdb = new fm_db_class($wpdb->prefix.get_option('fm-forms-table-name'),
					$wpdb->prefix.get_option('fm-items-table-name'),
					$wpdb->prefix.get_option('fm-settings-table-name'),
					$wpdb->prefix.get_option('fm-templates-table-name'),
					$wpdb->dbh
					);
$fm_display = new fm_display_class();
$fm_templates = new fm_template_manager();
				
/**************************************************************/
/******* DATABASE SETUP ***************************************/

function fm_install(){
	global $fmdb;
	global $fm_currentVersion;
	
	//from any version before 1.4.0; must be done before the old columns are removed
	$fmdb->convertAppearanceSettings();
	
	//initialize the database
	$fmdb->setupFormManager();

	// covers updates from 1.3.0 
	$q = "UPDATE `{$fmdb->formsTable}` SET `behaviors` = 'reg_user_only,display_summ,single_submission' WHERE `behaviors` = 'reg_user_only,no_dup'";
	$fmdb->query($q);
	$q = "UPDATE `{$fmdb->formsTable}` SET `behaviors` = 'reg_user_only,display_summ,edit' WHERE `behaviors` = 'reg_user_only,no_dup,edit'";
	$fmdb->query($q);
	
	// covers versions up to and including 1.3.10
	$fmdb->fixCollation();		
	
	$fmdb->updateDataTables();
		
	update_option('fm-version', $fm_currentVersion);			
}  
register_activation_hook(__FILE__,'fm_install');

//uninstall - delete the table(s). 
function fm_uninstall(){
	global $fmdb;	
	$fmdb->removeFormManager();
	
	delete_option('fm-shortcode');
	delete_option('fm-forms-table-name');
	delete_option('fm-items-table-name');
	delete_option('fm-settings-table-name');
	delete_option('fm-templates-table-name');
	delete_option('fm-data-table-prefix');
	delete_option('fm-query-table-prefix');
	delete_option('fm-default-form-template');
	delete_option('fm-default-summary-template');
	delete_option('fm-version');
	delete_option('fm-temp-dir');
}
register_uninstall_hook(__FILE__,'fm_uninstall');


/**************************************************************/
/******* HOUSEKEEPING *****************************************/

//delete .csv files on each login
add_action('wp_login', 'fm_cleanCSVData');
function fm_cleanCSVData(){
	$dirName = @dirname(__FILE__)."/".get_option("fm-temp-dir");
	$dir = @opendir($dirName);
	while($fname = @readdir($dir)) {
		if(file_exists(dirname(__FILE__)."/".get_option("fm-temp-dir")."/".$fname))
			@unlink($dirName."/".$fname);
	}
	@closedir($dir);
}

/**************************************************************/
/******* INIT, SCRIPTS & CSS **********************************/

add_action('admin_init', 'fm_adminInit');
function fm_adminInit(){
	global $fm_templates;
	
	wp_enqueue_script('scriptaculous');
	wp_enqueue_script('scriptaculous-dragdrop');
	
	wp_enqueue_script('form-manager-js', plugins_url('/js/scripts.js', __FILE__));	
	
	wp_register_style('form-manager-css', plugins_url('/css/style.css', __FILE__));
	wp_enqueue_style('form-manager-css');
	
	$fm_templates->initTemplates();
}

add_action('init', 'fm_userInit');
function fm_userInit(){
	global $fm_currentVersion;
	//update check, since the snarky wordpress dev changed the behavior of a function based on its english name, rather than its widely accepted usage.
	//"The perfect is the enemy of the good". 
	$ver = get_option('fm-version');
	if($ver != $fm_currentVersion){
		fm_install();
	}

	include 'settings.php';
	
	wp_enqueue_script('form-manager-js-helpers', plugins_url('/js/helpers.js', __FILE__));
	wp_enqueue_script('form-manager-js-validation', plugins_url('/js/validation.js', __FILE__));
	
	wp_register_style('form-manager-css', plugins_url('/css/style.css', __FILE__));
	wp_enqueue_style('form-manager-css');
}

add_action('wp_head', 'fm_userHead');
function fm_userHead(){
	global $fm_controls;
	foreach($fm_controls as $control){
		$control->showUserScripts();	
	}
}

/**************************************************************/
/******* ADMIN PAGES ******************************************/

add_action('admin_menu', 'fm_setupAdminMenu');
function fm_setupAdminMenu(){
	$pages[] = add_object_page("Forms", "Forms", "manage_options", "fm-admin-main", 'fm_showMainPage');
	$pages[] = add_submenu_page("fm-admin-main", "Edit", "Edit", "manage_options", "fm-edit-form", 'fm_showEditPage');
	$pages[] = add_submenu_page("fm-admin-main", "Data", "Data", "manage_options", "fm-form-data", 'fm_showDataPage');	
	
	//at some point, make this link go to a fresh form
	//$pages[] = add_submenu_page("fm-admin-main", "Add New", "Add New", "manage_options", "fm-add-new", 'fm_showMainPage');
	
	$pages[] = add_submenu_page("fm-admin-main", "Settings", "Settings", "manage_options", "fm-global-settings", 'fm_showSettingsPage');
	$pages[] = add_submenu_page("fm-admin-main", "Advanced Settings", "Advanced Settings", "manage_options", "fm-global-settings-advanced", 'fm_showSettingsAdvancedPage');
	
	$pages[] = add_submenu_page("fm-admin-main", "Edit Form - Advanced", "Edit Form - Advanced", "manage_options", "fm-edit-form-advanced", 'fm_showEditAdvancedPage');
	
	foreach($pages as $page)
		add_action('admin_head-'.$page, 'fm_adminHeadPluginOnly');
}

add_action('admin_head', 'fm_adminHead');
function fm_adminHead(){
	global $submenu;	
	
	//we don't actually want all the pages to show up in the menu, but having slugs for pages makes things easy
	//unset($submenu['fm-admin-main'][0]);
	unset($submenu['fm-admin-main'][1]); //Edit
	unset($submenu['fm-admin-main'][2]); //Data
	
	unset($submenu['fm-admin-main'][4]); //Advanced settings
	
	unset($submenu['fm-admin-main'][5]); //Edit Form Advanced
}

//only show this stuff when viewing a plugin page, since some of it is messy
function fm_adminHeadPluginOnly(){
	global $fm_controls;
	//show the control scripts
	fm_showControlScripts();
	foreach($fm_controls as $control){
		$control->showScripts();
	}
}

function fm_showEditPage(){	include 'editform.php'; }
function fm_showEditAdvancedPage(){	include 'editformadv.php'; }
function fm_showDataPage(){	include 'formdata.php'; }
function fm_showMainPage(){	include 'main.php'; }
function fm_showSettingsPage(){	include 'editsettings.php'; }
function fm_showSettingsAdvancedPage(){	include 'editsettingsadv.php'; }

/**************************************************************/
/******* AJAX *************************************************/

//form editor 'save' button
add_action('wp_ajax_fm_save_form', 'fm_saveFormAjax');
function fm_saveFormAjax(){
	global $fmdb;
	
	$formInfo = fm_saveHelperGatherFormInfo();
	
	//check if the shortcode is a duplicate
	$scID = $fmdb->getFormID($formInfo['shortcode']);
	if(!($scID == false || $scID == $_POST['id'] || trim($formInfo['shortcode']) == "")){
		//get the old shortcode
		$formInfo['shortcode'] = $fmdb->getFormShortcode($_POST['id']);			
		//save the rest of the form
		$fmdb->updateForm($_POST['id'], $formInfo);
		
		//now tell the user there was an error
		echo "Error: the shortcode '".$formInfo['shortcode']."' is already in use. (other changes were saved successfully)";
		die();
	}
			
	//no errors: save the form, return '1'
	$fmdb->updateForm($_POST['id'], $formInfo);
	
	echo "1";
		
	die();
}

function fm_saveHelperGatherFormInfo(){
	//collect the posted information
	$formInfo = array();
	$formInfo['title'] = $_POST['title'];
	$formInfo['labels_on_top'] = $_POST['labels_on_top'];
	$formInfo['submitted_msg'] = $_POST['submitted_msg'];
	$formInfo['submit_btn_text'] = $_POST['submit_btn_text'];
	$formInfo['show_title'] = ($_POST['show_title']=="true"?1:0);
	$formInfo['show_border'] = ($_POST['show_border']=="true"?1:0);
	$formInfo['shortcode'] = sanitize_title($_POST['shortcode']);
	$formInfo['label_width'] = $_POST['label_width'];
	$formInfo['required_msg'] = $_POST['required_msg'];
	$formInfo['template_values'] = $_POST['template_values'];	
	$formInfo['show_summary'] = ($_POST['show_summary']=="true"?1:0);
	$formInfo['email_user_field'] = $_POST['email_user_field'];
	
	//build the notification email list
	$emailList = explode(",", $_POST['email_list']);
	$valid = true;
	for($x=0;$x<sizeof($emailList);$x++){
		$emailList[$x] = trim($emailList[$x]);		
		if($emailList[$x] != "" && !preg_match("/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/", $emailList[$x])){
			$valid = false;
			$x = sizeof($emailList);
		}	
	}
		
	if($valid){
		$temp = array();
		foreach($emailList as $email)
			if($email != "") $temp[] = $email;
		$formInfo['email_list'] = implode(",", $temp);
	}
	else
		echo "Error: There was a problem with the notification e-mail list.  Other settings were updated.";
		
		
	//build the items list
	$formInfo['items'] = array();
	if(isset($_POST['items'])){
		foreach($_POST['items'] as $item){			
			if(!is_serialized($item['extra'])){ //if not a serialized array, hopefully a parseable php array definition..								
				$item['extra'] = stripslashes(stripslashes($item['extra'])); //both javascript and $_POST add slashes
				//make sure the code to be eval'ed is safe (otherwise this would be a serious security risk)
				if(is_valid_array_expr($item['extra']))				
					eval("\$newExtra = ".$item['extra'].";"); 				
				else{
					echo "Error: Save posted an invalid array expression. <br />";
					echo $item['extra'];
					die();
				}					
				$item['extra'] = $newExtra;
			}			
			$formInfo['items'][] = $item;			
		}
	}
	
	return $formInfo;
}

//insert a new form item
add_action('wp_ajax_fm_new_item', 'fm_newItemAjax');
function fm_newItemAjax(){
	global $fm_display;
	global $fmdb;
	
	$uniqueName = $fmdb->getUniqueItemID($_POST['type']);

	$str = "{".
		"'html':\"".addslashes(urlencode($fm_display->getEditorItem($uniqueName, $_POST['type'], null)))."\",".
		"'uniqueName':'".$uniqueName."'".
		"}";
	
	echo $str;
	
	die();
}

//Use the 'formelements' helpers
add_action('wp_ajax_fm_create_form_element', 'fm_createFormElement');
function fm_createFormElement(){
	//echo "<pre>".print_r($elem,true)."</pre>";
	echo fe_getElementHTML($_POST['elem']);
	die();
}

//Create a CSV file for download
add_action('wp_ajax_fm_create_csv', 'fm_createCSV');
function fm_createCSV(){
	global $fmdb;
	
	$fname = sanitize_title($_POST['title'])." (".date("m-y-d h-i-s").").csv";
	
	$fmdb->writeFormSubmissionDataCSV($_POST['id'], dirname(__FILE__)."/".get_option("fm-temp-dir")."/".$fname);
	
	echo plugins_url('/'.get_option("fm-temp-dir").'/',  __FILE__).$fname;
	
	die();
}

//Download an uploaded file
add_action('wp_ajax_fm_download_file', 'fm_downloadFile');
function fm_downloadFile(){
	global $fmdb;
	
	$tmpDir =  dirname(__FILE__)."/".get_option("fm-temp-dir")."/";
	
	$formID = $_POST['id'];
	$itemID = $_POST['itemid'];
	$timestamp = $_POST['timestamp'];
	$userName = $_POST['user'];
	
	$dataRow = $fmdb->getSubmission($formID, $timestamp, $userName, "`".$itemID."`");
	
	$fileInfo = unserialize($dataRow[$itemID]);	
	
	fm_createFileFromDB($fileInfo['filename'], $fileInfo, $tmpDir);
	
	echo plugins_url('/'.get_option("fm-temp-dir").'/', __FILE__).$fileInfo['filename'];		
	
	die();
}

add_action('wp_ajax_fm_download_all_files', 'fm_downloadAllFiles');
function fm_downloadAllFiles(){
	global $fmdb;
	
	$tmpDir =  dirname(__FILE__)."/".get_option("fm-temp-dir")."/";
	
	$formID = $_POST['id'];	
	$itemID = $_POST['itemid'];
	
	$formInfo = $fmdb->getForm($formID);
	foreach($formInfo['items'] as $item)
		if($item['unique_name'] == $itemID)
			$itemLabel = $item['label'];
			
	$formData = $fmdb->getFormSubmissionDataRaw($formID, 'timestamp', 'DESC', 0, 0);
	$files = array();
	foreach($formData as $dataRow){
		$fileInfo = unserialize($dataRow[$itemID]);
		if(sizeof($fileInfo) > 1){			
			$fname = "(".$dataRow['timestamp'].") ".$fileInfo['filename'];
			$files[] = $tmpDir.$fname;
			fm_createFileFromDB($fname, $fileInfo, $tmpDir);
		}
	}
	
	if(sizeof($files) > 0){
	
		$zipFileName = sanitize_title($formInfo['title']." - ".$itemLabel).".zip";
		$zipFullPath =  $tmpDir.$zipFileName;	
		fm_createZIP($files, $zipFullPath); 
		 
		echo plugins_url('/'.get_option("fm-temp-dir").'/', __FILE__).$zipFileName;
		die();
	}
	else{
		echo "empty";
		die();
	}
	
	echo "fail";	
	die();
}

function fm_createFileFromDB($filename, $fileInfo, $dir){
	$fullpath = $dir.$filename;
	$fp = @fopen($fullpath,'wb') or die("Failed to open file");
	fwrite($fp, $fileInfo['contents']);
	fclose($fp);
}

/* Below is from David Walsh (davidwalsh.name), slightly modified. Thanks Dave! */
function fm_createZIP($files = array(),$destination = '') {
   
  //vars
  $valid_files = array();
  //if files were passed in...
  if(is_array($files)) {
    //cycle through each file
    foreach($files as $file) {
      //make sure the file exists
      if(file_exists($file)) {
        $valid_files[] = $file;
      }
    }
  }
  //if we have good files...
  if(count($valid_files)) {
    //create the archive
    $zip = new ZipArchive();
    if($zip->open($destination, ZIPARCHIVE::OVERWRITE | ZIPARCHIVE::CREATE | ZIPARCHIVE::FL_NODIR) !== true) {
	  return false;
    }
    //add the files
    foreach($valid_files as $file) {
      $zip->addFile($file,basename($file));
    }
    //debug
    //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
    
    //close the zip -- done!
    $zip->close();
    
    //check to make sure the file exists
	return file_exists($destination);
  }
  else
  { 
    return false;
  }
}

/**************************************************************/
/******* SHORTCODE ********************************************/

add_shortcode(get_option('fm-shortcode'), 'fm_shortcodeHandler');
function fm_shortcodeHandler($atts){
	return fm_doFormBySlug($atts[0]);
}


/**************************************************************/
/******* API **************************************************/

//takes a form's slug as a string.  It has the same behavior as using the shortcode.  Displays the form (according to the set behavior), processes posts, etc.
function fm_doFormBySlug($formSlug){
	global $fm_display;
	global $fmdb;
	global $current_user;
	global $fm_registered_user_only_msg;
		
	$formID = $fmdb->getFormID($formSlug);
	if($formID === false) return "(form ".(trim($formSlug)!=""?"'{$formSlug}' ":"")."not found)";
	
	$output = "";
	
	//get and parse the form settings
	$formInfo = $fmdb->getForm($formID);
	$arr = explode(",", $formInfo['behaviors']);
	$formBehaviors = array();
	foreach($arr as $v){
		$formBehaviors[$v] = true;
	}
	
	$userDataCount = $fmdb->getUserSubmissionCount($formID, $current_user->user_login);
	
	if($_POST['fm_id'] == $formID && (wp_verify_nonce($_POST['fm_nonce'],'fm-nonce') && ($userDataCount == 0 || !isset($formBehaviors['single_submission'])))){
		// process the post
		get_currentuserinfo();	
		
		$overwrite = (isset($formBehaviors['display_summ']) || isset($formBehaviors['overwrite']));
		$postData = $fmdb->processPost($formID, array('user'=>$current_user->user_login, 'user_ip' => fm_get_user_IP()), $overwrite);			
		foreach($formInfo['items'] as $item){
			if($item['type'] != 'file')
				$postData[$item['unique_name']] = stripslashes($postData[$item['unique_name']]);
		}
			
		if($fmdb->processFailed()){			
			return '<em>'.$fmdb->getErrorMessage().'</em>'.
					$output.
					$fm_display->displayForm($formInfo, array('action' => get_permalink()), $postData);
		}
		else{
			// send email notifications
				
			if($formInfo['use_advanced_email'] != 1){
			
				$formInfo['email_list'] = trim($formInfo['email_list']) ;
				$formInfo['email_user_field'] = trim($formInfo['email_user_field']);		
					
				if($formInfo['email_list'] != ""
				|| $formInfo['email_user_field'] != "" 
				|| $fmdb->getGlobalSetting('email_admin') == "YES"
				|| $fmdb->getGlobalSetting('email_reg_users') == "YES"){
				
					$subject = get_option('blogname').": '".$formInfo['title']."' Submission";				
					$message = $fm_display->displayDataSummary('email', $formInfo, $postData);
					$headers  = 'MIME-Version: 1.0'."\r\n".
								'Content-type: text/html'."\r\n".
								'From: '.get_option('admin_email')."\r\n".
								'Reply-To: '.get_option('admin_email')."\r\n";
					
					$temp = "";
					if($fmdb->getGlobalSetting('email_admin') == "YES")
						wp_mail(get_option('admin_email'), $subject, $message, $headers);
						
					if($fmdb->getGlobalSetting('email_reg_users') == "YES"){
						if(trim($current_user->user_email) != "")
							wp_mail($current_user->user_email, $subject, $message, $headers);
					}
					if($formInfo['email_list'] != "")
						wp_mail($formInfo['email_list'], $subject, $message, $headers);
						
					if($formInfo['email_user_field'] != "")
						wp_mail($postData[$formInfo['email_user_field']], $subject, $message, $headers);
		
				}
			}else{
				//use the advanced e-mail settings 
				$advEmail = new fm_advanced_email_class($formInfo, $postData);

				$emails = $advEmail->generateEmails($formInfo['advanced_email']);
								
				foreach($emails as $email){
					$headerStr = "";
					foreach($email['headers'] as $header => $value)
						$headerStr = $header.": ".$value."\r\n";
					wp_mail($email['to'], $email['subject'], $email['message'], $headerStr);
				}
			}
			//display the acknowledgment of a successful submission
			if(!isset($formBehaviors['display_summ']))
				return '<p>'.$formInfo['submitted_msg'].'</p>'.
						($formInfo['show_summary']==1 ? $fm_display->displayDataSummary('summary', $formInfo, $postData) : "");
			else
				$output = '<p>'.$formInfo['submitted_msg'].'</p>';
		}
	}
		
	//'reg_user_only', block unregistered users
	if(isset($formBehaviors['reg_user_only']) && $current_user->user_login == "") 
		return sprintf($fm_registered_user_only_msg, $formInfo['title']);
		
	//'display_summ', show previous submission if there is one and break
	
	if(isset($formBehaviors['display_summ'])){
		$userData = $fmdb->getUserSubmissions($formID, $current_user->user_login, true);
		
		if(sizeof($userData) > 0){		//only display a summary if there is a previous submission by this user
			if(!$_REQUEST['fm-edit-'.$formID] == '1'){							
				if(!isset($formBehaviors['edit']))
					return $output.$fm_display->displayDataSummary('summary', $formInfo, $postData);
				else{
					$currentPage = get_permalink();
					$parsedURL = parse_url($currentPage);
					if(trim($parsedURL['query']) == "")
						$editLink = $curentPage."?fm-edit-".$formID."=1";
					else
						$editLink = $currentPage."&fm-edit-".$formID."=1";
					
					return $output.
							$fm_display->displayDataSummary('summary', $formInfo, $userData[0]).
							"<span class=\"fm-data-summary-edit\"><a href=\"".$editLink."\">Edit '".$formInfo['title']."'</a></span>";
				}				
			}
			else
				return $output.$fm_display->displayForm($formInfo, array('action' => get_permalink()), $userData[0]);
		}
	}
	
	//if we got this far, just display the form
	return $fm_display->displayForm($formInfo, array('action' => get_permalink()));
}
?>