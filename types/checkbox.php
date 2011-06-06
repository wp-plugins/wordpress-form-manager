<?php
/* translators: checkbox element settings */

class fm_checkboxControl extends fm_controlBase{
	public function getTypeName(){ return "checkbox"; }
	
	/* translators: this appears in the 'Add Form Element' menu */
	public function getTypeLabel(){ return __("Checkbox", 'wordpress-form-manager'); }
	
	public function itemDefaults(){
		$itemInfo = array();
		$itemInfo['label'] = __("New Checkbox", 'wordpress-form-manager');
		$itemInfo['description'] = __("Item Description", 'wordpress-form-manager');
		$itemInfo['extra'] = array();
		$itemInfo['nickname'] = '';
		$itemInfo['required'] = 0;
		$itemInfo['validator'] = "";
		$ItemInfo['validation_msg'] = "";
		$itemInfo['db_type'] = "VARCHAR( 10 )";
		
		return $itemInfo;
	}
	
	public function showItem($uniqueName, $itemInfo){
		$elem=array('type' => 'checkbox',
					'attributes' => array('name' => $uniqueName,
											'id'=> $uniqueName,
											'style'=> ($itemInfo['extra']['position'] == "right" ? "float:right;" : "")
											),
					'checked'=> ($itemInfo['extra']['value']=='checked')				
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
		$arr[] = new fm_editPanelItemBase($uniqueName, 'label', __('Label', 'wordpress-form-manager'), array('value' => $itemInfo['label']));
		$arr[] = new fm_editPanelItemDropdown($uniqueName, 'position', __('Position', 'wordpress-form-manager'), array('options' => array('left' => __("Left", 'wordpress-form-manager'), 'right' => __("Right", 'wordpress-form-manager')), 'value' => $itemInfo['extra']['position']));
		$arr[] = new fm_editPanelItemCheckbox($uniqueName, 'value', __('Checked by Default', 'wordpress-form-manager'), array('checked'=>($itemInfo['extra']['value']=='checked')));
		$arr[] = new fm_editPanelItemCheckbox($uniqueName, 'required', __('Required', 'wordpress-form-manager'), array('checked'=>$itemInfo['required']));
		return $arr;
	}
	
	public function getPanelScriptOptions(){
		$opt = $this->getPanelScriptOptionDefaults();		
		$opt['extra'] = "\"array('value' => '\" + ".$this->checkboxScriptHelper('value',array('onValue'=>'checked', 'offValue'=>""))." + \"', 'position' => '\" + fm_fix_str(fm_get_item_value(itemID, 'position')) + \"')\"";
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
				if(document.getElementById(itemID + '-required').checked)
					document.getElementById(itemID + '-edit-required').innerHTML = "<em>*</em>";
				else
					document.getElementById(itemID + '-edit-required').innerHTML = "";
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