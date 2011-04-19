<?php
/*
Plugin Name: Form Manager
Plugin URI: http://www.campbellhoffman.com/form-manager/
Description: Update 1.2.4 fixes install error.  <br /> Create custom forms; download entered data in .csv format; validation, required fields, custom acknowledgments;
Version: 1.2.4
Author: Campbell Hoffman
Author URI: http://www.campbellhoffman.com/
License: GPL2
*/

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

update_option("shortcode", "form");
update_option("forms-table-name", "fm_forms");
update_option("items-table-name", "fm_items");
update_option("data-table-prefix", "fm_data");
update_option("query-table-prefix", "fm_queries");

global $wpdb;
global $fmdb;
$fmdb = new fm_db_class($wpdb->prefix.get_option('forms-table-name'),
					$wpdb->prefix.get_option('items-table-name'),
					$wpdb->dbh);
$fm_display = new fm_display_class();

				
/**************************************************************/
/******* DATABASE SETUP ***************************************/

function fd_install () {
	global $fmdb;	
	$fmdb->setupFormManager();   
}  
register_activation_hook(__FILE__,'fd_install');

//uninstall - delete the table(s). 
function fd_uninstall() {
	global $fmdb;	
	$fmdb->removeFormManager();
}

register_uninstall_hook(__FILE__,'fd_uninstall');


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
	
	foreach($pages as $page)
		add_action('admin_head-'.$page, 'fm_adminHeadPluginOnly');
}

add_action('admin_head', 'fm_adminHead');
function fm_adminHead(){
	global $submenu;	
	//we don't actually want all the pages to show up in the menu, but having slugs for pages makes things easy
	unset($submenu['fm-admin-main'][0]);
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


/**************************************************************/
/******* SHORTCODE ********************************************/

add_shortcode(get_option('shortcode'), 'fm_shortcodeHandler');
function fm_shortcodeHandler($atts){
	global $fm_display;
	global $fmdb;
	global $current_user;
	
	$formID = $fmdb->getFormID($atts[0]);
	if($formID === false) return "(form ".(trim($atts[0])!=""?"'{$atts[0]}' ":"")."not found)";
	$formInfo = $fmdb->getForm($formID);
	
	if(wp_verify_nonce($_POST['fm-submit-nonce'],'fm-submit')){
		get_currentuserinfo();		
		$fmdb->processPost($formID, array('user'=>$current_user->user_login));
		return $formInfo['submitted_msg'];
	}
	else if($fmdb->isForm($formID))
		return $fm_display->displayForm($formInfo);
}

include 'settings.php';
?>