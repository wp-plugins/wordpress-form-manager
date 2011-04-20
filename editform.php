<?php
global $fmdb;
global $fm_display;
global $fm_controls;

$form = null;
if($_REQUEST['id']!="")
	$form = $fmdb->getForm($_REQUEST['id']);
	
$formList = $fmdb->getFormList();

/// LOAD FIELDS //////////////////////////////////////////

if(isset($_POST['load-fields'])){
	$loadedForm = $fmdb->copyForm($_POST['load-fields-id']);
	if($_POST['load-fields-insert-after'] == "0"){  //insert at beginning		
		$temp = $form['items'];
		$form['items'] = $loadedForm['items'];
		foreach($temp as $item)
			$form['items'][] = $item;
	}
	else if($_POST['load-fields-insert-after'] == "1"){  //insert at end
		foreach($loadedForm['items'] as $item)
			$form['items'][] = $item;
	}
	else{		
		$temp = array();
		foreach($form['items'] as $oldItem){
			$temp[] = $oldItem;
			if($oldItem['unique_name'] == $_POST['load-fields-insert-after']){
				foreach($loadedForm['items'] as $newItem)
					$temp[] = $newItem;
			}
		}
		$form['items'] = $temp;
	}
}

// parse e-mail list
$email_list = explode(",", $form['email_list']);
$email_admin = "no";
for($x=0;$x<sizeof($email_list);$x++){
	$email_list[$x] = trim($email_list[$x]);
	if($email_list[$x] == trim(get_option('admin_email'))){
		unset($email_list[$x]);
		$email_admin = "yes";
	}
}

///////////////////////////////////////////////////////
?>
<form name="fm-main-form" id="fm-main-form" action="" method="post">
<input type="hidden" value="<?php echo $form['ID'];?>" name="form-id" id="form-id"/>
<input type="hidden" value="" name="message" id="message-post" />

<div class="wrap">
<div id="icon-edit-pages" class="icon32"></div>
<h2>Edit Form</h2>

<div id="message-container">
<?php 
if(isset($_POST['message']))
	switch($_POST['message']){
		case 1: ?><div id="message-success" class="updated"><p>Form updated. </p></div><?php break;
		case 2: ?><div id="message-error" class="error"><p>Save failed. </p></div><?php break;
		default: ?>
			<?php if(isset($_POST['message']) && trim($_POST['message']) != ""): ?>
			<div id="message-error" class="error"><p><?php echo stripslashes($_POST['message']);?></p></div>
			<?php endif; ?>
		<?php
	} ?></div>

<div id="poststuff" class="metabox-holder has-right-sidebar">
	<div id="side-info-column" class="inner-sidebar">	
		
		<div id="side-sortables" class="meta-box-sortables">
			<div id="submitdiv" class="postbox " >
			<h3><span>Publish</span></h3>
			<div class="inside">
				<div class="submitbox" id="submitpost">
					<div id="minor-publishing">
						<div style="display:none;">
						<input type="submit" name="save" value="Save" />
						</div>
					
						<div id="minor-publishing-actions">
						
							<div id="preview-action">
							<a class="button-secondary" href="<?php echo get_admin_url(null, 'admin.php')."?page=fm-edit-form&id=".$form['ID'];?>">Cancel Changes</a>
							</div>
						
							<div class="clear"></div>
						</div>
						
						<div id="misc-publishing-actions"></div>
						<div class="clear"></div>
					</div>
				
					<div id="major-publishing-actions">
						<div id="delete-action">
						<a class="submitdelete deletion" href="#">Move to Trash</a>
						</div>						
						<div id="publishing-action">
						<img src="http://localhost/wordpress/wp-admin/images/wpspin_light.gif" id="ajax-loading" style="visibility:hidden;" alt="" />								
								<input name="publish" type="button" class="button-primary" id="publish" tabindex="5" accesskey="p" value="Save Form" onclick="fm_saveForm()" />						
						</div>						
						<div class="clear"></div>
					</div>										
				</div>							
			</div>				
		</div>	
		<!-------------------------------------------------------------------------------------------------- -->
		<div id="submitdiv" class="postbox " >
		<h3 class='hndle'><span>Load Fields</span></h3>
		<div class="inside">
			<div class="submitbox" id="submitpost">			
				<div id="minor-publishing">						
					<div id="minor-publishing-actions">						
						<div class="metabox-prefs">
							<label for="load-fields-id">Form: </label>		
							<select name="load-fields-id">
								<?php foreach($formList as $f): ?>
								<option value="<?php echo $f['ID'];?>"><?php echo $f['title']; ?></option>
								<?php endforeach; ?> 
							</select>
							<br />
							<label for="load-fields-insert-after">Insert After:</label>
							<select name="load-fields-insert-after">
								<option value="0">(Insert at beginning)</option>
								<?php foreach($form['items'] as $item): ?>
								<option value="<?php echo $item['unique_name'];?>"><?php echo fm_restrictString($item['label'],15);?></option>
								<?php endforeach; ?>
								<option value="1">(Insert at end)</option>
							</select>
							<br />
							<input name="load-fields" type="submit" class="button-secondary" value="Load Fields" onclick="return fm_loadFields()"/>	
						</div>					
						<div class="clear"></div>			
					</div>								
					<div class="clear"></div>
				</div>				
			</div>		
		</div>
		</div>		
		<!-------------------------------------------------------------------------------------------------- -->
		<div id="submitdiv" class="postbox " >
		<h3 class='hndle'><span>Submission Data</span></h3>
		<div class="inside">
			<div class="submitbox" id="submitpost">			
				<div id="minor-publishing">						
					<div id="minor-publishing-actions">						
						<div id="preview-action">		
							<a class="preview button" href="<?php echo get_admin_url(null, 'admin.php')."?page=fm-form-data&id=".$form['ID'];?>" >View Data</a>	
						</div>					
						<div class="clear"></div>			
					</div>				
					<div id="misc-publishing-actions">					
						<div class="misc-pub-section">Submission count: <strong><?php echo $fmdb->getSubmissionDataNumRows($form['ID']);?></strong></div>					
						<div class="misc-pub-section misc-pub-section-last">Last submission: <strong><?php $sub = $fmdb->getLastSubmission($form['ID']); echo $sub['timestamp'];?></strong></div>					
					</div>
					<div class="clear"></div>
				</div>				
			</div>		
		</div>
		</div>
		<!-------------------------------------------------------------------------------------------------- -->	
		
	</div><!-- side-info-column -->
