<?php

class fm_textareaControl extends fm_controlBase{
	public function getTypeName(){ return "textarea"; }
	
	public function getTypeLabel(){ return "Text Area"; }
	
	public function showItem($uniqueName, $itemInfo){
		$elem=array('type' => 'textarea',
					'default' => $itemInfo['extra']['value'],
					'attributes' => array('name' => $uniqueName,
											'id'=> $uniqueName,
											'style' => "width:".$itemInfo['extra']['cols']."px;height:".$itemInfo['extra']['rows']."px;"
											)					
					);											
		return fe_getElementHTML($elem);
	}	
	
	public function itemDefaults(){
		$itemInfo = array();
		$itemInfo['label'] = "Item Label";
		$itemInfo['description'] = "Item Description";
		$itemInfo['extra'] = array('cols'=>'300', 'rows' => '100');
		$itemInfo['nickname'] = "Item Nickname";
		$itemInfo['required'] = 0;
		$itemInfo['validator'] = "";
		$ItemInfo['validation_msg'] = "";
		$itemInfo['db_type'] = "TEXT";
		
		return $itemInfo;
	}
	
	public function editItem($uniqueName, $itemInfo){
		$elem=array('type' => 'textarea',
					'default' => $itemInfo['extra']['value'],
					'attributes' => array('name' => $uniqueName."-edit-value",
											'id'=> $uniqueName."-edit-value",
											'rows'=> 2,
											'cols'=> 18,
											'readonly' => 'readonly'
											)
					);											
		return fe_getElementHTML($elem);
	}
	
	public function getPanelItems($uniqueName, $itemInfo){
		$arr=array();
		$arr[] = new fm_editPanelItemBase($uniqueName, 'label', 'Label', array('value' => $itemInfo['label']));
		$arr[] = new fm_editPanelItemBase($uniqueName, 'value', 'Default Value', array('value' => $itemInfo['extra']['value']));
		$arr[] = new fm_editPanelItemBase($uniqueName, 'rows', 'Height (in pixels)', array('value' => $itemInfo['extra']['rows']));
		$arr[] = new fm_editPanelItemBase($uniqueName, 'cols', 'Width (in pixels)', array('value' => $itemInfo['extra']['cols']));
		$arr[] = new fm_editPanelItemCheckbox($uniqueName, 'required', 'Required', array('checked'=>$itemInfo['required']));
		return $arr;
	}
	
	public function getPanelScriptOptions(){
		$opt = $this->getPanelScriptOptionDefaults();		
		$opt['extra'] = $this->extraScriptHelper(array('value'=>'value', 'rows'=>'rows', 'cols'=>'cols'));
		$opt['required'] = $this->checkboxScriptHelper('required');
		
		return $opt;
	}
	
	public function getShowHideCallbackName(){
		return "fm_textarea_show_hide";
	}
	
	public function getRequiredValidatorName(){ 
		return 'fm_base_required_validator';
	}

	protected function showExtraScripts(){
		?><script type="text/javascript">
		function fm_textarea_show_hide(itemID, isDone){
			if(isDone){
				document.getElementById(itemID + '-edit-label').innerHTML = document.getElementById(itemID + '-label').value;
				document.getElementById(itemID + '-edit-value').innerHTML = document.getElementById(itemID + '-value').value;
				if(document.getElementById(itemID + '-required').checked)
					document.getElementById(itemID + '-edit-required').innerHTML = "<em>*</em>";
				else
					document.getElementById(itemID + '-edit-required').innerHTML = "";
			}
		}
		</script>
		<?php
	}
	
	protected function getPanelKeys(){
		return array('label','required');
	}	
}

?>