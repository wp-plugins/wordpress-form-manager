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
		fm_save_error = false;
		document.getElementById('ajax-loading').style.visibility = 'visible';

		var data = {
				action: 'fm_save_form',
				id: document.getElementById('form-id').value,
				title: document.getElementById('title').value,				
				submitted_msg: document.getElementById('submitted_msg').value,
				submit_btn_text: document.getElementById('submit_btn_text').value,				
				shortcode: document.getElementById('shortcode').value,	
				required_msg: document.getElementById('required_msg').value,				
				show_summary: document.getElementById('show_summary').checked,
				template_values: { },
				items: fm_getFormItems('form-list')
		};	
		
		for(var x=0;x<fm_save_extra_vars.length;x++){
			var extraVal = fm_getItemValue(fm_save_extra_vars[x].id, fm_save_extra_vars[x].value);	
			var id = fm_save_extra_vars[x].id.toString();
			id = id.substr(3);
			data.template_values[id] = extraVal;			
		}
		
		if(!fm_save_error){		
			jQuery.post(ajaxurl, data, function(response){
				document.getElementById('message-post').value = response;
				document.getElementById('fm-main-form').submit();
			});	
		}
	}
}

var fm_save_error;
var fm_save_validators = [];

function fm_registerSaveValidator(_itemType, _fn){
	fm_save_validators[_itemType] = _fn;	
}

var fm_save_extra_vars = [];

function fm_registerExtraSaveVar(elementId, val){
	var newVar = {
		id: elementId,
		value: val
	};
	fm_save_extra_vars.push(newVar);
}

function fm_getItemValue(id, val){
	try{
		return document.getElementById(id)[val];
	}
	catch(err){
		return null;
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
	var fail;
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
	var type = document.getElementById(itemID + '-type').value;
	var valid;

	eval('newItem = ' + fn + ';');
	if(typeof(fm_save_validators[type]) != 'undefined'){
		eval("valid = " + fm_save_validators[type] + "('" + 	itemID + "');");
		if(!valid)
			fm_save_error = true;
	}
		
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

function fm_downloadFile(_itemID, _timestamp, _user, _user_ip){
	var data = {
		action: 'fm_download_file',
		id: document.getElementById('form-id').value,
		itemid: _itemID,
		timestamp: _timestamp,
		user: _user,
		user_ip: _user_ip
	}
	
	jQuery.post(ajaxurl, data, function(response){		
		window.open(response,'Download');				
	});
}

function fm_downloadAllFiles(_itemID){
	var data = {
		action: 'fm_download_all_files',
		id: document.getElementById('form-id').value,
		itemid: _itemID			
	}
	
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
	
	if(typeof js_multi_item_count[ulID] == 'undefined')
		js_multi_item_create(ulID);
	
	var UL = document.getElementById(ulID);
	var newLI = document.createElement('li');
	var newItemID = ulID + '-item-' + js_multi_item_count[ulID];
	eval("var HTML = " + callback + "('" + ulID + "', '" + newItemID + "', val);");
	newLI.innerHTML = "<table><tr><td><a class=\"handle-" + ulID + "\" style=\"cursor: move;\">move</a></td><td>" + HTML + "</td><td><a onclick=\"js_multi_item_remove('" + newItemID + "')\">delete</a></td></tr></table>";
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
	var itemValue = "";
	for(var i=0;i<UL.childNodes.length;i++){
		if(typeof UL.childNodes[i].id != 'undefined'){
			eval("itemValue = " + itemCallback + "('" + UL.childNodes[i].id + "');");
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

function js_multi_item_clear(ulID){
	var UL = document.getElementById(ulID);		
	for(var i=UL.childNodes.length-1;i>=0;i--){
		if(typeof UL.childNodes[i].id != 'undefined'){
			js_multi_item_remove(UL.childNodes[i].id);
		}
	}	
}

function js_multi_item_text_entry(ulID, getcallback, setcallback){
	var UL = document.getElementById(ulID);	
	var listItems = js_multi_item_get(ulID, getcallback);
	var listItemsText = "";
	for(var x=0; x<listItems.length;x++){		
		if(x>0) listItemsText += ", ";		
		listItemsText += listItems[x];
	}
	var newListItemsText = prompt("Enter items separated by commas", listItemsText);
	
	var neverHappens = "@%#$*&))("
	newListItemsText = newListItemsText.replace(/\\,/, neverHappens);
	newListItems = newListItemsText.split(",");
	
	js_multi_item_clear(ulID);
	
	var tempStr;
	for(var x=0; x<newListItems.length;x++){
		tempStr = jQuery.trim(newListItems[x].replace(neverHappens, ","));
		js_multi_item_add(ulID, setcallback, tempStr);	
	}
}