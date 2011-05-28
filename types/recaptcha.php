<?php
/* translators: the following are recaptcha element settings */

class fm_recaptchaControl extends fm_controlBase{
	
	var $err;
	
	public function getTypeName(){ return "recaptcha"; }
	
	/* translators: this appears in the 'Add Form Element' menu */
	public function getTypeLabel(){ return __("reCAPTCHA", 'wordpress-form-manager'); }
	
	public function showItem($uniqueName, $itemInfo){
		global $fmdb;
		$publickey = $fmdb->getGlobalSetting('recaptcha_public'); 
		if($publickey == "") return __("(No reCAPTCHA API public key found)", 'wordpress-form-manager');
		
		if(!function_exists('recaptcha_get_html'))
			require_once('recaptcha/recaptchalib.php');		
			
		return "<script type=\"text/javascript\"> var RecaptchaOptions = { theme : '".$fmdb->getGlobalSetting('recaptcha_theme')."' }; </script>".
				recaptcha_get_html($publickey).
				(isset($_POST['recaptcha_challenge_field'])?"<br /> <em> ".__("The reCAPTCHA was incorrect.", 'wordpress-form-manager')." </em>":"");
	}	
	
	public function processPost($uniqueName, $itemInfo){
		global $fmdb;
		$publickey = $fmdb->getGlobalSetting('recaptcha_public'); 
		$privatekey = $fmdb->getGlobalSetting('recaptcha_private');
		if($privatekey == "" || $publickey == "" ) return "";
		
		if(!function_exists('recaptcha_check_answer'))			
			require_once('recaptcha/recaptchalib.php');		
			
		$resp = recaptcha_check_answer ($privatekey,
									$_SERVER["REMOTE_ADDR"],
									$_POST["recaptcha_challenge_field"],
									$_POST["recaptcha_response_field"]);
		
		if (!$resp->is_valid === false) {
			// What happens when the CAPTCHA was entered incorrectly
			$this->err = $resp->error;
				return false;
		} 
		$this->err = false;
		return "";
	}
	
	public function itemDefaults(){
		$itemInfo = array();
		$itemInfo['label'] = "New reCAPTCHA";
		$itemInfo['description'] = "Item Description";
		$itemInfo['extra'] = array();
		$itemInfo['nickname'] = '';
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
		if($publickey == "" || $privatekey == "") return __("You need reCAPTCHA API keys.", 'wordpress-form-manager')." <br /> ".__("Fix this in", 'wordpress-form-manager')." <a href=\"".get_admin_url(null, 'admin.php')."?page=fm-global-settings\">".__("Settings", 'wordpress-form-manager')."</a>.";
		return __("(reCAPTCHA field)", 'wordpress-form-manager');
	}
	
	public function getPanelItems($uniqueName, $itemInfo){
		$arr=array();		
		$arr[] = new fm_editPanelItemBase($uniqueName, 'label', __('Label', 'wordpress-form-manager'), array('value' => $itemInfo['label']));
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