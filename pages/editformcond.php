<script type="text/javascript">
function fm_getNewConditionHTML(conditionInfo){
	var str = ""
	var temp;
	
	str += '<table class="condition-buttons">';
	
	str += '<tr><td class="condition-move"><a class="handle edit-form-button">move</a></td>';
	
	str += '<td>' + fm_getRuleSelect(conditionInfo.id + '-rule', conditionInfo.rule) + '</td><td><a class="edit-form-button" id="' + conditionInfo.id + '-showhide" onclick="fm_showHideCondition(\'' + conditionInfo.id + '\')">hide</a></td><td><a class="edit-form-button" onclick="fm_removeCondition(\'' + conditionInfo.id + '\')">delete</a></td></tr>';
	
	str += '</table>';
	str += '<div id="' + conditionInfo.id + '-div">';
	str += '<table>';
	
	str += '<tr><td>';
		str += '<div class="condition-tests-div">';
		str += '<ul id="' + conditionInfo.id + '-tests' + '" class="condition-test-list">';
			
			for(var x=0;x<conditionInfo.tests.length;x++){
				str += '<li class="postbox condition-test" id="' + conditionInfo.id + '_test_li_' + x + '">' + fm_getTestHTML(conditionInfo.id, conditionInfo.tests[x], x) + '</li>';
//alert('bar');
			}
			
			if(conditionInfo.tests.length == 0)
				str += '<li class="postbox condition-test" id="' + conditionInfo.id + '_test_li_' + x + '">' + fm_getTestHTML(conditionInfo.id, false, x) + '</li>';

		str += '</ul>';
		str += '<input type="button" class="button secondary" value="Add" onclick="fm_addConditionTest(\'' + conditionInfo.id + '\')"/>';
		str += '<input type="hidden" name="' + conditionInfo.id + '-test-count" id="' + conditionInfo.id + '-test-count" value="' + (conditionInfo.tests.length + 1) + '" />';
		str += '<input type="hidden" name="' + conditionInfo.id + '-test-order" id="' + conditionInfo.id + '-test-order" value="" />';
		str += '</div>';
		
		str += '<div class="condition-items-div">';
		str += '<?php _e("Applies to",'wordpress-form-manager');?>:';
		str += '<ul id="' + conditionInfo.id + '-items' + '" class="condition-item-list">';
			for(var x=0;x<conditionInfo.items.length;x++)
				str += '<li class="condition-item" id="' + conditionInfo.id + '_item_li_' + x + '">' + fm_getItemHTML(conditionInfo.id, conditionInfo.items[x], x) + '</li>';
				
			if(conditionInfo.items.length == 0)
				str += '<li class="condition-item" id="' + conditionInfo.id + '_item_li_' + x + '">' + fm_getItemHTML(conditionInfo.id, false, x) + '</li>';			

		str += '</ul>';
		str += '<input type="button" class="button secondary" value="Add" onclick="fm_addConditionItem(\'' + conditionInfo.id + '\')"/>';
		str += '<input type="hidden" name="' + conditionInfo.id + '-item-count" id="' + conditionInfo.id + '-item-count" value="' + (conditionInfo.items.length + 1) + '" />';
		str += '</div>';
		
	str += '</td></tr>'
	
	str += '</table>';
	str += '</div>';
	
	return str;
}

function fm_getTestHTML(id, testInfo, index){
	var str = "";
	var itemID = "";
	var test = "";
	var connective = ( index == 0 ? "" : "and" );
	var val = "";
	if(testInfo != false){						
		itemID = testInfo.unique_name;
		test = testInfo.test;
		connective = testInfo.connective;
		val = testInfo.val;		
	}
		
	str += '<table><tr>';
	
	str += '<td id="' + id + '-condition-td-' + index + '"'; 
	if(connective == ""){ str += ' style="visibility:hidden;"';} 
	str += '>' + fm_getSelect(id + '-test-connective-' + index, ['and', 'or'], ['AND', 'OR'], connective) + '</td>';
	
	str += '<td>' + fm_getItemSelect(id + '-test-itemID-' + index, itemID) + '</td>';
	str += '<td>' + fm_getTestSelect(id + '-test-' + index, test) + '</td>';
	var textID = id + '-test-val-' + index;
	str += '<td><input type="text" size="20" id="' + textID + '" name="' + textID + '" class="test-value-input" value="' + val + '"/></td>';
	str += '<td><a class="edit-form-button" onclick="fm_removeTest(\'' + id + '\', \'' + index + '\')" >&nbsp;&nbsp;&nbsp;delete</a></td>';
	str += '</tr></table>';
	
	return str;
}

function fm_getItemHTML(id, itemID, index){
	return '<table><tr><td>' + fm_getAllItemsSelect(id + '-item-' + index, itemID) + '</td><td><a class="edit-form-button" onclick="fm_removeItem(\'' + id + '\', \'' + index + '\')">delete</a></td></tr></table>';
}

function fm_getRuleSelect(id, rule){
	var str = "";
	
	var ruleKeys = 		['none', 'onlyshowif', 'showif', 'hideif', 'requireonlyif', 'addrequireif', 'removerequireif'];
	var ruleNames = 	['(Choose a rule type)', 'Only show elements if...', 'Show elements if...', 'Hide elements if...', 'Require elements only if...', 'Require elements if...', 'Do not require elements if...'];

	str += fm_getSelect(id, ruleKeys, ruleNames, rule);
	
	return str;
}

