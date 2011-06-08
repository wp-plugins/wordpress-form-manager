<?php
/**************************************************************/
/******* API **************************************************/

//takes a form's slug as a string, returns paginated 
function fm_doDataListBySlug($formSlug, $template, $orderBy = 'timestamp', $ord = 'DESC', $dataPerPage = 30){
	global $fmdb;
	
	parse_str($_SERVER['QUERY_STRING'], $queryVars);
	
	// make sure the slug is valid
	$formID = $fmdb->getFormID($formSlug);
	if($formID === false) return "(form ".(trim($formSlug)!=""?"'{$formSlug}' ":"")."not found".")";
	
	// see if 'orderby' is a valid unique name
	if($orderBy != 'timestamp' &&
		$orderBy != 'user' &&
		$orderBy != 'user_ip'){
		
		$orderByItem = $fmdb->getFormItem($orderBy);
		if($orderByItem === false) // not a valid unique name, but could be a nickname
			$orderByItem = $fmdb->getItemByNickname($formID, $orderBy);
		if($orderByItem === false) return "(orderby) ".$orderBy." not found";
		
		$orderBy = $orderByItem['unique_name'];
	}
	
	$currentPage = (isset($_REQUEST['fm-data-page']) ? $_REQUEST['fm-data-page'] : 0);
	$currentStartIndex = $currentPage * $dataPerPage;
	
	$submissionCount = $fmdb->getSubmissionDataCount($formID);
	$numPages = ceil($submissionCount / $dataPerPage);
	$pageLinkStr = "";
	
	$pageRoot = get_permalink();
	$pageRoot = substr($pageRoot, 0, strpos($pageRoot, "?"));
	
	// navigation 
	$pageLinkStr = "";
	if($numPages > 1){
		$pageLinkStr = "<p class=\"fm-data-nav\">";
		if($currentPage != 0)
			$pageLinkStr.= "<a href=\"".$pageRoot."?".http_build_query(array_merge($queryVars, array('fm-data-page' => ($currentPage - 1))))."\"><</a>&nbsp;";
		for($x=0;$x<$numPages;$x++){
			if($currentPage == $x)
				$pageLinkStr.= "<strong>".($x+1)."&nbsp;</strong>";
			else
				$pageLinkStr.= "<a href=\"".$pageRoot."?".http_build_query(array_merge($queryVars, array('fm-data-page' => $x)))."\">".($x+1)."</a>&nbsp;";		
		}
		if($currentPage != ($numPages - 1))
			$pageLinkStr.= "<a href=\"".$pageRoot."?".http_build_query(array_merge($queryVars, array('fm-data-page' => ($currentPage + 1))))."\">></a>&nbsp;";
		$pageLinkStr.= "</p>";
	}
	
	// summaries
	$summaries = fm_getFormDataSummaries($formID, $template, $orderBy, $ord, $currentStartIndex, $dataPerPage);
	$summaryListStr = '<p class="fm-data">'.implode('</p><p class="fm-data">', $summaries).'</p>';
	
	// put it all together
	return  $pageLinkStr.
			$summaryListStr.
			$pageLinkStr;
}

//takes a form's slug as a string, returns an array of strings containing formatted data summaries, using the 'summary' template.
function fm_getFormDataSummaries($formID, $template, $orderBy = 'timestamp', $ord = 'DESC', $startIndex = 0, $numItems = 30){
	global $fmdb;
	global $fm_display;
	
	$formInfo = $fmdb->getForm($formID);
	
	$formData = $fmdb->getFormSubmissionDataRaw($formID, $orderBy, strtoupper($ord), $startIndex, $numItems);
	
	
	$strArray = array();
	foreach($formData as $dataRow){
		$strArray[] = $fm_display->displayDataSummary($template, $formInfo, $dataRow);
	}
	
	return $strArray;	
}

function fm_getFormID($formSlug){
	global $fmdb;
	return $fmdb->getFormID($formSlug);
}

