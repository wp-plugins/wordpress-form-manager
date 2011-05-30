<?php
/* translators: the following are used when displaying a form */

include 'types.php';

class fm_display_class{

// template variables
var $currentFormInfo;
var $currentFormOptions;
var $currentFormValues;
var $currentFormData;
var $currentItemIndex;

///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////

//options:
//	class - 'class' attribute for the <form> tag
//	action - 'action' attribute for the <form> tag
//'params' is an associative array of hidden values inserted into the form
function displayForm($formInfo, $options=array(), $values=array()){
	global $fm_templates;
	global $fm_controls;
	global $fmdb;
	
	$templateFile = $formInfo['form_template'];
	if($templateFile == '') $templateFile = $fmdb->getGlobalSetting('template_form');
	if($templateFile == '') $templateFile = get_option('fm-default-form-template');
	
	if(file_exists($fm_templates->templatesDir.'/'.$templateFile))
		$str = $this->displayFormTemplate($templateFile, $formInfo, $options, $values);
	else
		$str = $this->displayFormTemplate(get_option('fm-default-form-template'), $formInfo, $options, $values);

	return $str;
}


//shows an unordered list of left-labeled form items, no form tags, no submit button, no validation scripts
function displayFormBare($formInfo, $options=array(), $values=array()){
	global $msg;
	global $fmdb;
	global $fm_controls;	
	
	////////////////////////////////////////////////////////////////////////////////////////
	
	$defaults = array('label_width' => '200',
						'ul_class' => '',
						'li_class' => '',
						'exclude_types' => array(),
						'include_types' => array(),
						'display_callbacks' => array(),
						'unique_name_suffix' => ''
					);	
	foreach($defaults as $key => $default)
		if(!isset($options[$key])) $options[$key] = $default;
		
	///////////////////////////////////////////////////////////////////////////////////////
		
	$str.= "<ul".($options['ul_class'] != '' ? " class=\"".$options['ul_class']."\"" : "").">\n";
	
		foreach($formInfo['items'] as $item){
			
			//if override $item['extra']['value'] if the unique_name is in $values
			if(isset($values[$item['unique_name']]))
				$item['extra']['value'] = $values[$item['unique_name']];
			
			if(!in_array($item['type'], $options['exclude_types']) || in_array($item['type'], $options['include_types'])){
			
				$str.= "<li".($options['li_class'] != '' ? " class=\"".$options['li_class']."\"" : "").">";
				
				////////////////////////////////////////////////////////////////////////////////////////
				
					$str.='<table><tr>';
					$str.='<td style="width:'.$options['label_width'].'px"><label>'.(trim($item['nickname']) == "" ? $item['label'] : $item['nickname']);
					if($item['required']=='1')	$str.= '&nbsp;<em>*</em>';
					$str.='</label>';
					$str.='</td>';
					$str.='<td>';
					
					reset($options['display_callbacks']);
					if(array_key_exists($item['type'], $options['display_callbacks']))
						$str.= call_user_func($options['display_callbacks'][$item['type']], $item['unique_name'].$options['unique_name_suffix'], $item);
					else
						$str.= $fm_controls[$item['type']]->showItem($item['unique_name'].$options['unique_name_suffix'], $item);
					
					$str.='</td>';
					$str.='</tr></table>';			
							
				////////////////////////////////////////////////////////////////////////////////////////
				
				$str.= "</li>\n";
			}
		}
	
	$str.= "</ul>\n";	
	
	return $str;
}

function displayFormTemplate($template, $formInfo, $options=array(), $values=array()){
	global $msg;
	global $fmdb;
	global $fm_controls;
	global $fm_templates;
	global $fm_template_controls;
	
	$templateInfo = $fm_templates->getTemplateAttributes($template);
	
	//if override $item['extra']['value'] if the unique_name is in $values	
	foreach($formInfo['items'] as $k=>$item)
		if(isset($values[$item['unique_name']]))
			$formInfo['items'][$k]['extra']['value'] = $values[$item['unique_name']];	
			
	if(!isset($options['class'])) $options['class'] = 'fm-form';
		
	$this->currentFormInfo = $formInfo;
	$this->currentFormOptions = $options;
	$this->currentFormValues = $values;
	$this->currentItemIndex = -1;		
	
	if(isset($templateInfo['options']))
		foreach($templateInfo['options'] as $option){
			$varName = substr($option['var'],1);
			if(!isset($formInfo['template_values'][$varName])) $value = $option['default'];
			else $value = $fm_template_controls[$option['type']]->parseStoredValue($formInfo['template_values'][$varName], $option);
			${$varName} = $value;
		}
	
	ob_start();	
	
	// load the template
	include $fm_templates->templatesDir."/".$template;	
	
	$str = ob_get_contents();
	ob_end_clean();
		
	$str.= $this->displayFormScripts($formInfo, $options);
	
	return $str;
}

protected function displayFormScripts($formInfo, $options=array()){
	global $fm_controls;
	
	$str.="<script type=\"text/javascript\">\n";
	$str.="// validation\n";
	foreach($formInfo['items'] as $item){
		if($item['required'] == '1'){
		 	$callback = $fm_controls[$item['type']]->getRequiredValidatorName();
			if($callback != "")
				$str.="fm_val_register('".$formInfo['ID']."', ".
					"'".$item['unique_name']."', ".
					"'".$callback."', ".
					"'".format_string_for_js(sprintf($formInfo['required_msg'], $item['label']))."');\n";
		}
		if($item['extra']['validation'] != 'none'){
			$callback = $fm_controls[$item['type']]->getGeneralValidatorName();
			if($callback != ""){
				$str.="fm_val_register('".$formInfo['ID']."', ".
					"'".$item['unique_name']."', ".
					"'".$callback."', ".
					"'".format_string_for_js(sprintf($fm_controls[$item['type']]->getGeneralValidatorMessage($item['extra']['validation']), $item['label']))."', ".
					"'".$item['extra']['validation']."');\n";
			}
		}
	}	
	
	$str.="// register form items \n";
	foreach($formInfo['items'] as $item){
		$str.="fm_register_form_item('".$formInfo['ID']."', '".$item['unique_name']."', '".$item['type']."', {placeholder: '".$item['extra']['value']."'});\n";
	}
	
	if(isset($options['use_placeholders']) && $options['use_placeholders'] === false)
		$str.="fm_remove_placeholders();\n"; //this will convert placeholders into values; used to re-populate a form after a bad submission, for user profile style, etc., where the 'value' field needs to be the fields' value rather than a placeholder
	else
		$str.="fm_add_placeholders();\n"; //this will make sure the placeholder functionality is simulated in browsers that do not support HTML 5 'placeholder' attribute in text fields
	
	$str.="</script>\n";
	return $str;
}

///////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////

function displayDataSummary($type, $formInfo, $data){
	global $fmdb;
	global $fm_templates;
	
	$templateFile = "";
	
	if($type == 'email'){
		$templateFile = $formInfo['email_template'];
		if($templateFile == '') $templateFile = $fmdb->getGlobalSetting('template_email');
		if($templateFile == '') $templateFile = get_option('fm-default-summary-template');
	}
	else if($type == 'summary'){
		$templateFile = $formInfo['summary_template'];
		if($templateFile == '') $templateFile = $fmdb->getGlobalSetting('template_summary');
		if($templateFile == '') $templateFile = get_option('fm-default-summary-template');
	}
	else if($fm_templates->isTemplate($type.".php"))	
		$templateFile = $type.".php";
	
	if($templateFile != "")
		return $this->displayDataSummaryTemplate($templateFile, $formInfo, $data);
	
	return "The template '".$type."' was not found.";	
}

function displayDataSummaryNotemplate($formInfo, $data, $before = "", $after = "", $userAndTimestamp = false){
	global $fm_controls;
	
	$str = "";
	$str.= "<div class=\"fm-data-summary\">\n";
	$str.= $before;
	$str.= "<ul>\n";
	if($userAndTimestamp){
		$str.= "<li><span class=\"fm-data-summary-label\">User:&nbsp;&nbsp;</span><span class=\"fm-data-summary-value\">".$data['user']."</span></li>\n";
		$str.= "<li><span class=\"fm-data-summary-label\">Timestamp:&nbsp;&nbsp;</span><span class=\"fm-data-summary-value\">".$data['timestamp']."</span></li>\n";
	}
	foreach($formInfo['items'] as $item){
		if($item['db_type'] != "NONE")
			$str.= "<li><span class=\"fm-data-summary-label\">".$item['label'].":&nbsp;&nbsp;</span><span class=\"fm-data-summary-value\">".$fm_controls[$item['type']]->parseData($item['unique_name'], $item, $data[$item['unique_name']])."</span></li>\n";
	}
	$str.= "</ul>\n";
	$str.= $after;
	$str.= "</div>";
	return $str;
}

///////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////

function displayDataSummaryTemplate($template, $formInfo, $data){
	global $fm_templates;
	
	$this->currentFormInfo = $formInfo;
	$this->currentFormData = $data;
	$this->currentItemIndex = -1;
	
	ob_start();
	
	include $fm_templates->templatesDir."/".$template;
	
	$str = ob_get_contents();
	ob_end_clean();
	return $str;
}

///////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////

function getEditorItem($uniqueName, $type, $itemInfo){
	global $fm_controls;
	
	if(isset($fm_controls[$type]))
		$control = $fm_controls[$type];
	else
		$control = $fm_controls['default'];
	// a new item
	if($itemInfo == null) $itemInfo = $control->itemDefaults();
	
	$itemInfo['type'] = $type;
	$itemInfo['unique_name'] = $uniqueName;
	
	$str = "<table class=\"editor-item-table\">".
			"<tr>".	
			"<td class=\"editor-item-container\">".$control->showEditorItem($uniqueName, $itemInfo)."</td>".
			"<td class=\"editor-item-buttons\"><a class=\"edit-form-button\" onclick=\"fm_showEditDivCallback('{$uniqueName}','".$control->getShowHideCallbackName()."')\" id=\"{$uniqueName}-edit\"/>edit</a></td>".
			"<td class=\"editor-item-buttons\">"."<a class=\"edit-form-button\" onclick=\"fm_deleteItem('{$uniqueName}')\">delete</a>"."</td>".
			"</tr>".
			"</table>".
			"<input type=\"hidden\" id=\"{$uniqueName}-type\" value=\"{$type}\" />";
	
	return $str;
}

///////////////////////////////////////////////////////////////////////////////////////////////
}


