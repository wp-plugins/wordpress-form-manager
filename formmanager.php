<?php
/*
Plugin Name: Form Manager
Plugin URI: http://www.campbellhoffman.com/form-manager/
Description: Create custom forms; download entered data in .csv format; validation, required fields, custom acknowledgments;
Version: 1.3.4
Author: Campbell Hoffman
Author URI: http://www.campbellhoffman.com/
License: GPL2
*/

global $fm_currentVersion;
$fm_currentVersion = "1.3.4";

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

/**************************************************************/
/******* PLUGIN OPTIONS ***************************************/

update_option("fm-shortcode", "form");
update_option("fm-forms-table-name", "fm_forms");
update_option("fm-items-table-name", "fm_items");
update_option("fm-settings-table-name", "fm_settings");
update_option("fm-data-table-prefix", "fm_data");
update_option("fm-query-table-prefix", "fm_queries");

global $wpdb;
global $fmdb;
$fmdb = new fm_db_class($wpdb->prefix.get_option('fm-forms-table-name'),
					$wpdb->prefix.get_option('fm-items-table-name'),
					$wpdb->prefix.get_option('fm-settings-table-name'),
					$wpdb->dbh
					);
$fm_display = new fm_display_class();

				
/**************************************************************/
/******* DATABASE SETUP ***************************************/

function fm_install () {
	global $fmdb;	
	$fmdb->setupFormManager();   
	
	/* covers updates from 1.3.0 */
	$q = "UPDATE `{$fmdb->formsTable}` SET `behaviors` = 'reg_user_only,display_summ,single_submission' WHERE `behaviors` = 'reg_user_only,no_dup'";
	$fmdb->query($q);
	$q = "UPDATE `{$fmdb->formsTable}` SET `behaviors` = 'reg_user_only,display_summ,edit' WHERE `behaviors` = 'reg_user_only,no_dup,edit'";
	$fmdb->query($q);						
}  
register_activation_hook(__FILE__,'fm_install');

//uninstall - delete the table(s). 
function fm_uninstall() {
	global $fmdb;	
	$fmdb->removeFormManager();
}
register_uninstall_hook(__FILE__,'fm_uninstall');


/**************************************************************/
/******* HOUSEKEEPING *****************************************/

//delete .csv files on each login
add_action('wp_login', 'fm_cleanCSVData');
function fm_cleanCSVData(){
	$dirName = @dirname(__FILE__)."/csvdata";
	$dir = @opendir($dirName);
	while($fname = @readdir($dir)) {
		if(strpos($fname, ".csv") !== false)
			@unlink($dirName."/".$fname);
	}
	@closedir($dir);
}

/**************************************************************/
/******* INIT, SCRIPTS & CSS **********************************/

add_action('admin_init', 'fm_adminInit');
function fm_adminInit(){
	wp_enqueue_script('scriptaculous');
	wp_enqueue_script('scriptaculous-dragdrop');
	
	wp_enqueue_script('form-manager-js', plugins_url('/js/scripts.js', __FILE__));	
	
	wp_register_style('form-manager-css', plugins_url('/css/style.css', __FILE__));
	wp_enqueue_style('form-manager-css');
}

add_action('init', 'fm_userInit');
function fm_userInit(){
	global $fm_currentVersion;
	//update check, since the snarky wordpress dev changed the behavior of a function based on its english name, rather than its widely accepted usage.
	//"The perfect is the enemy of the good". 
	$ver = get_option('fm-version');
	if($ver != $fm_currentVersion){
		update_option('fm-version', $fm_currentVersion);
		fm_install();
	}

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
	
	foreach($pages as $page)
		add_action('admin_head-'.$page, 'fm_adminHeadPluginOnly');
}

