////////////////////////////////////////////////////////////
///// MAIN FORMS PAGE //////////////////////////////////////

//user clicks on 'delete' for a form
function fm_deleteFormClick(formID){
	document.getElementById('fm-action').value = "delete";
	document.getElementById('fm-id').value = formID;			
	document.getElementById('fm-main-form').submit();
}	

////////////////////////////////////////////////////////////
//// FORM EDITOR ///////////////////////////////////////////

//AJAX

function fm_saveForm(){	
	var doSave = true;
	if(fm_itemsWereDeleted){
		doSave = confirm("There may be data associated with the form item(s) you removed.  Are you sure you want to save?");
	}
	
	if(doSave){
		document.getElementById('ajax-loading').style.visibility = 'visible';
		var data = {
				action: 'fm_save_form',
				id: document.getElementById('form-id').value,
				title: document.getElementById('title').value,
				labels_on_top: document.getElementById('labels_on_top').value,
				submitted_msg: document.getElementById('submitted_msg').value,
				submit_btn_text: document.getElementById('submit_btn_text').value,
				show_title: document.getElementById('show_title').checked,
				show_border: document.getElementById('show_border').checked,
				shortcode: document.getElementById('shortcode').value,
				label_width: document.getElementById('label_width').value,
				items: fm_getFormItems('form-list')
		};	
	
		jQuery.post(ajaxurl, data, function(response){
			document.getElementById('message-post').value = response;
			document.getElementById('fm-main-form').submit();
		});	
	}
}

function fm_loadFields(){
	return confirm("Any unsaved changes will be lost. Are you sure?");
}

function fm_initEditor(){
	Sortable.create('form-list',{handles:$$('a.handle')});
}

//forms editor item functions

function fm_addItem(type){
	var listUL = document.getElementById('form-list');
	var newLI = document.createElement('li');
	
	var data = {
		action: 'fm_new_item',
		type: type
	};	
	
	jQuery.post(ajaxurl, data, function(response){
		eval('itemInfo = ' + response + ';');
		newLI.innerHTML = decodeURIComponent((itemInfo['html'] + '').replace(/\+/g, '%20'));
		newLI.id = itemInfo['uniqueName'];
		newLI.className = "edit-form-menu-item postbox";
		listUL.appendChild(newLI);
		fm_initEditor();
	});		
}

var fm_itemsWereDeleted = false;
function fm_deleteItem(itemID){
	var listItem = document.getElementById(itemID);
	listItem.parentNode.removeChild(listItem);
	fm_itemsWereDeleted = true;
}

function fm_getFormItems(editorID){
	var listUL = document.getElementById(editorID);
	var arr = [];
	for(var index=0; index<listUL.childNodes.length; index++){
		if(typeof listUL.childNodes[index].id != 'undefined'){				
			var itemID = listUL.childNodes[index].id;
			var newItem = fm_getFormItem(itemID,index);
			arr.push( newItem );			
		}
	}
	return arr;
}
function fm_getFormItem(itemID,index){	
	var fn = document.getElementById(itemID + '-get').value;
	eval('newItem = ' + fn + ';');
	return newItem;
}


//helpers for scripts for saving / editing individual items
function fm_get_item_value(itemID, key){
	return document.getElementById(itemID + '-' + key).value;
}
function fm_set_item_value(itemID, key, value){
	document.getElementById(itemID + '-' + key).value = value;
}


////////////////////////////////////////////////////////////
//// DATA PAGE /////////////////////////////////////////////

function fm_dataCBColChange(){
	var rows = document.getElementById('fm-num-data-rows').value;
	if(rows==0) return 0;
	var c =	document.getElementById('cb-col-top').checked;
	for(var x=0;x<rows;x++){		
		document.getElementById('fm-checked-' + x).checked = c;
	}
}

function fm_downloadCSV(){
	var data = {
			action: 'fm_create_csv',
			id: document.getElementById('form-id').value,
			title: document.getElementById('title').value
	};	

	jQuery.post(ajaxurl, data, function(response){		
		window.open(response,'Download');
	});	
}

/***************************************************************************/

var js_multi_item_count = [];
function js_multi_item_create(ulID){
	js_multi_item_count[ulID] = 0;
}
function js_multi_item_init(ulID){
	Sortable.create(ulID,{handles:$$('a.handle-' + ulID)});
}
function js_multi_item_add(ulID,callback,val){
	var UL = document.getElementById(ulID);
	var newLI = document.createElement('li');
	var newItemID = ulID + '-item-' + js_multi_item_count[ulID];
	eval("var HTML = " + callback + "('" + ulID + "', '" + newItemID + "', val);");
	newLI.innerHTML = "<table><tr><td><a href=\"#\" class=\"handle-" + ulID + "\">move</a></td><td>" + HTML + "</td><td><a href=\"#\" onclick=\"js_multi_item_remove('" + newItemID + "')\">delete</a></td></tr></table>";
	newLI.id = newItemID;
	UL.appendChild(newLI);
	js_multi_item_count[ulID]++;
	js_multi_item_init(ulID);
}
function js_multi_item_remove(itemID){
	var listItem = document.getElementById(itemID);
	listItem.parentNode.removeChild(listItem);
}
function js_multi_item_get(ulID,itemCallback){
	var UL = document.getElementById(ulID);
	var arr = [];
	for(var i=0;i<UL.childNodes.length;i++){
		if(typeof UL.childNodes[i].id != 'undefined'){
			eval("var itemValue = " + itemCallback + "('" + UL.childNodes[i].id + "');");
			arr.push(itemValue);			
		}
	}
	return arr;
}
function js_multi_item_get_index(ulID, itemCallback, index){
	var UL = document.getElementById(ulID);
	eval("var itemValue = " + itemCallback + "('" + UL.childNodes[index].id + "');");
	return itemValue;	
}
function js_multi_item_get_php_array(ulID, itemCallback){
	var optArr = js_multi_item_get(ulID, itemCallback);
	var str = "array(";
	for(var i=0;i<optArr.length;i++){
		str += "'" + fm_fix_str(optArr[i]) + "'";
		if(i<optArr.length-1) str += ", ";
	}
	str += ")";
	return str;
}