///////////////////////////////////////////////////////////////////////////////////////////////
///// FORM TEMPLATE FUNCTIONS /////////////////////////////////////////////////////////////////

function fm_form_start(){
	global $fm_display;

	echo "<form enctype=\"multipart/form-data\" class=\"".$fm_display->currentFormOptions['class']."\" ".
			"method=\"post\" action=\"".$fm_display->currentFormOptions['action']."\" ".
			"name=\"fm-form-".$fm_display->currentFormInfo['ID']."\" id=\"fm-form-".$fm_display->currentFormInfo['ID']."\">\n";	
}
function fm_form_class(){
	global $fm_display;
	return $fm_display->currentFormOptions['class'];
}
function fm_form_action(){
	global $fm_display;
	return $fm_display->currentFormOptions['action'];
}
function fm_form_end(){
	global $fm_display;
	$str = fm_form_hidden();
	$str.= "</form>\n";
	echo $str;
}
function fm_form_hidden(){
	global $fm_display;
	$str = "<input type=\"hidden\" name=\"fm_nonce\" value=\"".wp_create_nonce('fm-nonce')."\" />\n";	
	$str.= "<input type=\"hidden\" name=\"fm_id\" value=\"".$fm_display->currentFormInfo['ID']."\" />\n";	
	return $str;
}

