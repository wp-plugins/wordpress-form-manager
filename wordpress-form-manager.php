<?php
/*
Plugin Name: Form Manager
Plugin URI: http://www.campbellhoffman.com/form-manager/
Description: Create custom forms; download entered data in .csv format; validation, required fields, custom acknowledgments;
Version: 1.5.2
Author: Campbell Hoffman
Author URI: http://www.campbellhoffman.com/
Text Domain: wordpress-form-manager
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

$fm_oldIncludePath = get_include_path();
set_include_path(dirname(__FILE__).'/');

global $fm_currentVersion;
$fm_currentVersion = "1.5.2";

global $fm_DEBUG;
$fm_DEBUG = false;

// flags for other plugins we want to integrate with
global $fm_SLIMSTAT_EXISTS;
global $fm_MEMBERS_EXISTS;

$fm_SLIMSTAT_EXISTS = false;
$fm_MEMBERS_EXISTS = false;

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
	wp_die( __('Form Manager requires WordPress version 3.0 or higher', 'wordpress-form-manager') );
	
// only PHP 5.0+
if ( version_compare(PHP_VERSION, '5.2.0', '<') ) 
	wp_die( __('Form Manager requires PHP version 5.2 or higher', 'wordpress-form-manager') );

include 'helpers.php';

include 'db.php';
include 'display.php';
include 'template.php';
include 'email.php';
include 'formdefinition.php';

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
update_option("fm-data-shortcode", "formdata");

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
	
	//updates from 1.4.10 and previous
	$fmdb->fixTemplatesTableModified();
	
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
	delete_option('fm-data-shortcode');
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
	global $fm_SLIMSTAT_EXISTS;	
	if(get_option('slimstat_secret') !==  false) $fm_SLIMSTAT_EXISTS = true;
}

add_action('admin_enqueue_scripts', 'fm_adminEnqueueScripts');
function fm_adminEnqueueScripts(){
	wp_enqueue_script('form-manager-js', plugins_url('/js/scripts.js', __FILE__), array('scriptaculous'));	
	
	wp_register_style('form-manager-css', plugins_url('/css/style.css', __FILE__));
	wp_enqueue_style('form-manager-css');	
}

add_action('init', 'fm_userInit');
function fm_userInit(){
	global $fm_currentVersion;
	global $fm_templates;
	
	load_plugin_textdomain('wordpress-form-manager', false, dirname(plugin_basename(__FILE__)).'/languages/' );
		
	//update check, since the snarky wordpress dev changed the behavior of a function based on its english name, rather than its widely accepted usage.
	//"The perfect is the enemy of the good". 
	$ver = get_option('fm-version');
	if($ver != $fm_currentVersion){
		fm_install();
	}

	include 'settings.php';
	
	$fm_templates->initTemplates();
	
	wp_enqueue_script('form-manager-js-user', plugins_url('/js/userscripts.js', __FILE__));
	
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
	$pages[] = add_object_page(__("Forms", 'wordpress-form-manager'), __("Forms", 'wordpress-form-manager'), apply_filters('fm_main_capability', 'manage_options'), "fm-admin-main", 'fm_showMainPage', plugins_url('/mce_plugins/formmanager.png', __FILE__));
	$pages[] = add_submenu_page("fm-admin-main", __("Edit", 'wordpress-form-manager'), __("Edit", 'wordpress-form-manager'), apply_filters('fm_forms_capability', 'manage_options'), "fm-edit-form", 'fm_showEditPage');
	//$pages[] = add_submenu_page("fm-admin-main", __("Data", 'wordpress-form-manager'), __("Data", 'wordpress-form-manager'), apply_filters('fm_data_capability', 'manage_options'), "fm-form-data", 'fm_showDataPage');	
	
	//at some point, make this link go to a fresh form
	//$pages[] = add_submenu_page("fm-admin-main", "Add New", "Add New", "manage_options", "fm-add-new", 'fm_showMainPage');
	
	$pages[] = add_submenu_page("fm-admin-main", __("Settings", 'wordpress-form-manager'), __("Settings", 'wordpress-form-manager'), apply_filters('fm_settings_capability', 'manage_options'), "fm-global-settings", 'fm_showSettingsPage');
	$pages[] = add_submenu_page("fm-admin-main", __("Advanced Settings", 'wordpress-form-manager'), __("Advanced Settings", 'wordpress-form-manager'), apply_filters('fm_settings_advanced_capability', 'manage_options'), "fm-global-settings-advanced", 'fm_showSettingsAdvancedPage');
	
	//$pages[] = add_submenu_page("fm-admin-main", __("Edit Form - Advanced", 'wordpress-form-manager'), __("Edit Form - Advanced", 'wordpress-form-manager'), apply_filters('fm_forms_advanced_capability', 'manage_options'), "fm-edit-form-advanced", 'fm_showEditAdvancedPage');
	
	foreach($pages as $page)
		add_action('admin_head-'.$page, 'fm_adminHeadPluginOnly');
		
	$pluginName = plugin_basename(__FILE__);
	add_filter( 'plugin_action_links_' . $pluginName, 'fm_pluginActions' );
}

function fm_pluginActions($links){ 
	$settings_link = '<a href="'.get_admin_url(null, 'admin.php')."?page=fm-global-settings".'">' . __('Settings', 'wordpress-form-manager') . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}	

add_action('admin_head', 'fm_adminHead');
function fm_adminHead(){
	global $submenu;	
	
	//we don't actually want all the pages to show up in the menu, but having slugs for pages makes things easy
	
	/*$toUnset = array('fm-edit-form',
						'fm-form-data',
						'fm-edit-form-advanced'
						); */
						
	$toUnset = array('fm-edit-form');
						
	foreach($submenu['fm-admin-main'] as $index => $submenuItem)
		if(in_array($submenuItem[2], $toUnset, true))
			unset($submenu['fm-admin-main'][$index]);	

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

