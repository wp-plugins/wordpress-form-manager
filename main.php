<?php
global $fmdb;
global $wpdb;

$currentDialog = "main";

/////////////////////////////////////////////////////////////////////////////
// PROCESS POST /////////////////////////////////////////////////////////////

//ADD NEW
//for now just add blank forms for 'Add New'
if(isset($_POST['fm-add-new']))
	$fmdb->createForm(null,$wpdb->prefix.get_option('fm-data-table-prefix'));
	
	
//APPLY ACTION
if(isset($_POST['fm-doaction'])){
	//check for 'delete'
	if($_POST['fm-action-select'] == "delete"){
		//get a list of selected IDs
		$fList = $fmdb->getFormList();
		$deleteIds = array();
		foreach($fList as $form){
			if(isset($_POST['fm-checked-'.$form['ID']])) $deleteIds[] = $form['ID'];
		}		
		if(sizeof($deleteIds)>0) $currentDialog = "verify-delete";
	}
}

//SINGLE DELETE
if($_POST['fm-action'] == "delete"){
	$deleteIds = array();
	$deleteIds[0] = $_POST['fm-id'];
	$currentDialog = "verify-delete";
}

//VERIFY DELETE
if(isset($_POST['fm-delete-yes'])){
	$index=0;
	while(isset($_POST['fm-delete-id-'.$index])){
		$fmdb->deleteForm($_POST['fm-delete-id-'.$index]);
		$index++;
	}
}

/////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
// DISPLAY UI

$formList = $fmdb->getFormList();

?>

<?php

/////////////////////////////////////////////////////////////////////////////
// FORM EDITOR //////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////
// VERIFY DELETE ////////////////////////////////////////////////////////////

if($currentDialog == "verify-delete"):?>
<form name="fm-main-form" id="fm-main-form" action="" method="post">
<div class="wrap">
	<div id="icon-edit-pages" class="icon32"></div>
	<h2 style="margin-bottom:20px">Forms</h2>
	<div class="form-wrap">
		<h3>Are you sure you want to delete: </h3>
	
		<ul style="list-style-type:disc;margin-left:30px;">
		<?php
		foreach($formList as $form){
			if(in_array($form['ID'], $deleteIds, true)){
			echo "<li>".$form['title']."</li>";	
			}
		}
		?>
		</ul>
		
		<br />
		<?php $index=0; foreach($deleteIds as $id): ?>
			<input type="hidden" value="<?php echo $id;?>" name="fm-delete-id-<?php echo $index++;?>" />
		<?php endforeach; ?>
		<input type="submit" value="Yes" name="fm-delete-yes" />
		<input type="submit" value="Cancel" name="fm-delete-cancel"  />
	</div>
</div>
</form>
<?php

/////////////////////////////////////////////////////////////////////////////
// MAIN EDITOR //////////////////////////////////////////////////////////////

else: ?>

<form name="fm-main-form" id="fm-main-form" action="" method="post">
	<div class="wrap">
		<div id="icon-edit-pages" class="icon32"></div>
		<h2 style="margin-bottom:20px">Forms <input type="submit" class="button-secondary" name="fm-add-new" value="Add New" /></h2>
		<?php if(sizeof($formList)>0): ?>
		<div class="tablenav">
		
			<div class="alignleft actions">
				<select name="fm-action-select">
				<option value="-1" selected="selected">Bulk Actions</option>
				<option value="delete">Delete</option>
				</select>
				<input type="submit" value="Apply" name="fm-doaction" id="fm-doaction" class="button-secondary action" />
			</div>
				
			<div class="clear"></div>
		</div>		
		
		<table class="widefat post fixed">
			<thead>
			<tr>
				<th scope="col" class="manage-column column-cb check-column">&nbsp;</th>
				<th>Name</th>
				<th>Slug</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<th scope="col" class="manage-column column-cb check-column">&nbsp;</th>
				<th>Name</th>
				<th>Slug</th>
			</tr>
			</tfoot>
			<?php	 foreach($formList as $form): ?>
				<tr class="alternate author-self status-publish iedit">
					<td><input type="checkbox" name="fm-checked-<?php echo $form['ID'];?>"/></td>
					<td class="post-title column-title"><strong><a class="row-title" href="<?php echo get_admin_url(null, 'admin.php')."?page=fm-edit-form&id=".$form['ID'];?>"><?php echo $form['title'];?></a></strong>
						<div class="row-actions">
						<span class='edit'>
						<a href="<?php echo get_admin_url(null, 'admin.php')."?page=fm-edit-form&id=".$form['ID'];?>" title="Edit this form">Edit</a> | 
						<a href="<?php echo get_admin_url(null, 'admin.php')."?page=fm-form-data&id=".$form['ID'];?>" title="View form data">Data</a> | 
						<a href="#" title="Delete this form" onClick="fm_deleteFormClick('<?php echo $form['ID'];?>');return false">Delete</a>
						</span>
						</div>
					</td>
					<td><?php echo $form['shortcode'];?></td>
				</tr>
			<?php endforeach; ?>			
			<input type="hidden" value="" id="fm-action" name="fm-action"/>
			<input type="hidden" value="" id="fm-id" name="fm-id"/>
		</table>	
	<?php else: ?> No forms yet...<?php endif; ?>
	</div>
</form>
<?php endif; //end if main editor ?>