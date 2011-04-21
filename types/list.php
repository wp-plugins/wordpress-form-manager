<?php
class fm_customListControl extends fm_controlBase{
	
	public function getTypeName(){
		return "custom_list";
	}
	
	public function getTypeLabel(){
		return "List";
	}		
	
	public function itemDefaults(){
		$itemInfo = array();
		$itemInfo['label'] = "Item Label";
		$itemInfo['description'] = "Item Description";
		$itemInfo['extra'] = array('list_type' => 'select');
		$itemInfo['nickname'] = "Item Nickname";
		$itemInfo['required'] = 0;
		$itemInfo['validator'] = "";
		$ItemInfo['validation_msg'] = "";
		$itemInfo['db_type'] = "TEXT";
		
		return $itemInfo;
	}
	
	public function showItem($uniqueName, $itemInfo){
		$fn = $itemInfo['extra']['list_type']."_showItem";
		return $this->$fn($uniqueName, $itemInfo).
				"<input type=\"hidden\" id=\"".$uniqueName."-list-type\" value=\"".$itemInfo['extra']['list_type']."\" />".
				"<input type=\"hidden\" id=\"".$uniqueName."-count\" value=\"".sizeof($itemInfo['extra']['options'])."\" />";
		
	}
		public function select_showItem($uniqueName, $itemInfo, $disabled = false){
			$elem=array('type' => 'select',
						'attributes' => array('name' => $uniqueName,
												'id'=> $uniqueName,																					
												'style' => "width:".$itemInfo['extra']['size']."px;"
											),
						'value' => $itemInfo['extra']['value'],	
						'options' => $itemInfo['extra']['options']
						);			
			if($itemInfo['required'] == "1")
				$elem['options'] = array_merge(array('-1' => "..."), $elem['options']);
			if($disabled)
				$elem['attributes']['disabled'] = 'disabled';				
			return fe_getElementHTML($elem);
		}	
		public function list_showItem($uniqueName, $itemInfo, $disabled = false){
			$elem=array('type' => 'select',
						'attributes' => array('name' => $uniqueName,
												'id'=> $uniqueName,																					
												'style' => "width:".$itemInfo['extra']['size']."px;",
												'size' => sizeof($itemInfo['extra']['options'])
											),
						'value' => $itemInfo['extra']['value'],	
						'options' => $itemInfo['extra']['options']
						);
			if($itemInfo['required'] == "1")
				$elem['options'] = array_merge(array('-1' => "..."), $elem['options']);
			if($disabled)
				$elem['attributes']['disabled'] = 'disabled';								
			return fe_getElementHTML($elem);
		}
		public function radio_showItem($uniqueName, $itemInfo, $disabled = false){
			$elem=array('type' => 'radio',
						'attributes' => array('name' => $uniqueName,
												'id'=> $uniqueName
											),
						'separator' => '<br>',
						'options' => $itemInfo['extra']['options'],
						'value' => $itemInfo['extra']['value']
						);	
			if($disabled)
				$elem['attributes']['disabled'] = 'disabled';										
			return fe_getElementHTML($elem);
		}
		public function checkbox_showItem($uniqueName, $itemInfo, $disabled = false){
			$elem=array('type' => 'checkbox_list',						
						'separator' => '<br>',
						'value' => $itemInfo['extra']['value']
						);
			$elem['options'] = array();
			for($x=0;$x<sizeof($itemInfo['extra']['options']);$x++)
				$elem['options'][$uniqueName."-".$x] = $itemInfo['extra']['options'][$x];
			if($disabled)
				$elem['attributes']['disabled'] = 'disabled';
			return '<div class="fm-checkbox-list">'.fe_getElementHTML($elem).'</div>';
		}
		
		
	public function editItem($uniqueName, $itemInfo){	
		$fn = $itemInfo['extra']['list_type']."_showItem";
		unset($itemInfo['extra']['size']);
		return $this->$fn($uniqueName, $itemInfo, true);
	}
	
	public function processPost($uniqueName, $itemInfo){
		$fn = $itemInfo['extra']['list_type']."_processPost";
		return $this->$fn($uniqueName, $itemInfo);
	}
		public function select_processPost($uniqueName, $itemInfo){
			if(isset($_POST[$uniqueName])){
				if($itemInfo['required'] == "1")
					return addslashes($itemInfo['extra']['options'][$_POST[$uniqueName]-1]);
				else
					return addslashes($itemInfo['extra']['options'][$_POST[$uniqueName]]);			
			}
			return "";
		}
		public function list_processPost($uniqueName, $itemInfo){
			return $this->select_processPost($uniqueName, $itemInfo);
		}
		public function radio_processPost($uniqueName, $itemInfo){
			if(isset($_POST[$uniqueName]))
				return addslashes($itemInfo['extra']['options'][$_POST[$uniqueName]]);
			return "";
		}
		public function checkbox_processPost($uniqueName, $itemInfo){
			$arr=array();
			for($x=0;$x<sizeof($itemInfo['extra']['options']);$x++){
				if(isset($_POST[$uniqueName."-".$x]))
					$arr[] = ($_POST[$uniqueName."-".$x]=="on"?$itemInfo['extra']['options'][$x]:"");
			}
			return implode(", ", $arr);
		}
		
