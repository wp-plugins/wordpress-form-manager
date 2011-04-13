<?php
global $fmdb;
global $fm_controls;

$form = null;
$formData = null;
if($_REQUEST['id']!="") $form = $fmdb->getForm($_REQUEST['id']);
if($form != null) $formData = $fmdb->getFormSubmissionData($form['ID']);

////////////////////////////////////////////////////////////////////////////////
//ACTIONS

//Delete data row(s):
if(isset($_POST['fm-action-select']) && $_POST['fm-action-select'] == 'delete'){
	//use the raw data, since we need the slashes for the WHERE = comparison
	$rawData = $fmdb->getFormSubmissionData($form['ID'], true);
	$toDelete=array();
	$numRows = (int)$_POST['fm-num-data-rows'];
	for($x=0;$x<$numRows;$x++){
		if(isset($_POST['fm-checked-'.$x])){
			$toDelete[]=$x;
		}
	}
	foreach($toDelete as $del){	
		$fmdb->deleteSubmissionDataRow($form['ID'], $rawData[$del]);
	}
	//clean up the mess we made
	$formData = $fmdb->getFormSubmissionData($form['ID']);
}

////////////////////////////////////////////////////////////////////////////////
//DOWNLOAD CSV

if(isset($_REQUEST['csv']) && $_REQUEST['csv']==1){
	/*
	$csvdata = "this is a test.";
	
	$fname = 'myCSV.csv';
	$fp = fopen($fname,'w');
	fwrite($fp,$csvdata);
	fclose($fp);	
	*/
}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
//MAIN DIALOG

//make the data look nice
$newData=array();

//keep track of the max number of chars in each column so we can set the table widths appropriately
$colMaxChars=array();
for($x=0;$x<sizeof($form['items']);$x++) $colMaxChars[$x]=0;

if($formData !== false){
	foreach($formData as $dataRow){
		$x=1;
		foreach($form['items'] as $formItem){
			//get the raw data
			$raw = $dataRow[$formItem['unique_name']];
			//parse the data according to the control type
			$parsed = $fm_controls[$formItem['type']]->parseData($formItem['unique_name'], $formItem, $raw);
			//display the shortened version
			$restricted = fm_restrictString($parsed, 20);
			$dataRow[$formItem['unique_name']] = $restricted;
			
			//update $colMaxChars
			$len = strlen($restricted);
			if($len>$colMaxChars[$x]) $colMaxChars[$x] = $len;		
			$x++;
		}
		$newData[] = $dataRow;
	}
}

//'total' character width
$totalCharWidth = 0;
for($x=0;$x<sizeof($form['items']);$x++) $totalCharWidth += $colMaxChars[$x];

$formData = $newData;

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
		<a class="button-secondary action" href="<?php echo $_SERVER['PHP_SELF']."?page=fm-edit-form&id=".$form['ID'];?>" title="Edit this form">Edit Form</a>
	</div>
		<div class="tablenav">			
			<div class="alignleft actions">
				<select name="fm-action-select" id="fm-action-select">
				<option value="-1" selected="selected">Bulk Actions</option>
				<option value="delete">Delete</option>
				</select>
				<input type="submit" value="Apply" name="fm-doaction" id="fm-doaction" onclick="return fm_confirmSubmit()" class="button-secondary action" />							
				<script type="text/javascript">
				function fm_confirmSubmit(){
					var action = document.getElementById('fm-action-select').value;
					if(action == 'delete'){
						//see if anything is selected
						var numItems = document.getElementById('fm-num-data-rows').value;
						var selected = false;
						for(var x=0;x<numItems;x++){
							if(document.getElementById('fm-checked-' + x).checked){
								selected = true;
								x = numItems;
							}
						}
						if (selected) return confirm("Are you sure you want to delete the selected items?");
						return false;
					}
					return false;
				}
				</script>
			</div>				
			<div class="clear"></div>
		</div>		
		
		<table class="widefat post fixed">
			<thead>
			<tr>
				<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" id="cb-col-top" onchange="fm_dataCBColChange()"/></th>
				<th width="130px">Timestamp</th>
				<th width="60px">User</th>
				<?php $x=1; foreach($form['items'] as $formItem): ?>
					<?php if($formItem['db_type'] != "NONE"): ?>
						<th><?php echo fm_restrictString($formItem['label'],20);?></th>
					<?php endif; ?>
				<?php endforeach; ?>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" id="cb-col-bottom" onchange="fm_dataCBColChange()"/></th>
				<th>Timestamp</th>
				<th>User</th>
				<?php foreach($form['items'] as $formItem): ?>
					<?php if($formItem['db_type'] != "NONE"): ?>
						<th><?php echo fm_restrictString($formItem['label'],20);?></th>
					<?php endif; ?>
				<?php endforeach; ?>
			</tr>
			</tfoot>
			<?php $index=0; ?>
			<?php foreach($formData as $dataRow): ?>
				<tr class="alternate author-self status-publish iedit">
					<td><input type="checkbox" name="fm-checked-<?php echo $index;?>" id="fm-checked-<?php echo $index++;?>"/></td>
					<td><?php echo $dataRow['timestamp'];?></td>
					<td><?php echo $dataRow['user'];?></td>
					<?php foreach($form['items'] as $formItem): ?>
						<?php if($formItem['db_type'] != "NONE"): ?>
							<td class="post-title column-title"><?php echo $dataRow[$formItem['unique_name']];?></td>
						<?php endif; ?>
					<?php endforeach; ?>					
				</tr>
			<?php endforeach; ?>
			<input type="hidden" name="fm-num-data-rows" id="fm-num-data-rows" value="<?php echo $index;?>" />
		</table>			
		
	<br class="clear" />
</div><!-- /wrap -->

</form>