<?php
/* translators: the following are from the form's data page */
global $fmdb;
global $fm_display;
global $fm_controls;

global $fm_SLIMSTAT_EXISTS;
global $fm_MEMBERS_EXISTS;

$itemsPerPage = 30;
$set = isset($_REQUEST['set']) ? $_REQUEST['set'] : 0;

$fm_dataDialog = "main";

$form = null;
$formData = null;
if($_REQUEST['id']!="") $form = $fmdb->getForm($_REQUEST['id']);
if($form != null){
	$orderBy = isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : 'timestamp';
	$orderBy = $fmdb->isValidItem($form, $orderBy) ? $orderBy : 'timestamp';
	$ord = $_REQUEST['ord'] == 'ASC' ? 'ASC' : 'DESC';
	$formData = $fmdb->getFormSubmissionData($form['ID'], $orderBy, $ord, ($set*$itemsPerPage), $itemsPerPage);
	
	$numDataPages = ceil($formData['count'] / $itemsPerPage);
}

$hasPosts = $fmdb->dataHasPublishedSubmissions($form['ID']);

// PARSE THE QUERY STRING
parse_str($_SERVER['QUERY_STRING'], $queryVars);

////////////////////////////////////////////////////////////////////////////////
//ACTIONS

//Delete data row(s):
if(isset($_POST['fm-action-select'])){
		
	switch($_POST['fm-action-select']){
		case "delete":
			if(!$fm_MEMBERS_EXISTS || current_user_can('form_manager_delete_data')){
				$toDelete = fm_data_getCheckedRows();
				foreach($toDelete as $del)
					$fmdb->deleteSubmissionDataRow($form['ID'], $formData['data'][$del]);				
			}
			//clean up the mess we made
			$formData = $fmdb->getFormSubmissionData($form['ID'], $orderBy, $ord, ($set*$itemsPerPage), $itemsPerPage);
			break;
		case "delete_all":
			if(!$fm_MEMBERS_EXISTS || current_user_can('form_manager_delete_data'))
				$fmdb->clearSubmissionData($form['ID']);
			//clean up the mess we made
			$formData = $fmdb->getFormSubmissionData($form['ID'], $orderBy, $ord, ($set*$itemsPerPage), $itemsPerPage);
			break;
		case "edit":
			if(!$fm_MEMBERS_EXISTS || current_user_can('form_manager_edit_data'))
				$fm_dataDialog = "edit";	
			break;
		case "summary":
			$fm_dataDialog = "summary";
			break;
	}
}

//Edit data rows(s)
else if((!$fm_MEMBERS_EXISTS || current_user_can('form_manager_edit_data')) && isset($_POST['fm-edit-data-ok'])){
	$numRows = $_POST['fm-num-edit-rows'];
	$postFailed = false;
	
	for($x=0;$x<$numRows;$x++){
		$dataIndex = $_POST['fm-edit-row-'.$x];
		
		$newData = array();
		$postData = array();
		foreach($form['items'] as $item){
			if($item['type'] != 'file'
			&& $item['type'] != 'separator'
			&& $item['type'] != 'note'
			&& $item['type'] != 'recaptcha'){		
				$processed = $fm_controls[$item['type']]->processPost($item['unique_name']."-".$x, $item);
				if($processed === false){
					$postFailed = true;
				}
				if($fmdb->isDataCol($item['unique_name']))						
					$postData[$item['unique_name']] = $processed;
			}
		}
		
		$fmdb->updateDataSubmissionRow($form['ID'],
										$formData['data'][$dataIndex]['timestamp'],
										$formData['data'][$dataIndex]['user'],
										$formData['data'][$dataIndex]['user_ip'],
										$postData
										);
	}
	//clean up the mess we made
	$formData = $fmdb->getFormSubmissionData($form['ID'], $orderBy, $ord, ($set*$itemsPerPage), $itemsPerPage);
}

////////////////////////////////////////////////////////////////////////////////
// BEGIN OUTPUT