//takes a form's slug as a string.  It has the same behavior as using the shortcode.  Displays the form (according to the set behavior), processes posts, etc.
function fm_doFormBySlug($formSlug){
	global $fm_display;
	global $fmdb;
	global $current_user;
	global $fm_registered_user_only_msg;
		
	$formID = $fmdb->getFormID($formSlug);
	if($formID === false) return sprintf(__("(form  %s not found)", 'wordpress-form-manager'), (trim($formSlug)!=""?"'{$formSlug}' ":""));
	
	$output = "";
	
	//get and parse the form settings
	$formInfo = $fmdb->getForm($formID);
	$arr = explode(",", $formInfo['behaviors']);
	$formBehaviors = array();
	foreach($arr as $v){
		$formBehaviors[$v] = true;
	}
	
	$userDataCount = $fmdb->getUserSubmissionCount($formID, $current_user->user_login);
	
	if($_POST['fm_id'] == $formID && (wp_verify_nonce($_POST['fm_nonce'],'fm-nonce') && ($userDataCount == 0 || !isset($formBehaviors['single_submission'])))){
		// process the post
		get_currentuserinfo();	
		
		$overwrite = (isset($formBehaviors['display_summ']) || isset($formBehaviors['overwrite']));
		$postData = $fmdb->processPost($formID, array('user'=>$current_user->user_login, 'user_ip' => fm_get_user_IP()), $overwrite);			
		foreach($formInfo['items'] as $item){
			//if($item['type'] != 'file')
				$postData[$item['unique_name']] = stripslashes($postData[$item['unique_name']]);
		}
			
		if($fmdb->processFailed()){			
			return '<em>'.$fmdb->getErrorMessage().'</em>'.
					$fm_display->displayForm($formInfo, array('action' => get_permalink(), 'use_placeholders' => false), $postData);
		}
		else{
			// send email notifications
				
			if($formInfo['use_advanced_email'] != 1){
			
				$formInfo['email_list'] = trim($formInfo['email_list']) ;
				$formInfo['email_user_field'] = trim($formInfo['email_user_field']);		
					
				if($formInfo['email_list'] != ""
				|| $formInfo['email_user_field'] != "" 
				|| $fmdb->getGlobalSetting('email_admin') == "YES"
				|| $fmdb->getGlobalSetting('email_reg_users') == "YES"){
				
					$subject = get_option('blogname').": '".$formInfo['title']."' Submission";				
					$message = $fm_display->displayDataSummary('email', $formInfo, $postData);
					$headers  = 'MIME-Version: 1.0'."\r\n".
								'Content-type: text/html'."\r\n".
								'From: '.get_option('admin_email')."\r\n".
								'Reply-To: '.get_option('admin_email')."\r\n";
					
					$temp = "";
					if($fmdb->getGlobalSetting('email_admin') == "YES")
						wp_mail(get_option('admin_email'), $subject, $message, $headers);
						
					if($fmdb->getGlobalSetting('email_reg_users') == "YES"){
						if(trim($current_user->user_email) != "")
							wp_mail($current_user->user_email, $subject, $message, $headers);
					}
					if($formInfo['email_list'] != "")
						wp_mail($formInfo['email_list'], $subject, $message, $headers);
						
					if($formInfo['email_user_field'] != "")
						wp_mail($postData[$formInfo['email_user_field']], $subject, $message, $headers);
		
				}
			}else{
				//use the advanced e-mail settings 
				$advEmail = new fm_advanced_email_class($formInfo, $postData);

				$emails = $advEmail->generateEmails($formInfo['advanced_email']);
								
				foreach($emails as $email){
					$headerStr = "";
					foreach($email['headers'] as $header => $value)
						$headerStr.= $header.": ".$value."\r\n";
					wp_mail($email['to'], $email['subject'], $email['message'], $headerStr);
				}
			}
			
			//publish the submission as a post, if the form is set to do so
			if($formInfo['publish_post'] == 1){
				// Create post object
				$newPost = array(
					'post_title' => sprintf($formInfo['publish_post_title'], $formInfo['title']),
					'post_content' => $fm_display->displayDataSummary('summary', $formInfo, $postData),
					'post_status' => 'publish',
					'post_author' => 1,
					'post_category' => array($formInfo['publish_post_category'])
				);
				
				// Insert the post into the database
				$postID = wp_insert_post($newPost, false);
				if($postID != 0){					
					$fmdb->updateDataSubmissionRow($formInfo['ID'], $postData['timestamp'], $postData['user'], $postData['user_ip'], array('post_id' => $postID));
				}
			}			
			
			//display the acknowledgment of a successful submission
			$output.= '<p>'.$formInfo['submitted_msg'].'</p>';
			
			//show the automatic redirection script
			if($formInfo['auto_redirect']==1){
				$output.=	"<script language=\"javascript\"><!--\n".
							"setTimeout('location.replace(\"".get_permalink($formInfo['auto_redirect_page'])."\")', ".($formInfo['auto_redirect_timeout']*1000).");\n".
							"//-->\n".
							"</script>\n";
			}
			
			if(!isset($formBehaviors['display_summ']))
				return $output.
						($formInfo['show_summary']==1 ? $fm_display->displayDataSummary('summary', $formInfo, $postData) : "");
		}
	}
		
	//'reg_user_only', block unregistered users
	if(isset($formBehaviors['reg_user_only']) && $current_user->user_login == "") 
		return sprintf($fm_registered_user_only_msg, $formInfo['title']);
		
	//'display_summ', show previous submission if there is one and break
	
	if(isset($formBehaviors['display_summ'])){
		$userData = $fmdb->getUserSubmissions($formID, $current_user->user_login, true);
		
		if(sizeof($userData) > 0){		//only display a summary if there is a previous submission by this user
			if(!$_REQUEST['fm-edit-'.$formID] == '1'){							
				if(!isset($formBehaviors['edit']))
					return $output.$fm_display->displayDataSummary('summary', $formInfo, $postData);
				else{
					$currentPage = get_permalink();
					$parsedURL = parse_url($currentPage);
					if(trim($parsedURL['query']) == "")
						$editLink = $curentPage."?fm-edit-".$formID."=1";
					else
						$editLink = $currentPage."&fm-edit-".$formID."=1";
					
					return $output.
							$fm_display->displayDataSummary('summary', $formInfo, $userData[0]).
							"<span class=\"fm-data-summary-edit\"><a href=\"".$editLink."\">Edit '".$formInfo['title']."'</a></span>";
				}				
			}
			else
				return $output.$fm_display->displayForm($formInfo, array('action' => get_permalink(), 'use_placeholders' => false), $userData[0]);
		}
	}
	
	//if we got this far, just display the form
	return $fm_display->displayForm($formInfo, array('action' => get_permalink()));
}
?>