<?php

class fm_recaptchaControl extends fm_controlBase{
	
	var $err;
	
	public function getTypeName(){ return "recaptcha"; }
	
	public function getTypeLabel(){ return "reCAPTCHA"; }
	
	public function showItem($uniqueName, $itemInfo){
		global $fmdb;
		$publickey = $fmdb->getGlobalSetting('recaptcha_public'); 
		if($publickey == "") return "(No reCAPTCHA API public key found)";
		
		require_once('recaptcha/recaptchalib.php');		
		return recaptcha_get_html($publickey).
				(isset($_POST['recaptcha_challenge_field'])?"<br /> <em> The reCAPTCHA was incorrect. </em>":"");
	}	
	
	public function processPost($uniqueName, $itemInfo){
		global $fmdb;
		$publickey = $fmdb->getGlobalSetting('recaptcha_public'); 
		$privatekey = $fmdb->getGlobalSetting('recaptcha_private');
		if($privatekey == "" || $publickey == "" ) return "";
		
		require_once('recaptcha/recaptchalib.php');		
		$resp = recaptcha_check_answer ($privatekey,
									$_SERVER["REMOTE_ADDR"],
									$_POST["recaptcha_challenge_field"],
									$_POST["recaptcha_response_field"]);
		
		if (!$resp->is_valid) {
		// What happens when the CAPTCHA was entered incorrectly
		$this->err = $resp->error;
			return false;
		} 
		$this->err = false;
		return "";
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

	public function editItem($uniqueName, $itemInfo){
		global $fmdb;
		$publickey = $fmdb->getGlobalSetting('recaptcha_public');
		$privatekey = $fmdb->getGlobalSetting('recaptcha_private');
		if($publickey == "" || $privatekey == "") return "You need reCAPTCHA API keys. <br /> Fix this in <a href=\"".get_admin_url(null, 'admin.php')."?page=fm-global-settings\">Settings</a>.";
		return "(reCAPTCHA field)";
	}
	
	public function getPanelItems($uniqueName, $itemInfo){
		$arr=array();
		
		$arr[] = new fm_editPanelItemBase($uniqueName, 'label', 'Label', array('value' => $itemInfo['label']));
		return $arr;
	}
	
	public function getPanelScriptOptions(){
		$opt = $this->getPanelScriptOptionDefaults();		

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
			}
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