function fm_showEditPage(){	include 'pages/editform.php'; }
function fm_showEditAdvancedPage(){	include 'pages/editformadv.php'; }
function fm_showDataPage(){	include 'pages/formdata.php'; }
function fm_showMainPage(){	include 'pages/main.php'; }
function fm_showSettingsPage(){	include 'pages/editsettings.php'; }
function fm_showSettingsAdvancedPage(){	include 'pages/editsettingsadv.php'; }

// capabilities

if (function_exists( 'members_plugin_init' )){
	$fm_MEMBERS_EXISTS = true;
	
	add_filter('fm_main_capability', 'fm_main_capability');
	add_filter('fm_forms_capability', 'fm_forms_capability');
	add_filter('fm_forms_advanced_capability', 'fm_forms_advanced_capability');
	add_filter('fm_data_capability', 'fm_data_capability');
	add_filter('fm_settings_capability', 'fm_settings_capability');
	add_filter('fm_settings_advanced_capability', 'fm_settings_advanced_capability');
	
	add_filter('members_get_capabilities', 'fm_add_members_capabilities' ); 
}

function fm_main_capability( $cap ) { return 'form_manager_main'; }
function fm_forms_capability( $cap ) { return 'form_manager_forms'; }
function fm_forms_advanced_capability( $cap ) { return 'form_manager_forms_advanced'; }
function fm_data_capability( $cap ) { return 'form_manager_data'; }
function fm_settings_capability( $cap ) { return 'form_manager_settings'; }
function fm_settings_advanced_capability( $cap ) { return 'form_manager_settings_advanced'; }

function fm_add_members_capabilities( $caps ) {
	$caps[] = 'form_manager_main';
	$caps[] = 'form_manager_forms';
	$caps[] = 'form_manager_delete_forms';
	$caps[] = 'form_manager_add_forms';
	$caps[] = 'form_manager_forms_advanced';
	$caps[] = 'form_manager_data';
	$caps[] = 'form_manager_settings';
	$caps[] = 'form_manager_settings_advanced';
	
	$caps[] = 'form_manager_edit_data';
	$caps[] = 'form_manager_delete_data';
	$caps[] = 'form_manager_nicknames';
	$caps[] = 'form_manager_conditions';
	
	return $caps;
}

include 'ajax.php';

/**************************************************************/
/******* SHORTCODES *******************************************/

add_shortcode(get_option('fm-shortcode'), 'fm_shortcodeHandler');
function fm_shortcodeHandler($atts){
	if(!isset($atts[0])) return sprintf(__("Form Manager: shortcode must include a form slug.  For example, something like '%s'", 'wordpress-form-manager'), "[form form-1]");
	return fm_doFormBySlug($atts[0]);
}

add_shortcode(get_option('fm-data-shortcode'), 'fm_dataShortcodeHandler');
function fm_dataShortcodeHandler($atts){
	if(!isset($atts[0])) return sprintf(__("Form Manager: shortcode must include a form slug.  For example, something like '%s'", 'wordpress-form-manager'), "[formdata form-1]");
	$formSlug = $atts[0];
	
	$atts = shortcode_atts(array(
		'orderby' => 'timestamp',
		'order' => 'desc',
		'dataperpage' => 30,
		'template' => 'fm-summary-multi'
		), $atts);
		
	return fm_doDataListBySlug($formSlug, $atts['template'], $atts['orderby'], $atts['order'], $atts['dataperpage']);	
}

/**************************************************************/
/******* HELPERS **********************************************/

//allow scheduling of clearing the temporary directory

function fm_deleteTemporaryFiles($filename){
	//for now, just clear the directory.
	$dir = dirname(__FILE__)."/".get_option("fm-temp-dir");	
	if($handle = opendir($dir)){
		while(($file = readdir($handle)) !== false){
			if($file != "." && $file != ".." && is_file($dir."/".$file))
				unlink($dir."/".$file);
		}
		closedir($handle);		
	}
}
add_action('fm_delete_temporary_file', 'fm_deleteTemporaryFiles');

/**************************************************************/

include 'api.php';

/* ANDREA : include php for create TinyMCE Button */
include 'tinymce.php';


//set the include path back to whatever it was before:
set_include_path($fm_oldIncludePath);

?>