switch($fm_dataDialog){

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
//EDIT DIALOG

case "edit":

if($formData !== false){
	$numRows = (int)$_POST['fm-num-data-rows'];

	?>
<form name="fm-main-form" id="fm-main-form" action="" method="post">
<div class="wrap">
	<div id="icon-edit-pages" class="icon32"></div>
	<h2><?php _e("Data", 'wordpress-form-manager');?>: <?php echo $form['title'];?></h2>
	<div style="float:right;">
		<input type="submit" name="fm-edit-data-ok" value="<?php _e("Submit Changes", 'wordpress-form-manager');?>" />
		<input type="submit" name="fm-edit-data-cancel" value="<?php _e("Cancel", 'wordpress-form-manager');?>" />		
	</div>
	
	<div class="wrap">
	<br />
	
	<?php _e("Edit data", 'wordpress-form-manager');?>: <br />
	<?php
	
	$callbacks = array( 'text' => 'fm_data_displayTextEdit',
						'textarea' => 'fm_data_displayTextAreaEdit',
						'file' => 'fm_data_displayFileEdit'
					);
	$exclude_types = array('note', 'recaptcha');
	
	$editRowCount = 0;
	for($x=0;$x<$numRows;$x++){
		if(isset($_POST['fm-checked-'.$x])){
			echo "<div class=\"fm-data-edit-div\" >".$fm_display->displayFormBare($form, array('exclude_types' => $exclude_types, 'display_callbacks' => $callbacks, 'unique_name_suffix' => '-'.$x), $formData['data'][$x])."</div>\n";
			echo "<input type=\"hidden\" name=\"fm-edit-row-".$editRowCount."\" value=\"".$x."\" />\n";
			$editRowCount++;
		}
	}	
	?>
	</div>
	
	<input type="hidden" name="fm-num-edit-rows" value="<?php echo $editRowCount; ?>" />
	
	<div>
		<div style="float:right;">		
			<input type="submit" name="fm-edit-data-ok" value="<?php _e("Submit Changes", 'wordpress-form-manager');?>" />
			<input type="submit" name="fm-edit-data-cancel" value="<?php _e("Cancel", 'wordpress-form-manager');?>" />		
		</div>
	</div>
</div>
	<?php
}
break;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
//SUMMARY DIALOG

case "summary":

if($formData !== false){
	$numRows = (int)$_POST['fm-num-data-rows'];
	?>
<div class="wrap">
	<div id="icon-edit-pages" class="icon32"></div>
	<h2><?php _e("Data", 'wordpress-form-manager');?>: <?php echo $form['title'];?></h2>
	
	<div class="wrap">
	<br />
	<?php _e("Data summary:", 'wordpress-form-manager');?> <br />
	<?php
	for($x=0;$x<$numRows;$x++){		
		if(isset($_POST['fm-checked-'.$x])){
			echo "<div class=\"fm-data-summary-div\" >".$fm_display->displayDataSummaryNoTemplate($form, $formData['data'][$x], "", "", true)."</div>\n";
		}
	}	
	?>
	</div>	
	
</div>
	<?php
}

break;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
//MAIN DIALOG


case "main":
default:

//keep track of the max number of chars in each column so we can set the table widths appropriately
$colMaxChars=array();
for($x=0;$x<sizeof($form['items']);$x++) $colMaxChars[$x]=0;

if($formData !== false){
	foreach($formData['data'] as $dataRow){
		$x=1;
		foreach($form['items'] as $formItem){			
			$restricted = fm_restrictString($dataRow[$formItem['unique_name']], 20);						
			$len = strlen($restricted);
			if($len>$colMaxChars[$x]) $colMaxChars[$x] = $len;		
			$x++;
		}
	}
}

//'total' character width
$totalCharWidth = 0;
for($x=0;$x<sizeof($form['items']);$x++) $totalCharWidth += $colMaxChars[$x];

?>
<form name="fm-main-form" id="fm-main-form" action="" method="post">
<input type="hidden" value="<?php echo $form['ID'];?>" name="form-id" id="form-id"/>
<input type="hidden" value="<?php echo $form['title'];?>" name="title" id="title"/>
<input type="hidden" value="" name="message" id="message-post" />

<div class="wrap">
	<div style="float:right;padding-top:10px;">
		
		<a class="button-primary" onclick="fm_downloadCSV()" title="Download Data as CSV"><?php _e("Download Data (.csv)", 'wordpress-form-manager');?></a>
		
		<br />
		<div id="csv-working" style="visibility:hidden;padding-top:10px;margin-bottom:-20px;" ><img src="<?php echo get_admin_url(null, '');?>/images/wpspin_light.gif" id="ajax-loading" alt=""/>&nbsp;<?php _e("Working...", 'wordpress-form-manager');?></div><a href="#" id="fm-csv-download-link"></a>
	</div>
		<div class="tablenav">			
			<div class="alignleft actions">
				<select name="fm-action-select" id="fm-action-select">
				<option value="-1" selected="selected"><?php _e("Bulk Actions", 'wordpress-form-manager');?></option>
				<option value="summary"><?php _e("Show Summary", 'wordpress-form-manager');?></option>
				<?php if(!$fm_MEMBERS_EXISTS || current_user_can('form_manager_delete_data')): ?>
				<option value="delete"><?php _e("Delete Selected", 'wordpress-form-manager');?></option>
				<option value="delete_all"><?php _e("Delete All Submission Data", 'wordpress-form-manager');?></option>
				<?php endif; ?>
				<?php if(!$fm_MEMBERS_EXISTS || current_user_can('form_manager_edit_data')): ?>
				<option value="edit"><?php _e("Edit Selected", 'wordpress-form-manager');?></option>
				<?php endif; ?>
				</select>
				<input type="submit" value="<?php _e("Apply", 'wordpress-form-manager');?>" name="fm-doaction" id="fm-doaction" onclick="return fm_confirmSubmit()" class="button-secondary action" />							
				<script type="text/javascript">
				function fm_confirmSubmit(){
					var action = document.getElementById('fm-action-select').value;
					var numItems = document.getElementById('fm-num-data-rows').value;
					
					//see if anything is selected
					var selected = false;
					for(var x=0;x<numItems;x++){
						if(document.getElementById('fm-checked-' + x).checked){
							selected = true;
							x = numItems;
						}
					}					
					
					if(action == 'delete'){
						if (selected) return confirm("<?php _e("Are you sure you want to delete the selected items?", 'wordpress-form-manager');?>");
						return false;
					}
					else if(action == 'delete_all'){
						return confirm("<?php _e("This will delete all submission data for this form. Are you sure?", 'wordpress-form-manager');?>");
					}			
					else if(action != '-1') return selected;		
					return false;
				}
				</script>
			</div>				
			<div class="clear"></div>
		</div>		
		
		<div class="tablenav">
			<div style="float:left;">
			Showing page <?php echo $set + 1;?> ( Rows <?php echo $set*$itemsPerPage;?> - <?php echo min(($set+1)*$itemsPerPage, $formData['count']); ?> out of <?php echo $formData['count'];?> ): 
			</div>
			<div style="float:right;">
				Page: &nbsp;&nbsp;
				<?php for($x=0;$x<$numDataPages;$x++): ?>
					<?php if($set == $x): ?>
						<strong><?php echo $x+1; ?>&nbsp;</strong>
					<?php else: ?>
						<a href="<?php echo get_admin_url(null, 'admin.php')."?".http_build_query(array_merge($queryVars, array('set' => $x)));?>"> <?php echo $x+1; ?></a>&nbsp;
					<?php endif; ?>
				<?php endfor; ?>
			</div>			
		</div>
		<table class="widefat post fixed">
			<thead>
			<tr>
				<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" id="cb-col-top" onchange="fm_dataCBColChange()"/></th>
				<th width="130px"><a class="edit-form-button" href="<?php
									$ord = ($queryVars['orderby'] == 'timestamp' && $queryVars['ord'] == 'ASC') ? 'DESC' : 'ASC';
									echo get_admin_url(null, 'admin.php')."?".http_build_query(array_merge($queryVars, array('ord' => $ord, 'orderby' => 'timestamp'))); ?>"><?php _e("Timestamp", 'wordpress-form-manager');?></a></th>
				<th width="60px"><a class="edit-form-button" href="<?php
									$ord = ($queryVars['orderby'] == 'user' && $queryVars['ord'] == 'ASC') ? 'DESC' : 'ASC';
									echo get_admin_url(null, 'admin.php')."?".http_build_query(array_merge($queryVars, array('ord' => $ord, 'orderby' => 'user'))); ?>"><?php _e("User", 'wordpress-form-manager');?></a></th>
				<th width="130px"><a class="edit-form-button" href="<?php
									$ord = ($queryVars['orderby'] == 'user_ip' && $queryVars['ord'] == 'ASC') ? 'DESC' : 'ASC';
									echo get_admin_url(null, 'admin.php')."?".http_build_query(array_merge($queryVars, array('ord' => $ord, 'orderby' => 'user_ip'))); ?>"><?php _e("IP Address", 'wordpress-form-manager');?></a></th>
				<?php if($hasPosts): ?>
					<th><?php _e("Post", 'wordpress-form-manager');?></th>
				<?php endif; ?>
				<?php $x=1; foreach($form['items'] as $formItem): ?>
					<?php if($fmdb->isDataCol($formItem['unique_name'])): ?>
						<th><a class="edit-form-button" href="<?php
									$ord = ($queryVars['orderby'] == $formItem['unique_name'] && $queryVars['ord'] == 'ASC') ? 'DESC' : 'ASC';
									echo get_admin_url(null, 'admin.php')."?".http_build_query(array_merge($queryVars, array('ord' => $ord, 'orderby' => $formItem['unique_name']))); ?>">
									<?php echo (trim($formItem['nickname']) != "" ? $formItem['nickname'] : fm_restrictString($formItem['label'],20));?>
									</a>
									<?php if($formItem['type'] == 'file'): ?>
									<div style="margin-top:8px"><a id="<?php echo $formItem['unique_name'];?>-download" class="button-primary" onclick="fm_downloadAllFiles('<?php echo $formItem['unique_name'];?>')"><?php _e("Download Files", 'wordpress-form-manager');?></a>																	
									</div>
									<div style="position:absolute;">
										<div id="<?php echo $formItem['unique_name'];?>-working" style="visibility:hidden;position:relative;top:-17px;margin-bottom:-17px;" ><img src="<?php echo get_admin_url(null, '');?>/images/wpspin_light.gif" id="ajax-loading" alt=""/>&nbsp;<?php _e("Working...", 'wordpress-form-manager');?></div>
										<a style="visibility:hidden;position:relative;top:-17px;text-decoration:underline;" id="<?php echo $formItem['unique_name'];?>-link" href="#">&nbsp;</a>
									</div>
									<?php endif; ?>
									</th>
					<?php endif; ?>
				<?php endforeach; ?>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" id="cb-col-bottom" onchange="fm_dataCBColChange()"/></th>
				<th><?php _e("Timestamp", 'wordpress-form-manager');?></th>
				<th><?php _e("User", 'wordpress-form-manager');?></th>
				<th><?php _e("IP Address", 'wordpress-form-manager');?></th>
				<?php if($hasPosts): ?>
					<th><?php _e("Post", 'wordpress-form-manager');?></th>
				<?php endif; ?>
				<?php foreach($form['items'] as $formItem): ?>
					<?php if($fmdb->isDataCol($formItem['unique_name'])): ?>
						<th><?php echo fm_restrictString($formItem['label'],20);?></th>
					<?php endif; ?>
				<?php endforeach; ?>
			</tr>
			</tfoot>
			<?php $index=0; ?>
			<?php foreach($formData['data'] as $dataRow): ?>
				<tr class="alternate author-self status-publish iedit">
					<td><input type="checkbox" name="fm-checked-<?php echo $index;?>" id="fm-checked-<?php echo $index++;?>"/></td>
					<td><?php echo $dataRow['timestamp'];?></td>
					<td><?php echo $dataRow['user'];?></td>
					<td><?php if($fm_SLIMSTAT_EXISTS): echo fm_get_slimstat_IP_link($queryVars, $dataRow['user_ip']); ?>
						<?php else: echo $dataRow['user_ip']; endif; ?>
					</td>
					<?php if($hasPosts): ?>
						<td><?php if($dataRow['post_id'] > 0): ?><a href="<?php echo get_permalink($dataRow['post_id']);?>"><?php echo get_the_title($dataRow['post_id']);?></a><?php else: echo "&nbsp;"; endif;?></td>
					<?php endif; ?>
					<?php 
					foreach($form['items'] as $formItem){
						if($formItem['type'] == 'file'){
							if(strpos($dataRow[$formItem['unique_name']],"</a>") === false):?>
								<td><a class="fm-download-link" onclick="fm_downloadFile('<?php echo $formItem['unique_name'];?>', '<?php echo $dataRow['timestamp'];?>', '<?php echo $dataRow['user'];?>')" title="<?php _e("Download", 'wordpress-form-manager');?> '<?php echo $dataRow[$formItem['unique_name']];?>'"><?php echo $dataRow[$formItem['unique_name']]; ?></a></td>
							<?php else: ?>
								<td><?php echo $dataRow[$formItem['unique_name']]; ?></td>
							<?php endif; /* end if file */ 
						}else if($fmdb->isDataCol($formItem['unique_name'])){ ?>
							<td class="post-title column-title"><?php echo fm_restrictString($dataRow[$formItem['unique_name']], 75);?></td>						
						<?php } /* end if data type other than file */
					} /* end foreach */ 
					?>
				</tr>
			<?php endforeach; ?>
			<input type="hidden" name="fm-num-data-rows" id="fm-num-data-rows" value="<?php echo $index;?>" />
		</table>			
		
	<br class="clear" />
</div><!-- /wrap -->

</form>

<?php 
}

// HELPERS
function fm_data_getCheckedRows(){
	$checked=array();
	$numRows = (int)$_POST['fm-num-data-rows'];
	for($x=0;$x<$numRows;$x++){
		if(isset($_POST['fm-checked-'.$x])){
			$checked[]=$x;
		}
	}
	return $checked;
}

function fm_data_displayTextEdit($uniqueName, $itemInfo){ return "<input name=\"".$uniqueName."\" type=\"text\" value=\"".htmlspecialchars($itemInfo['extra']['value'])."\" style=\"width:400px;\"/>"; }
function fm_data_displayTextAreaEdit($uniqueName, $itemInfo){ return "<textarea style=\"width:400px;\" rows=\"5\"  name=\"".$uniqueName."\" >".$itemInfo['extra']['value']."</textarea>"; }
function fm_data_displayFileEdit($uniqueName, $itemInfo){ return $itemInfo['extra']['value']; }

?>