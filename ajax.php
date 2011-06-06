<?php

/**************************************************************/
/******* AJAX *************************************************/

//form editor 'save' button
add_action('wp_ajax_fm_save_form', 'fm_saveFormAjax');
global $fm_save_had_error;

function fm_saveFormAjax(){
	global $fmdb;
	global $fm_save_had_error;
	
	$fm_save_had_error = false;
	
	$formInfo = fm_saveHelperGatherFormInfo();
	
	//check if the shortcode is a duplicate
	$scID = $fmdb->getFormID($formInfo['shortcode']);
	if(!($scID == false || $scID == $_POST['id'] || trim($formInfo['shortcode']) == "")){
		//get the old shortcode
		$formInfo['shortcode'] = $fmdb->getFormShortcode($_POST['id']);			
		//save the rest of the form
		$fmdb->updateForm($_POST['id'], $formInfo);
		
		//now tell the user there was an error
		printf(__("Error: the shortcode '%s' is already in use. (other changes were saved successfully)", 'wordpress-form-manager'), $formInfo['shortcode']);
		
		die();
	}
			
	//no errors: save the form, return '1'
	$fmdb->updateForm($_POST['id'], $formInfo);
	
	if(!$fm_save_had_error)
		echo "1";
		
	die();
}

function fm_saveHelperGatherFormInfo(){
	global $fm_save_had_error;
	
	//collect the posted information
	$formInfo = array();
	$formInfo['title'] = $_POST['title'];
	$formInfo['labels_on_top'] = $_POST['labels_on_top'];
	$formInfo['submitted_msg'] = $_POST['submitted_msg'];
	$formInfo['submit_btn_text'] = $_POST['submit_btn_text'];
	$formInfo['show_title'] = ($_POST['show_title']=="true"?1:0);
	$formInfo['show_border'] = ($_POST['show_border']=="true"?1:0);
	$formInfo['shortcode'] = sanitize_title($_POST['shortcode']);
	$formInfo['label_width'] = $_POST['label_width'];
	$formInfo['required_msg'] = $_POST['required_msg'];
	$formInfo['template_values'] = $_POST['template_values'];	
	$formInfo['show_summary'] = ($_POST['show_summary']=="true"?1:0);
	$formInfo['email_user_field'] = $_POST['email_user_field'];	
	$formInfo['auto_redirect'] = ($_POST['auto_redirect']=="true"?1:0);
	$formInfo['auto_redirect_page'] = $_POST['auto_redirect_page'];
	$formInfo['auto_redirect_timeout'] = $_POST['auto_redirect_timeout'];
	
	//build the notification email list
	$emailList = explode(",", $_POST['email_list']);
	$valid = true;
	for($x=0;$x<sizeof($emailList);$x++){
		$emailList[$x] = trim($emailList[$x]);		
		if($emailList[$x] != "" && !preg_match("/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/", $emailList[$x])){
			$valid = false;
			$x = sizeof($emailList);
		}	
	}
		
	if($valid){
		$temp = array();
		foreach($emailList as $email)
			if($email != "") $temp[] = $email;
		$formInfo['email_list'] = implode(",", $temp);
	}
	else{
		/* translators: this error is given when saving the form, if there was a problem with the list of e-mails under 'E-Mail Notifications'. */
		_e("Error: There was a problem with the notification e-mail list.  Other settings were updated.", 'wordpress-form-manager');
		$fm_save_had_error = true;
	}
		
	//build the items list
	$formInfo['items'] = array();
	if(isset($_POST['items'])){
		foreach($_POST['items'] as $item){			
			if(!is_serialized($item['extra'])){ //if not a serialized array, hopefully a parseable php array definition..								
				$item['extra'] = stripslashes(stripslashes($item['extra'])); //both javascript and $_POST add slashes
				//make sure the code to be eval'ed is safe (otherwise this would be a serious security risk)
				if(is_valid_array_expr($item['extra']))				
					eval("\$newExtra = ".$item['extra'].";"); 				
				else{
					/* translators: This error occurs if the save script failed for some reason. */
					_e("Error: Save posted an invalid array expression.", 'wordpress-form-manager')."<br />";
					echo $item['extra'];
					die();
				}					
				$item['extra'] = $newExtra;
			}			
			$formInfo['items'][] = $item;			
		}
	}
	
	return $formInfo;
}

//insert a new form item
add_action('wp_ajax_fm_new_item', 'fm_newItemAjax');
function fm_newItemAjax(){
	global $fm_display;
	global $fmdb;
	
	$uniqueName = $fmdb->getUniqueItemID($_POST['type']);

	$str = "{".
		"'html':\"".addslashes(urlencode($fm_display->getEditorItem($uniqueName, $_POST['type'], null)))."\",".
		"'uniqueName':'".$uniqueName."'".
		"}";
	
	echo $str;
	
	die();
}

//Use the 'formelements' helpers
add_action('wp_ajax_fm_create_form_element', 'fm_createFormElement');
function fm_createFormElement(){
	//echo "<pre>".print_r($elem,true)."</pre>";
	echo fe_getElementHTML($_POST['elem']);
	die();
}

