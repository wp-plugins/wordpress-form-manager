<?php
include 'formelements/formelements.php';

///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////

//associative array: 'type name' => 'class name'
// the keys in this array are used in the 'addItem' AJAX to create new items, and as the 'type' db field for form items
$fm_controlTypes = array('default' => 'fm_controlBase',
						'text' => 'fm_textControl',
						'textarea' => 'fm_textareaControl',									
						'checkbox' => 'fm_checkboxControl',
						'separator' => 'fm_separatorControl',
						'custom_list' => 'fm_customListControl',
						'note' => 'fm_noteControl'
);
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////

//control base class
include 'types/base.php';

//control types
include 'types/separator.php';
include 'types/text.php';
include 'types/textarea.php';
include 'types/checkbox.php';
include 'types/list.php';
include 'types/note.php';

//'panel' helpers
include 'types/panelhelper.php';

///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////

function fm_showControlScripts(){
	?><script type="text/javascript">
	function fm_showEditDivCallback(itemID, callback){		
		var editDiv = document.getElementById(itemID + '-edit-div');
		var editClick = document.getElementById(itemID + '-edit');
				
		if(editClick.innerHTML == 'edit'){
			if(callback != "") eval(callback + '(itemID,false);');
			editClick.innerHTML = 'done';
			Effect.BlindDown(itemID + '-edit-div', { duration: 0.5 });
		}
		else{
			if(callback != "") eval(callback + '(itemID,true);');
			editClick.innerHTML = 'edit';
			Effect.BlindUp(itemID + '-edit-div', { duration: 0.5 });
		}	
	}
	</script><?php
}	

////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////

function fm_buildControlTypes($controlTypes){
	$arr = array();
	foreach($controlTypes as $name=>$class){
		$arr[$name] = new $class();
	}
	return $arr;
}

$fm_controls = fm_buildControlTypes($fm_controlTypes);

?>