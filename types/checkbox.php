<?php

class fm_checkboxControl extends fm_controlBase{
	public function getTypeName(){ return "checkbox"; }
		
	public function getTypeLabel(){ return "Checkbox"; }
	
	public function showItem($uniqueName, $itemInfo){
		$elem=array('type' => 'checkbox',
					'attributes' => array('name' => $uniqueName,
											'id'=> $uniqueName											
											),
					'checked'=> $itemInfo['extra']['value']
					);											
		return fe_getElementHTML($elem);
	}	
	
	public function processPost($uniqueName, $itemInfo){
		if(isset($_POST[$uniqueName]))
			return $_POST[$uniqueName]=="on"?"yes":"no";
		return "no";
	}
	
	public function editItem($uniqueName, $itemInfo){
		return "<input id=\"{$uniqueName}-edit-value\" type=\"checkbox\" disabled ".$itemInfo['extra']['value']." />";
	}
	
	public function getPanelItems($uniqueName, $itemInfo){
		$arr=array();
		$arr[] = new fm_editPanelItemBase($uniqueName, 'label', 'Label', array('value' => $itemInfo['label']));
		$arr[] = new fm_editPanelItemCheckbox($uniqueName, 'value', 'Checked by Default', array('checked'=>$itemInfo['extra']['value']));
		$arr[] = new fm_editPanelItemCheckbox($uniqueName, 'required', 'Required', array('checked'=>$itemInfo['required']));
		return $arr;
	}
	
	public function getPanelScriptOptions(){
		$opt = $this->getPanelScriptOptionDefaults();		
		$opt['extra'] = "\"array('value' => '\" + ".$this->checkboxScriptHelper('value',array('onValue'=>'checked', 'offValue'=>""))." + \"')\"";
		$opt['required'] = $this->checkboxScriptHelper('required');
		return $opt;
	}
	
	public function getShowHideCallbackName(){
		return "fm_".$this->getTypeName()."_show_hide";
	}
	
	protected function showExtraScripts(){
		?><script type="text/javascript">
		function fm_<?php echo $this->getTypeName(); ?>_show_hide(itemID, isDone){
			if(isDone){
				document.getElementById(itemID + '-edit-label').innerHTML = document.getElementById(itemID + '-label').value;
				document.getElementById(itemID + '-edit-value').checked = document.getElementById(itemID + '-value').checked;
			}
		}
		</script>
		<?php
	}
	
	public function getRequiredValidatorName(){ 
		return "fm_checkbox_required_validator";
	}	
	public function showUserScripts(){		
		?>
		<script type="text/javascript">
		function fm_checkbox_required_validator(formID, itemID){
			return document.getElementById('fm-form-' + formID)[itemID].checked;
		}
		</script>
		<?php
	}
		
	protected function getPanelKeys(){
		return array('label', 'required');
	}	
}

?>