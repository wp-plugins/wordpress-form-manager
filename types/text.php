<?php

class fm_textControl extends fm_controlBase{
	var $validators;
	
	function __construct(){
		$this->validators = array();
	}
	public function getTypeName(){ return "text"; }
	
	public function getTypeLabel(){ return "Text"; }
	
	public function showItem($uniqueName, $itemInfo){
		$elem=array('type' => 'text',
					'attributes' => array('name' => $uniqueName,
											'id'=> $uniqueName,
											'value'=> htmlspecialchars($itemInfo['extra']['value']),
											'style' => "width:".$itemInfo['extra']['size']."px;"											
											)
					);											
		return fe_getElementHTML($elem);
	}	
	
	//returns an associative array keyed by the item db fields; used in the AJAX for creating a new form item in the back end / admin side
	public function itemDefaults(){
		$itemInfo = array();
		$itemInfo['label'] = "Item Label";
		$itemInfo['description'] = "Item Description";
		$itemInfo['extra'] = array('size' => '300');
		$itemInfo['nickname'] = "Item Nickname";
		$itemInfo['required'] = 0;
		$itemInfo['validator'] = "";
		$ItemInfo['validation_msg'] = "";
		$itemInfo['db_type'] = "TEXT";
		
		return $itemInfo;
	}
	
	public function editItem($uniqueName, $itemInfo){
		return "<input id=\"{$uniqueName}-edit-value\" type=\"text\" readonly=\"readonly\" value=\"".htmlspecialchars($itemInfo['extra']['value'])."\" />";
	}
	
	public function getPanelItems($uniqueName, $itemInfo){
		$arr=array();
		$arr[] = new fm_editPanelItemBase($uniqueName, 'label', 'Label', array('value' => $itemInfo['label']));
		$arr[] = new fm_editPanelItemBase($uniqueName, 'value', 'Default Value', array('value' => $itemInfo['extra']['value']));
		$arr[] = new fm_editPanelItemBase($uniqueName, 'size', 'Width (in pixels)', array('value' => $itemInfo['extra']['size']));
		$arr[] = new fm_editPanelItemCheckbox($uniqueName, 'required', 'Required', array('checked'=>$itemInfo['required']));
		$arr[] = new fm_editPanelItemDropdown($uniqueName, 'validation', 'Validation', array('options' => array_merge(array('none' => "..."), $this->getValidatorList()), 'value' => $itemInfo['extra']['validation']));		
		return $arr;
	}
	
	public function getPanelScriptOptions(){
		$opt = $this->getPanelScriptOptionDefaults();		
		$opt['extra'] = $this->extraScriptHelper(array('value'=>'value', 'size'=>'size', 'validation'=>'validation'));
		$opt['required'] = $this->checkboxScriptHelper('required');		
		return $opt;
	}
	
	public function getShowHideCallbackName(){
		return "fm_".$this->getTypeName()."_show_hide";
	}
	
	public function getRequiredValidatorName(){ 
		return 'fm_base_required_validator';
	}
	
	public function getGeneralValidatorName(){
		return 'fm_text_validation';	
	}
	
	public function getGeneralValidatorMessage($type){
		return $this->validators[$type]['message'];
	}
	
	protected function showExtraScripts(){
		?><script type="text/javascript">
		function fm_<?php echo $this->getTypeName(); ?>_show_hide(itemID, isDone){
			if(isDone){
				document.getElementById(itemID + '-edit-label').innerHTML = document.getElementById(itemID + '-label').value;
				document.getElementById(itemID + '-edit-value').value = document.getElementById(itemID + '-value').value;
			}
		}		
		</script>
		<?php
	}
	
	public function showUserScripts(){
		?><script type="text/javascript">
		function fm_text_validation(formID, itemID, valType){
			var itemValue = document.getElementById('fm-form-' + formID)[itemID].value.toString();
			if(fm_trim(itemValue) == "") return true;
			switch(valType){
				<?php foreach($this->validators as $val): ?>
				case "<?php echo $val['name'];?>":
					return itemValue.match(<?php echo $val['regexp'];?>);
				<?php endforeach; ?>
			}
			return false;
		}
		</script><?php
	}

	protected function getPanelKeys(){
		return array('label','required');
	}
	
	protected function getValidatorList(){
		$list = array();
		foreach($this->validators as $val){
			$list[$val['name']] = $val['label'];
		}
		return $list;
	}
	
	public function addValidator($name, $label, $message, $regexp){
		$this->validators[$name] = array('name' => $name, 'label' => $label, 'message' => $message, 'regexp' => $regexp);
	}
}

// HELPERS FOR VALIDATION

function fm_new_text_validator($name, $label, $message, $regexp){
	global $fm_controls;
	if(isset($fm_controls['text']))
		$fm_controls['text']->addValidator($name, $label, $message, $regexp);
}

?>