function fm_form_ID(){
	global $fm_display;
	return "fm-form-".$fm_display->currentFormInfo['ID'];
}
function fm_form_submit_btn_script(){
	global $fm_display;
	return "fm_validate(".$fm_display->currentFormInfo['ID'].")";
}

function fm_form_the_title(){
	global $fm_display;
	return $fm_display->currentFormInfo['title'];
}

function fm_form_the_submit_btn(){
	global $fm_display;
	return "<input type=\"submit\" ".
			"name=\"fm_form_submit\" ".
			"class=\"submit\" ".
			"value=\"".$fm_display->currentFormInfo['submit_btn_text']."\" ".
			"onclick=\"return fm_validate(".$fm_display->currentFormInfo['ID'].")\" ".
			" />\n";
}

function fm_form_submit_btn_name(){
	return "fm_form_submit";
}

function fm_form_submit_btn_text(){
	global $fm_display;
	return htmlspecialchars($fm_display->currentFormInfo['submit_btn_text']);
}

function fm_form_have_items(){
	global $fm_display;
	return ($fm_display->currentItemIndex < sizeof($fm_display->currentFormInfo['items'])-1);
}

function fm_form_the_item(){
	global $fm_display;
	$fm_display->currentItemIndex++;
}

function fm_form_the_label(){
	global $fm_display;
	return $fm_display->currentFormInfo['items'][$fm_display->currentItemIndex]['label'];
}