function fm_getTestSelect(id, test){
	var keys = 	['', 'eq', 'neq', 'lt', 'gt', 'lteq', 'gteq', 'isempty', 'nisempty', 'checked', 'unchecked'];
	var names =	['...', 'equals', 'does not equal', 'is less than', 'is greater than',  'is less than or equal to', 'is greater than or equal to', 'is empty', 'is not empty', 'is checked', 'is not checked'];
	
	return fm_getSelect(id, keys, names, test);
}


function fm_getCheckboxTestSelect(id, test){
	var keys = ['', 'checked', 'unchecked'];
	var names = ['...', 'is checked', 'is not checked'];
	
	return fm_getSelect(id, keys, names, test);
}
</script>
<?php
/* translators: the following are from the form's advanced section */

global $fmdb;
global $fm_display;
global $fm_templates;
global $fm_form_behavior_types;

global $fm_DEBUG;
global $fm_MEMBERS_EXISTS;

/////////////////////////////////////////////////////////////////////////////////////
// Process settings changes

if(isset($_POST['submit-form-settings'])){
	$form = $fmdb->getForm($_POST['fm-form-id']);
	
	$formInfo = array();	
	$formInfo['conditions'] = processConditionsPost();
	
	$fmdb->updateForm($_POST['fm-form-id'], $formInfo);
}

//takes the posted info and converts it to the proper associative array structure to be stored in the DB
function processConditionsPost(){
	global $fmdb;
	
	if(strlen($_POST['fm-conditions-ids']) == 0) return false;

	$conditionIDs = explode(",", $_POST['fm-conditions-ids']);
	$condInfo = array();
	
	foreach($conditionIDs as $condID){
		if(substr($condID,0,3) == "new")
			$newCondID = $fmdb->getUniqueItemID('cond');
		else
			$newCondID = $condID;
		$tempInfo = array('rule' => $_POST[$condID.'-rule'], 'id' => $newCondID, 'tests' => array(), 'items' => array());
		$testOrder = explode(",", $_POST[$condID.'-test-order']);
		for($x=0;$x<sizeof($testOrder);$x++){
			$tempInfo['tests'][] = array('test' => $_POST[$condID.'-test-'.$testOrder[$x]],
										'unique_name' => $_POST[$condID.'-test-itemID-'.$testOrder[$x]],
										'val' => $_POST[$condID.'-test-val-'.$testOrder[$x]],
										'connective' => $_POST[$condID.'-test-connective-'.$testOrder[$x]]
									);
		}
		for($x=0;$x<$_POST[$condID.'-item-count'];$x++){
			$temp = $_POST[$condID.'-item-'.$x];
			if($temp != "")
				$tempInfo['items'][] = $temp;
		}
		$condInfo[$newCondID] = $tempInfo;							
	}
	return $condInfo;
}

/////////////////////////////////////////////////////////////////////////////////////

$form = null;
if($_REQUEST['id']!="")
	$form = $fmdb->getForm($_REQUEST['id']);
	
/////////////////////////////////////////////////////////////////////////////////////

$fm_globalSettings = $fmdb->getGlobalSettings();

?>

<form name="fm-main-form" id="fm-main-form" action="" method="post">
<input type="hidden" value="1" name="message" id="message-post" />
<input type="hidden" value="<?php echo $form['ID'];?>" name="fm-form-id" />

<div class="wrap" style="padding-top:15px;">

<div style="float:right;">
<input type="submit" name="cancel" class="button secondary" value="<?php _e("Cancel Changes", 'wordpress-form-manager');?>" />
<input type="submit" name="submit-form-settings" id="submit" class="button-primary" value="<?php _e("Save Changes", 'wordpress-form-manager');?>" onclick="return fm_saveConditions();" />&nbsp;&nbsp;

</div>

<div id="message-container"><?php 
if(isset($_POST['message']) && isset($_POST['submit-form-settings']))
	switch($_POST['message']){
		case 1: ?><div id="message-success" class="updated"><p><strong><?php _e("Settings Saved.", 'wordpress-form-manager');?> </strong></p></div><?php break;
		case 2: ?><div id="message-error" class="error"><p><?php _e("Save failed.", 'wordpress-form-manager');?> </p></div><?php break;
		default: ?>
			<?php if(isset($_POST['message']) && trim($_POST['message']) != ""): ?>
			<div id="message-error" class="error"><p><?php echo stripslashes($_POST['message']);?></p></div>
			<?php endif; ?>
		<?php
	} 
?></div>

<div id="fm-conditions-container">
	<input type="button" onclick="fm_newCondition()" class="button secondary" value="<?php _e("Add", 'wordpress-form-manager');?>" />
	<ul id="fm-conditions">
	
	</ul>
	<input type="hidden" id="fm-conditions-ids" name="fm-conditions-ids" value="" />
</div>
<script type="text/javascript">
<?php 
foreach($form['items'] as $item){
		?>
fm_register_form_item(<?php echo json_encode($item);?>);
		<?php
	
}

if(is_array($form['conditions'])){
	foreach($form['conditions'] as $condition){
?>
fm_addCondition(<?php echo json_encode($condition); ?>);
<?php
	}
}
?>
fm_initConditionEditor();
</script>

<p class="submit">
<input type="submit" name="cancel" class="button secondary" value="<?php _e("Cancel Changes", 'wordpress-form-manager');?>" />
<input type="submit" name="submit-form-settings" id="submit" class="button-primary" value="<?php _e("Save Changes", 'wordpress-form-manager');?>"  onclick="return fm_saveConditions();" />
</p>

</div>

</form>