	public function getPanelItems($uniqueName, $itemInfo){
		$arr=array();		
		$arr[] = new fm_editPanelItemBase($uniqueName, 'label', 'Label', array('value' => $itemInfo['label']));
		$arr[] = new fm_editPanelItemDropdown($uniqueName, 'list_type', 'Style', array('options' => array('select' => "Dropdown", 'list' => "List Box", 'radio' => "Buttons", 'checkbox' => "Checkboxes"), 'value' => $itemInfo['extra']['list_type']));
		$arr[] = new fm_editPanelItemBase($uniqueName, 'size', 'Width (in pixels)', array('value' => $itemInfo['extra']['size']));
		$arr[] = new fm_editPanelItemCheckbox($uniqueName, 'required', 'Required', array('checked'=>$itemInfo['required']));
		$arr[] = new fm_editPanelItemMulti($uniqueName, 'options', 'List Items', array('options' => $itemInfo['extra']['options'], 'get_item_script' => 'fm_custom_list_options_panel_item'));
		return $arr;
	}
	
	public function getPanelScriptOptions(){
		$opt = $this->getPanelScriptOptionDefaults();		
		$opt['extra'] = "\"array('options' => \" + js_multi_item_get_php_array('multi-panel-' + itemID, 'fm_custom_list_option_get') + \", 'size' => '\" + fm_get_item_value(itemID, 'size') + \"', 'list_type' => '\" + fm_get_item_value(itemID, 'list_type') + \"')\"";
		$opt['required'] = $this->checkboxScriptHelper('required');
		
		return $opt;
	}
		
	//called when displaying the user form; used for validation scripts, etc.
	public function showUserScripts(){		
		?>
		<script type="text/javascript">
		function fm_custom_list_required_validator(formID, itemID){
			var listType = document.getElementById('fm-form-' + formID)[itemID + '-list-type'].value;
			switch(listType){
				case "radio":
					return fm_radio_list_required_validator(formID, itemID);
				case "checkbox": 
					return fm_checkbox_list_required_validator(formID, itemID);
				default:
					return fm_select_list_required_validator(formID, itemID);
			}
			return false;
		}
		function fm_select_list_required_validator(formID, itemID){			
			return (document.getElementById('fm-form-' + formID)[itemID].value != 0);
		}
		function fm_radio_list_required_validator(formID, itemID){	
			var radioList = document.getElementById('fm-form-' + formID)[itemID];
			for(var x=0;x<radioList.length;x++)
				if(radioList[x].checked == true) return true;		
			return false;
		}
		function fm_checkbox_list_required_validator(formID, itemID){
			var count = document.getElementById('fm-form-' + formID)[itemID + '-count'].value;
			for(var x=0;x<count;x++){
				if(document.getElementById('fm-form-' + formID)[itemID + '-' + x].checked) return true;
			}
			return false;
		}
		</script>
		<?php		
	}
	
	//called when displaying a required form item in the user form; returns the name of a javascript function that should return 'true' only if the input is not blank
	public function getRequiredValidatorName(){ 
		return "fm_custom_list_required_validator";
	}
	
	protected function showExtraScripts(){
		?><script type="text/javascript">
		function fm_custom_list_show_hide(itemID, isDone){
			if(isDone){				
				document.getElementById(itemID + '-edit-label').innerHTML = document.getElementById(itemID + '-label').value;
				//document.getElementById(itemID + '-edit-value').options[0].text = js_multi_item_get_index('multi-panel-' + itemID, 'fm_custom_list_option_get', 0);							
			}
		}
		function fm_custom_list_options_panel_item(itemID, optionID, optValue){
			return "<input id=\"" + optionID + "-text\" type=\"text\" value=\"" + fm_htmlEntities(optValue) + "\" style=\"width:100px;\"/>";
		}
		function fm_custom_list_option_get(optionID){
			var textInput = document.getElementById(optionID + "-text");
			return textInput.value;
		}
		</script>
		<?php
	}
	public function getShowHideCallbackName(){
		return "fm_custom_list_show_hide";
	}
	
	protected function getPanelKeys(){
		return array('label','required');
	}
}