add_action('admin_head', 'fm_adminHead');
function fm_adminHead(){
	global $submenu;	
	
	//we don't actually want all the pages to show up in the menu, but having slugs for pages makes things easy
	//unset($submenu['fm-admin-main'][0]);
	unset($submenu['fm-admin-main'][1]);
	unset($submenu['fm-admin-main'][2]);
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

function fm_showEditPage(){
	include 'editform.php';
}

function fm_showDataPage(){
	include 'formdata.php';
}

function fm_showMainPage(){	
	include 'main.php';
}

function fm_showSettingsPage(){
	include 'editsettings.php';
}

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
	$formInfo['behaviors'] = $_POST['behaviors'];
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
	if($_POST['email_admin'] == "true")
		$emailList[] = get_option('admin_email');
		
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

//Create a CSV file for download
add_action('wp_ajax_fm_create_csv', 'fm_createCSV');
function fm_createCSV(){
	global $fmdb;
	
	$fname = sanitize_title($_POST['title'])." (".date("m-y-d h-i-s").").csv";
	
	$fmdb->writeFormSubmissionDataCSV($_POST['id'], dirname(__FILE__)."/csvdata/".$fname);
	
	echo plugins_url('/csvdata/',  __FILE__).$fname;
	
	die();
}

//Use the 'formelements' helpers
add_action('wp_ajax_fm_create_form_element', 'fm_createFormElement');
function fm_createFormElement(){
	//echo "<pre>".print_r($elem,true)."</pre>";
	echo fe_getElementHTML($_POST['elem']);
	die();
}

/**************************************************************/
/******* SHORTCODE ********************************************/

add_shortcode(get_option('fm-shortcode'), 'fm_shortcodeHandler');
function fm_shortcodeHandler($atts){
	global $fm_display;
	global $fmdb;
	global $current_user;
	global $fm_registered_user_only_msg;
		
	$formID = $fmdb->getFormID($atts[0]);
	if($formID === false) return "(form ".(trim($atts[0])!=""?"'{$atts[0]}' ":"")."not found)";
	
	$output = "";
	
	//get and parse the form settings
	$formInfo = $fmdb->getForm($formID);
	$arr = explode(",", $formInfo['behaviors']);
	$formBehaviors = array();
	foreach($arr as $v){
		$formBehaviors[$v] = true;
	}
	
	$userData = $fmdb->getUserSubmissions($formID, $current_user->user_login, true);
	
	if($_POST['fm_id'] == $formID && (wp_verify_nonce($_POST['fm_nonce'],'fm-nonce') && (sizeof($userData) == 0 || !isset($formBehaviors['single_submission'])))){
		// process the post
		get_currentuserinfo();	
		
		$overwrite = (isset($formBehaviors['display_summ']) || isset($formBehaviors['overwrite']));
		$postData = $fmdb->processPost($formID, array('user'=>$current_user->user_login), $overwrite);	
		
		if($fmdb->processFailed()){
			foreach($postData as $k=>$v){
				$postData[$k] = stripslashes($v);
			}
			return $output.$fm_display->displayForm($formInfo, array('action' => get_permalink()), $postData);
		}
		else{					
			// send email notifications
			$formInfo['email_list'] = trim($formInfo['email_list']) ;
			$formInfo['email_user_field'] = trim($formInfo['email_user_field']);		
				
			if($formInfo['email_list'] != "" || $formInfo['email_user_field'] != ""){
				$subject = get_option('blogname').": '".$formInfo['title']."' Submission";
				$message = $subject."\n";
				if($postData['user'] != "") $message.= "User: ".$postData['user']."\n";
				$message.= "Timestamp: ".$postData['timestamp']."\n\n";
				foreach($formInfo['items'] as $formItem){
					if($formItem['db_type'] != "NONE")
						$message.= $formItem['label'].": ".stripslashes($postData[$formItem['unique_name']])."\n";
				}
				if($formInfo['email_list'] != "")
					wp_mail($formInfo['email_list'], $subject, $message);
				if($formInfo['email_user_field'] != "") //do a separate call, in case the form / user input is malformed, so we at least get the admin emails sent
					wp_mail($postData[$formInfo['email_user_field']], $subject, $message);
			}
			
			if(!isset($formBehaviors['display_summ']))
				return '<p>'.$formInfo['submitted_msg'].'</p>';
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
					return $output.$fm_display->displayDataSummary($formInfo, $userData[0]);
				else{
					$currentPage = get_permalink();
					$parsedURL = parse_url($currentPage);
					if(trim($parsedURL['query']) == "")
						$editLink = $curentPage."?fm-edit-".$formID."=1";
					else
						$editLink = $currentPage."&fm-edit-".$formID."=1";
					
					$str.= "<span class=\"fm-data-summary-edit\"><a href=\"".$editLink."\">Edit '".$formInfo['title']."'</a></span>";
					return $output.$fm_display->displayDataSummary($formInfo, $userData[0], "<h3>".$formInfo['title']."</h3>\n" , $str);
				}				
			}
			else
				return $output.$fm_display->displayForm($formInfo, array('action' => get_permalink()), $userData[0]);
		}
	}
	
	//if we got this far, just display the form
	return $fm_display->displayForm($formInfo, array('action' => get_permalink()));
}

include 'settings.php';
?>