</div><!-- poststuff -->

<div id="post-body">
<div id="post-body-content" class="edit-form-body">
<div id="titlediv">
	<div id="titlewrap">		
		<input type="text" name="post_title" id="title" size="30" tabindex="1" value="<?php echo $form['title'];?>" autocomplete="off" />
	</div>
</div>

<div id="postdivrich" class="postarea">

	<div id="editor-toolbar">
		<!-- <div class="zerosize"><input accesskey="e" type="button" onclick="switchEditors.go('content')" /></div>
		<a id="edButtonHTML" class="hide-if-no-js" onclick="switchEditors.go('content', 'html');">HTML</a>
		<a id="edButtonPreview" class="active hide-if-no-js" onclick="switchEditors.go('content', 'tinymce');">Visual</a> -->
		<div id="media-buttons" class="hide-if-no-js"> Insert Form Element: 
			
		</div>		

	</div>
	<div id='editorcontainer'>
		<div id="quicktags">
			<div class="fm-editor-controls">			
			<?php
				$types=array();
				foreach($fm_controls as $controlKey=>$controlType){
					if($controlKey != 'default')
						$types[]="<a class=\"edit-form-button\" onclick=\"fm_addItem('{$controlKey}')\">".$controlType->getTypeLabel()."</a>";
				}
				echo implode(" | \n", $types);
			?>
			</div>
		</div>
		<div class="fm-editor">
			<ul id="form-list">
			<?php foreach($form['items'] as $item): ?>
			<?php	echo "<li class=\"edit-form-menu-item postbox\" id=\"".$item['unique_name']."\">".$fm_display->getEditorItem($item['unique_name'], $item['type'], $item)."</li>\n"; ?>
			<?php endforeach; ?>	
			</ul>
		</div>
	</div>	
	
	
	<script type="text/javascript">	
	fm_initEditor();
	</script>

<!--this 'table' is here for aesthetics -->
<table id="post-status-info" cellspacing="0">
	<tbody>
		<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		</tr>
	</tbody>
</table>

</div>

<div id="normal-sortables" class="meta-box-sortables">
	<div id="postexcerpt" class="postbox " >
	<h3 class='hndle'><span>Shortcode</span></h3>
		<div class="inside">		
		<input type="text" id="shortcode" value="<?php echo $form['shortcode'];?>" />
		</div>
	</div>
</div>

<div id="normal-sortables" class="meta-box-sortables">
	<div id="postexcerpt" class="postbox " >
	<h3 class='hndle'><span>Settings</span></h3>
		<div class="inside">
		<div class="fm-form-admin">
			<br />
			<div class="fm-admin-field-wrap">
				<label>Show form title:</label>
				<input type="checkbox" id="show_title" <?php echo ($form['show_title']==1?'checked':'');?> />
			</div>
			<div class="fm-admin-field-wrap">
				<label>Show border:</label>
				<input type="checkbox" id="show_border" <?php echo ($form['show_border']==1?'checked':'');?> />
			</div>
			<div class="fm-admin-field-wrap">
				<label>Label position:
				<span class="small">Labels can be placed to the left or above each field</span>
				</label>
					<select id="labels_on_top">
						<option value="1" <?php if($form['labels_on_top']==1) echo "selected=\"selected\"";?>>Top</option>
						<option value="0" <?php if($form['labels_on_top']==0) echo "selected=\"selected\"";?>>Left</option>
					</select>		
			</div>
			<div class="fm-admin-field-wrap">								
				<label>Label width (in pixels):
				<span class="small">Applies to checkboxes, and when labels are to the left</span>
				</label>
					<input type="text" id="label_width" value="<?php echo $form['label_width'];?>" />
			</div>	
			<div class="fm-admin-field-wrap">								
				<label>Submit acknowledgement message:
				<span class="small">This is displayed after the form has been submitted</span>
				</label>
					<input type="text" id="submitted_msg" value="<?php echo $form['submitted_msg'];?>" />
			</div>				
			<div class="fm-admin-field-wrap">
				<label>Submit button label:</label>
					<input type="text" id="submit_btn_text" value="<?php echo $form['submit_btn_text'];?>"/>
			</div>
			<div class="fm-admin-field-wrap">
				<label>Send notification to <?php echo get_option('admin_email'); ?>:</label>
				<input type="checkbox" id="email_admin" <?php echo ($email_admin=="yes"?'checked':'');?> />
			</div>
			<div class="fm-admin-field-wrap">
				<label>Send notification(s) to:
				<span class="small">Enter a list of emails separated by commas</span>
				</label>
				<input type="text" id="email_list" value="<?php echo (sizeof($email_list)==0)?"":implode(", ", $email_list); ?>" />				
			</div>
		</div>
		</div>
	</div>
</div>


</div>
</div>
<br class="clear" />
</div><!-- /poststuff -->
</div>
</form>