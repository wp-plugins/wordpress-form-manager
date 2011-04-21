<?php
include 'types.php';

class fm_display_class{


///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////

//options:
//	class - 'class' attribute for the <form> tag
//	action - 'action' attribute for the <form> tag
//'params' is an associative array of hidden values inserted into the form
function displayForm($formInfo, $options=array(), $values=array()){
	global $msg;
	global $fmdb;
	global $fm_controls;	
	
	$validation_required = array();
	
	$formLabelWidth = (trim($formInfo['label_width']) == "")?"150":$formInfo['label_width'];
	
	//default div id
	if(!isset($options['class'])) $options['class'] = 'fm-form';
	
	$str = "";
	$str.= "<form class=\"".$options['class']."\" method=\"post\" action=\"".$options['action']."\" name=\"fm-form-".$formInfo['ID']."\" id=\"fm-form-".$formInfo['ID']."\">\n";
	
	if($formInfo['show_border']==1)
		$str.= "<fieldset>\n";
	
	if($formInfo['show_title']==1)
		if($formInfo['show_border']==1)
			$str.= "<legend>".$formInfo['title']."</legend>\n";
		else
			$str.= "<h3>".$formInfo['title']."</h3>\n";
	
	$str.= "<ul>\n";
	
		foreach($formInfo['items'] as $item){
			
			//if override $item['extra']['value'] if the unique_name is in $values
			if(isset($values[$item['unique_name']]))
				$item['extra']['value'] = $values[$item['unique_name']];
			
			$str.= "<li class=\"".$item['type']."\">";
			
			////////////////////////////////////////////////////////////////////////////////////////
			
			if(($formInfo['labels_on_top']==1 && $item['type'] != 'checkbox') 
				|| $item['type'] == 'separator' 
				|| ($item['type'] == 'note' && trim($item['label']) == ""))
			{
				$str.='<label>'.$item['label'].'</label>';
				if($item['required']=='1')	$str.= '<em>*</em>';
				$str.='<br />';
				$str.=$fm_controls[$item['type']]->showItem($item['unique_name'], $item);
			}
			else{
				$str.='<table><tr>';
				$str.='<td style="width:'.$formLabelWidth.'px"><label>'.$item['label'].'</label>';
				if($item['required']=='1')	$str.= '<em>*</em>';
				$str.='</td>';
				$str.='<td>'.$fm_controls[$item['type']]->showItem($item['unique_name'], $item).'</td>';
				$str.='</tr></table>';			
			}
						
			////////////////////////////////////////////////////////////////////////////////////////
			
			$str.= "</li>\n";
		}
	
	$str.= "</ul>\n";
	
	///// show the submit button //////
	$str.= "<input type=\"submit\" ".
			"name=\"fm_form_submit\" ".
			"class=\"submit\" ".
			"value=\"".$formInfo['submit_btn_text']."\" ".
			"onclick=\"return fm_validate(".$formInfo['ID'].")\" ".
			" />\n";
	
	if($formInfo['show_border']==1)	
		$str.= "</fieldset>\n";		
	
	//// echo the nonce ////	
	$str.= "<input type=\"hidden\" name=\"fm_nonce\" value=\"".wp_create_nonce('fm-nonce')."\" />\n";	
	$str.= "<input type=\"hidden\" name=\"fm_id\" value=\"".$formInfo['ID']."\" />\n";
	
	$str.= "</form>\n";
	
	
	////// show the validation scripts /////
	$str.="<!-- validation -->\n";
	$str.="<script type=\"text/javascript\">\n";
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
	
	foreach($fm_controls as $control){
		
	}	
	
	$str.="</script>\n";
	$str.="<!-- /validation -->\n";
	return $str;
}
///////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////

function displayDataSummary($formInfo, $data, $before = "", $after = "", $userAndTimestamp = false){
	global $fm_controls;
	
	$str = "";
	$str.= "<div class=\"fm-data-summary\">\n";
	$str.= $before;
	$str.= "<ul>\n";
	if($userAndTimestamp){
		$str.= "<li><span class=\"label\">User:&nbsp;&nbsp;</span><span class=\"value\">".$data['user']."</span></li>\n";
		$str.= "<li><span class=\"label\">Timestamp:&nbsp;&nbsp;</span><span class=\"value\">".$data['timestamp']."</span></li>\n";
	}
	foreach($formInfo['items'] as $item){
		if($item['db_type'] != "NONE")
			$str.= "<li><span class=\"label\">".$item['label'].":&nbsp;&nbsp;</span><span class=\"value\">".$fm_controls[$item['type']]->parseData($item['unique_name'], $item, $data[$item['unique_name']])."</span></li>\n";
	}
	$str.= "</ul>\n";
	$str.= $after;
	$str.= "</div>";
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
?>