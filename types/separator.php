<?php
class fm_separatorControl extends fm_controlBase{
	public function getTypeName(){ return "separator"; }
	
	public function getTypeLabel(){ return "Separator"; }
	
	public function showItem($uniqueName, $itemInfo){ return "<hr />"; }
	
	public function editItem($uniqueName, $itemInfo){ return "<hr />"; }
	
	public function getPanelItems($uniqueName, $itemInfo){
		$arr=array();
		$arr[] = new fm_editPanelItemBase($uniqueName, 'label', 'Label', array('value' => $itemInfo['label']));
		return $arr;
	}
	
	public function getPanelKeys(){
		return array('label');
	}
	
	public function getShowHideCallbackName(){
		return "fm_sep_show_hide";
	}
	
	protected function showExtraScripts(){
		?><script type="text/javascript">
		function fm_sep_show_hide(itemID, isDone){
			if(isDone){
				document.getElementById(itemID + '-edit-label').innerHTML = document.getElementById(itemID + '-label').value;
			}
		}
		</script>
		<?php
	}
	
	public function itemDefaults(){
		$itemInfo = array();
		$itemInfo['label'] = "Item Label";
		$itemInfo['description'] = "Item Description";
		$itemInfo['extra'] = array();
		$itemInfo['nickname'] = "Item Nickname";
		$itemInfo['required'] = 0;
		$itemInfo['validator'] = "";
		$ItemInfo['validation_msg'] = "";
		$itemInfo['db_type'] = "NONE";
		
		return $itemInfo;
	}
}
?>