function fm_form_the_input(){
	global $fm_display;
	global $fm_controls;
	$item = fm_form_get_item(); 
	if(isset($fm_display->currentFormValues[$item['unique_name']]))
		$item['extra']['value'] = $fm_display->currentFormValues[$item['unique_name']];				
	return $fm_controls[$item['type']]->showItem($item['unique_name'], $item);
}

function fm_form_is_separator(){
	return (fm_form_item_type() == 'separator');
}

function fm_form_is_note(){
	return (fm_form_item_type() == 'note');
}

function fm_form_item_type(){
	global $fm_display;
	return $fm_display->currentFormInfo['items'][$fm_display->currentItemIndex]['type'];
}

function fm_form_is_required(){
	global $fm_display;
	return ($fm_display->currentFormInfo['items'][$fm_display->currentItemIndex]['required'] == '1');
}

function fm_form_the_nickname(){
	global $fm_display;
	return $fm_display->currentFormInfo['items'][$fm_display->currentItemIndex]['nickname'];
}

function fm_form_get_item(){
	global $fm_display;
	return $fm_display->currentFormInfo['items'][$fm_display->currentItemIndex]; 
}

function fm_form_get_item_input($nickname){
	global $fm_display;
	global $fm_controls;
	$item = fm_summary_get_item($nickname);
	if(isset($fm_display->currentFormValues[$item['unique_name']]))
		$item['extra']['value'] = $fm_display->currentFormValues[$item['unique_name']];				
	return $fm_controls[$item['type']]->showItem($item['unique_name'], $item);
}

function fm_form_get_item_label($nickname){
	return fm_summary_get_item_label($nickname);
}

///////////////////////////////////////////////////////////////////////////////////////////////
///// EMAIL TEMPLATE FUNCTIONS ////////////////////////////////////////////////////////////////

function fm_summary_have_items(){
	global $fm_display;
	return ($fm_display->currentItemIndex < sizeof($fm_display->currentFormInfo['items'])-1);
}

function fm_summary_the_item(){
	global $fm_display;
	$fm_display->currentItemIndex++;
}

function fm_summary_the_label(){
	global $fm_display;
	return $fm_display->currentFormInfo['items'][$fm_display->currentItemIndex]['label'];
}

function fm_summary_the_type(){
	global $fm_display;
	return $fm_display->currentFormInfo['items'][$fm_display->currentItemIndex]['type'];
}

function fm_summary_has_data(){
	global $fm_display;
	return $fm_display->currentFormInfo['items'][$fm_display->currentItemIndex]['db_type'] != "NONE";
}

function fm_summary_the_value(){
	global $fm_display;
	global $fm_controls;
	$item = $fm_display->currentFormInfo['items'][$fm_display->currentItemIndex];
	return $fm_controls[$item['type']]->parseData($item['unique_name'], $item, $fm_display->currentFormData[$item['unique_name']]);
}

function fm_summary_the_timestamp(){
	return fm_summary_get_value('timestamp');
}

function fm_summary_the_user(){
	return fm_summary_get_value('user');
}

function fm_summary_the_nickname(){
	global $fm_display;
	return $fm_display->currentFormInfo['items'][$fm_display->currentItemIndex]['nickname'];
}

function fm_summary_the_IP(){
	return fm_summary_get_value('user_ip');
}

function fm_summary_the_title(){
	global $fm_display;
	return $fm_display->currentFormInfo['title'];
}

function fm_summary_get_item_label($nickname){
	$item = fm_summary_get_item($nickname);
	return $item['label'];
}

function fm_summary_get_item_value($nickname){
	global $fm_display;
	$item = fm_summary_get_item($nickname);
	return $fm_display->currentFormData[$item['unique_name']];
}

function fm_summary_get_item($nickname){
	global $fmdb;
	global $fm_display;
	return $fmdb->getItemByNickname($fm_display->currentFormInfo['ID'], $nickname);
}

function fm_summary_get_form_info(){
	global $fm_display;
	return $fm_display->currentFormInfo;
}

function fm_summary_get_value($uniqueName){
	global $fm_display;
	return $fm_display->currentFormData[$uniqueName];
}

?>