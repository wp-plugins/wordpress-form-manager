<?php

class fm_fileControl extends fm_controlBase{
	
	public function getTypeName(){ return "file"; }
	
	public function getTypeLabel(){ return "File"; }
	
	public function showItem($uniqueName, $itemInfo){
		return "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"".($itemInfo['extra']['max_size']*1024)."\" />".
				"<input name=\"".$uniqueName."\" id=\"".$uniqueName."\" type=\"file\" />";
	}	

	public function itemDefaults(){
		$itemInfo = array();
		$itemInfo['label'] = "New File Upload";
		$itemInfo['description'] = "Item Description";
		$itemInfo['extra'] = array('max_size' => 1000);
		$itemInfo['nickname'] = "Item Nickname";
		$itemInfo['required'] = 0;
		$itemInfo['validator'] = "";
		$ItemInfo['validation_msg'] = "";
		$itemInfo['db_type'] = "LONGBLOB";
		
		return $itemInfo;
	}

	public function editItem($uniqueName, $itemInfo){
		return "<input type=\"file\" disabled>";
	}
	
	public function processPost($uniqueName, $itemInfo){
		global $fmdb;
		if($_FILES[$uniqueName]['error'] > 0){
			if($_FILES[$uniqueName]['error'] == 2)
				$fmdb->setErrorMessage("(".$itemInfo['label'].") File upload exceeded maximum allowable size.");
			else
				$fmdb->setErrorMessage("(".$itemInfo['label'].") There was an error with the file upload.");
			return false;
		}
		
		$ext = pathinfo($_FILES[$uniqueName]['name'], PATHINFO_EXTENSION);
		if(strpos($itemInfo['extra']['exclude'], $ext) !== false){
			$fmdb->setErrorMessage("(".$itemInfo['label'].") Cannot be of type '.".$ext."'");
			return false;
		}
		else if(trim($itemInfo['extra']['restrict'] != "") && strpos($itemInfo['extra']['restrict'], $ext) === false){
			$fmdb->setErrorMessage("(".$itemInfo['label'].") Can only be of types ".$itemInfo['extra']['restrict']);
			return false;
		}
			
				
		$filename = $_FILES[$uniqueName]['tmp_name'];			
		$handle = fopen($filename, "rb");
		$contents = fread($handle, filesize($filename));
		fclose($handle);
		
		$saveVal = array('filename' => basename($_FILES[$uniqueName]['name']),
							'contents' => $contents,
							'size' => $_FILES[$uniqueName]['size']);
		return addslashes(serialize($saveVal));
		
	}
	
	public function parseData($uniqueName, $itemInfo, $data){
		$fileInfo = unserialize($data);
		return $fileInfo['filename']." (".((int)($fileInfo['size']/1024))." kB)";
	}
	
	public function getPanelItems($uniqueName, $itemInfo){
		$arr=array();
		
		$arr[] = new fm_editPanelItemBase($uniqueName, 'label', 'Label', array('value' => $itemInfo['label']));
		$arr[] = new fm_editPanelItemBase($uniqueName, 'max_size', 'Max file size (in kB)', array('value' => $itemInfo['extra']['max_size']));
		$arr[] = new fm_editPanelItemNote($uniqueName, '', "<span class=\"fm-small\" style=\"padding-bottom:10px;\">Your host restricts uploads to ".ini_get('upload_max_filesize')."B</span>", '');
		$arr[] = new fm_editPanelItemNote($uniqueName, '', "<span style=\"padding-:10px;font-weight:bold;\">File Types</span>", '');
		$arr[] = new fm_editPanelItemNote($uniqueName, '', "<span class=\"fm-small\" style=\"padding-bottom:10px;\">Enter a list of extensions separated by commas, e.g. \".txt, .rtf, .doc\"</span>", '');
		$arr[] = new fm_editPanelItemBase($uniqueName, 'restrict', 'Only allow', array('value' => $itemInfo['extra']['restrict']));		
		$arr[] = new fm_editPanelItemBase($uniqueName, 'exclude', 'Do not allow', array('value' => $itemInfo['extra']['exclude']));
		
	
		return $arr;
	}
	
	public function getPanelScriptOptions(){
		$opt = $this->getPanelScriptOptionDefaults();		
		$opt['extra'] = $this->extraScriptHelper(array('restrict' => 'restrict', 'exclude' => 'exclude', 'max_size' => 'max_size'));
		return $opt;
	}
	
	public function getShowHideCallbackName(){
		return "fm_".$this->getTypeName()."_show_hide";
	}
	
	public function getSaveValidatorName(){
		return "fm_file_save_validator";
	}
	
	protected function showExtraScripts(){
		?><script type="text/javascript">
		function fm_<?php echo $this->getTypeName(); ?>_show_hide(itemID, isDone){
			if(isDone){
				document.getElementById(itemID + '-edit-label').innerHTML = document.getElementById(itemID + '-label').value;
							
			}
		}		
		
		function fm_file_save_validator(itemID){
			var itemLabel = document.getElementById(itemID + '-label').value;
			var restrictExtensions = document.getElementById(itemID + '-restrict').value.toString();
			var excludeExtensions = document.getElementById(itemID + '-restrict').value.toString();
				
			if(!restrictExtensions.match(/^(\s*\.[a-zA-Z]+\s*)?(,\s*\.[a-zA-Z]+\s*)*$/)){
				alert(itemLabel + ": 'Only allow' must be a list of extensions separated by commas");
				return false;
			}
			if(!excludeExtensions.match(/^(\s*\.[a-zA-Z]+\s*)?(,\s*\.[a-zA-Z]+\s*)*$/)){
				alert(itemLabel + ": 'Do not allow' must be a list of extensions separated by commas");
				return false;
			}
			
			return true;
		}
		</script>
		<?php
	}
	
	public function showUserScripts(){
		
	}

	protected function getPanelKeys(){
		return array('label');
	}
}
?>