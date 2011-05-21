<?php
global $fmdb;
global $fm_display;
global $fm_controls;

$itemsPerPage = 30;
$set = isset($_REQUEST['set']) ? $_REQUEST['set'] : 0;

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

// PARSE THE QUERY STRING
parse_str($_SERVER['QUERY_STRING'], $queryVars);

////////////////////////////////////////////////////////////////////////////////
//ACTIONS

//Delete data row(s):
if(isset($_POST['fm-action-select'])){
	if($_POST['fm-action-select'] == 'delete'){	
		$toDelete=array();
		$numRows = (int)$_POST['fm-num-data-rows'];
		for($x=0;$x<$numRows;$x++){
			if(isset($_POST['fm-checked-'.$x])){
				$toDelete[]=$x;
			}
		}
		foreach($toDelete as $del){	
			$fmdb->deleteSubmissionDataRow($form['ID'], $formData['data'][$del]);
		}		
	}
	else if($_POST['fm-action-select'] == 'delete_all'){
		$fmdb->clearSubmissionData($form['ID']);	
	}
	
	//clean up the mess we made
	$formData = $fmdb->getFormSubmissionData($form['ID']);
}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
//SUMMARY DIALOG

if(isset($_POST['fm-action-select']) && $_POST['fm-action-select'] == 'summary'){

if($formData !== false){
	$numRows = (int)$_POST['fm-num-data-rows'];
	?>
<div class="wrap">
	<div id="icon-edit-pages" class="icon32"></div>
	<h2>Data: <?php echo $form['title'];?></h2>
	<div style="float:right;">		
		<a class="button-secondary action" href="<?php echo get_admin_url(null, 'admin.php')."?page=fm-form-data&id=".$form['ID'];?>" title="Back to Data">Back to Form Data</a>
	</div>
	
	<div class="wrap">
	<br />
	Showing data summary: <br />
	<?php
	for($x=0;$x<$numRows;$x++){		
		if(isset($_POST['fm-checked-'.$x])){
			echo "<div class=\"fm-data-summary-div\" >".$fm_display->displayDataSummary($form, $formData['data'][$x], "", "", true)."</div>\n";
		}
	}	
	?>
	</div>
	
	<div>
		<div style="float:right;">		
		<a class="button-secondary action" href="<?php echo get_admin_url(null, 'admin.php')."?page=fm-form-data&id=".$form['ID'];?>" title="Back to Data">Back to Form Data</a>
		</div>
	</div>
</div>
	<?php
}

}else{

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
//MAIN DIALOG


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
	<div id="icon-edit-pages" class="icon32"></div>
	<h2>Data: <?php echo $form['title'];?></h2>
	<div style="float:right;">
		<a class="button-primary" onclick="fm_downloadCSV()" title="Download Data as CSV">Download Data (.csv)</a>
		<a class="button-secondary action" href="<?php echo get_admin_url(null, 'admin.php')."?page=fm-edit-form&id=".$form['ID'];?>" title="Edit this form">Edit Form</a>
	</div>
		<div class="tablenav">			
			<div class="alignleft actions">
				<select name="fm-action-select" id="fm-action-select">
				<option value="-1" selected="selected">Bulk Actions</option>
				<option value="summary">Show Summary</option>
				<option value="delete">Delete Selected</option>
				<option value="delete_all">Delete All Submission Data</option>
				</select>
				<input type="submit" value="Apply" name="fm-doaction" id="fm-doaction" onclick="return fm_confirmSubmit()" class="button-secondary action" />							
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
						if (selected) return confirm("Are you sure you want to delete the selected items?");
						return false;
					}
					else if(action == 'delete_all'){
						return confirm("This will delete all submission data for this form. Are you sure?");
					}			
					else if(action == 'summary') return selected;		
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
				<a href="<?php 
					echo get_admin_url(null, 'admin.php')."?".http_build_query(array_merge($queryVars, array('set' => $x)));
					?>">
					<?php echo $x+1; ?></a>&nbsp;
				<?php endfor; ?>
			</div>			
		</div>
		<table class="widefat post fixed">
			<thead>
			<tr>
				<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" id="cb-col-top" onchange="fm_dataCBColChange()"/></th>
				<th width="130px"><a class="edit-form-button" href="<?php
									$ord = ($queryVars['orderby'] == 'timestamp' && $queryVars['ord'] == 'ASC') ? 'DESC' : 'ASC';
									echo get_admin_url(null, 'admin.php')."?".http_build_query(array_merge($queryVars, array('ord' => $ord, 'orderby' => 'timestamp'))); ?>">Timestamp</a></th>
				<th width="60px"><a class="edit-form-button" href="<?php
									$ord = ($queryVars['orderby'] == 'user' && $queryVars['ord'] == 'ASC') ? 'DESC' : 'ASC';
									echo get_admin_url(null, 'admin.php')."?".http_build_query(array_merge($queryVars, array('ord' => $ord, 'orderby' => 'user'))); ?>">User</a></th>
				<th width="130px"><a class="edit-form-button" href="<?php
									$ord = ($queryVars['orderby'] == 'user_ip' && $queryVars['ord'] == 'ASC') ? 'DESC' : 'ASC';
									echo get_admin_url(null, 'admin.php')."?".http_build_query(array_merge($queryVars, array('ord' => $ord, 'orderby' => 'user_ip'))); ?>">IP Address</a></th>
				<?php $x=1; foreach($form['items'] as $formItem): ?>
					<?php if($formItem['db_type'] != "NONE"): ?>
						<th><a class="edit-form-button" href="<?php
									$ord = ($queryVars['orderby'] == $formItem['unique_name'] && $queryVars['ord'] == 'ASC') ? 'DESC' : 'ASC';
									echo get_admin_url(null, 'admin.php')."?".http_build_query(array_merge($queryVars, array('ord' => $ord, 'orderby' => $formItem['unique_name']))); ?>"><?php echo fm_restrictString($formItem['label'],20);?></a>
									<a onclick="fm_downloadAllFiles('<?php echo $formItem['unique_name'];?>')">Download Files</a>
									</th>
					<?php endif; ?>
				<?php endforeach; ?>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" id="cb-col-bottom" onchange="fm_dataCBColChange()"/></th>
				<th>Timestamp</th>
				<th>User</th>
				<th>IP Address</th>
				<?php foreach($form['items'] as $formItem): ?>
					<?php if($formItem['db_type'] != "NONE"): ?>
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
					<td><?php echo $dataRow['user_ip'];?></td>
					<?php foreach($form['items'] as $formItem): ?>
						<?php if($formItem['type'] == 'file'): ?>
							<td><a onclick="fm_downloadFile('<?php echo $formItem['unique_name'];?>', '<?php echo $dataRow['timestamp'];?>', '<?php echo $dataRow['user'];?>')" title="Download '<?php echo $formItem['label'];?>'"><?php echo $dataRow[$formItem['unique_name']]; ?></a></td>
						<?php elseif($formItem['db_type'] != "NONE"): ?>
							<td class="post-title column-title"><?php echo fm_restrictString($dataRow[$formItem['unique_name']], 75);?></td>						
						<?php endif; ?>
					<?php endforeach; ?>					
				</tr>
			<?php endforeach; ?>
			<input type="hidden" name="fm-num-data-rows" id="fm-num-data-rows" value="<?php echo $index;?>" />
		</table>			
		
	<br class="clear" />
</div><!-- /wrap -->

</form>

<?php 
}
?>