//Create a CSV file for download
add_action('wp_ajax_fm_create_csv', 'fm_createCSV');
function fm_createCSV(){
	global $fmdb;
	
	/* translators: the date format for creating the filename of a .csv file.  see http://php.net/date */
	$fname = $_POST['title']." (".date(__("m-y-d h-i-s", 'wordpress-form-manager')).").csv";
	
	$CSVFileFullPath = dirname(__FILE__)."/".get_option("fm-temp-dir")."/".sanitize_title($fname);
	
	$fmdb->writeFormSubmissionDataCSV($_POST['id'], $CSVFileFullPath);
	
	$fp = fopen(dirname(__FILE__)."/".get_option("fm-temp-dir")."/"."download.php", "w");	
	fwrite($fp, fm_createDownloadFileContents($CSVFileFullPath, $fname));	
	fclose($fp);
	
	echo plugins_url('/'.get_option("fm-temp-dir").'/',  __FILE__)."download.php";
	
	die();
}

function fm_createDownloadFileContents($localFileName, $downloadFileName){
	$str = "";
	
	$str.= "<?php\n";
	$str.= "header('Content-Disposition: attachment; filename=\"".$downloadFileName."\"');\n";
	$str.= "readfile('".$localFileName."');\n";
	$str.= "?>";
 
	return $str;
}

//Download an uploaded file
add_action('wp_ajax_fm_download_file', 'fm_downloadFile');
function fm_downloadFile(){
	global $fmdb;
	
	$tmpDir =  dirname(__FILE__)."/".get_option("fm-temp-dir")."/";
	
	$formID = $_POST['id'];
	$itemID = $_POST['itemid'];
	$timestamp = $_POST['timestamp'];
	$userName = $_POST['user'];
	
	$dataRow = $fmdb->getSubmission($formID, $timestamp, $userName, "`".$itemID."`");
	
	$fileInfo = unserialize($dataRow[$itemID]);	
	
	fm_createFileFromDB($fileInfo['filename'], $fileInfo, $tmpDir);
	
	echo plugins_url('/'.get_option("fm-temp-dir").'/', __FILE__).$fileInfo['filename'];		
	
	die();
}

add_action('wp_ajax_fm_download_all_files', 'fm_downloadAllFiles');
function fm_downloadAllFiles(){
	global $fmdb;
	global $fm_controls;
	
	$tmpDir =  dirname(__FILE__)."/".get_option("fm-temp-dir")."/";
	
	$formID = $_POST['id'];	
	$itemID = $_POST['itemid'];
	
	$formInfo = $fmdb->getForm($formID);
	
	foreach($formInfo['items'] as $item){
		if($item['unique_name'] == $itemID){
			$itemLabel = $item['label'];
			$fileItem = $item;
		}
	}
	
	$formData = $fmdb->getFormSubmissionDataRaw($formID, 'timestamp', 'DESC', 0, 0);
	$files = array();
	foreach($formData as $dataRow){
		$fileInfo = unserialize($dataRow[$itemID]);
		if(sizeof($fileInfo) > 1){		
			if(!isset($fileInfo['upload_dir'])){
				$fname = "(".$dataRow['timestamp'].") ".$fileInfo['filename'];
				$files[] = $tmpDir.$fname;
				fm_createFileFromDB($fname, $fileInfo, $tmpDir);
			}
			else{
				$files[] = $fm_controls['file']->parseUploadDir($fileItem['extra']['upload_dir']).$fileInfo['filename'];
			}
		}
	}
	
	if(sizeof($files) > 0){
	
		$zipFileName = $formInfo['title']." - ".$itemLabel.".zip";
		$zipFullPath =  $tmpDir.sanitize_title($zipFileName);	
		fm_createZIP($files, $zipFullPath); 
		 
		$fp = fopen(dirname(__FILE__)."/".get_option("fm-temp-dir")."/"."download.php", "w");	
		fwrite($fp, fm_createDownloadFileContents($zipFullPath, $zipFileName));	
		fclose($fp); 
		
		echo plugins_url('/'.get_option("fm-temp-dir").'/', __FILE__)."download.php";
		die();
	}
	else{
		die();
	}
	die();
}

function fm_createFileFromDB($filename, $fileInfo, $dir){
	$fullpath = $dir.$filename;
	$fp = @fopen($fullpath,'wb') or die(__("Failed to open file", 'wordpress-form-manager'));
	fwrite($fp, $fileInfo['contents']);
	fclose($fp);
}

/* Below is from David Walsh (davidwalsh.name), slightly modified. Thanks Dave! */
function fm_createZIP($files = array(),$destination = '') {
   
  //vars
  $valid_files = array();
  //if files were passed in...
  if(is_array($files)) {
    //cycle through each file
    foreach($files as $file) {
      //make sure the file exists
      if(file_exists($file)) {
        $valid_files[] = $file;
      }
    }
  }
  //if we have good files...
  if(count($valid_files)) {
    //create the archive
    $zip = new ZipArchive();
    if($zip->open($destination, ZIPARCHIVE::OVERWRITE | ZIPARCHIVE::CREATE | ZIPARCHIVE::FL_NODIR) !== true) {
	  return false;
    }
    //add the files
    foreach($valid_files as $file) {
      $zip->addFile($file,basename($file));
    }
    //debug
    //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
    
    //close the zip -- done!
    $zip->close();
    
    //check to make sure the file exists
	return file_exists($destination);
  }
  else
  { 
    return false;
  }
}
?>