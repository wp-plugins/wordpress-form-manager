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
		doSave = confirm(fm_I18n.save_with_deleted_items);
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
				email_list: document.getElementById('email_list').value,
				email_user_field: document.getElementById('email_user_field').value,
				auto_redirect: document.getElementById('auto_redirect').checked,
				auto_redirect_page: document.getElementById('auto_redirect_page').value,
				auto_redirect_timeout: document.getElementById('auto_redirect_timeout').value,
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
	document.getElementById('csv-working').style.visibility = 'visible';
	document.getElementById('fm-csv-download-link').innerHTML = "";
	
	var data = {
		action: 'fm_create_csv',
		id: document.getElementById('form-id').value,
		title: document.getElementById('title').value
	};	

	jQuery.post(ajaxurl, data, function(response){		
		//window.open(encodeURI(response),'Download');
		document.getElementById('csv-working').style.visibility = 'hidden';
		document.getElementById('fm-csv-download-link').href = encodeURI(response);
		document.getElementById('fm-csv-download-link').innerHTML = "Click here to download";
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
	document.getElementById(_itemID + '-download').style.visibility = 'hidden';
	document.getElementById(_itemID + '-working').style.visibility = 'visible';
	
	var data = {
		action: 'fm_download_all_files',
		id: document.getElementById('form-id').value,
		itemid: _itemID			
	}
	
	jQuery.post(ajaxurl, data, function(response){		
		document.getElementById(_itemID + '-working').style.visibility = 'hidden';
	
		switch(response){
			case "empty":
				alert("There are no files to download");
				break;
			case "fail":
				alert("Unable to create .ZIP file");
			default:
				document.getElementById(_itemID + "-link").style.visibility = 'visible';
				document.getElementById(_itemID + "-link").href = response;
				document.getElementById(_itemID + "-link").innerHTML = "Click here to download";
		}
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

/////////////////////////////////////////////////////
//// CONDITIONS EDITOR //////////////////////////////

var fm_conditions = [];
var fm_next_new_ID = 0;

function fm_initConditionEditor(){
	Sortable.create('fm-conditions',{handles:$$('a.handle')});
}


function fm_newCondition(){
	conditionInfo = fm_getNewConditionInfo('new-' + fm_next_new_ID++);
	fm_addCondition(conditionInfo);
}
function fm_addCondition(conditionInfo){			
	var listUL = document.getElementById('fm-conditions');
	var newLI = document.createElement('li');
	
	newLI.className = "edit-form-menu-item postbox";
	
	newLI.innerHTML = fm_getNewConditionHTML(conditionInfo);
	
	newLI.id = 'fm-condition-' + conditionInfo.id;
	
	listUL.appendChild(newLI);
	
	fm_initConditionBox(conditionInfo);
	
	fm_initConditionEditor();	
	
}

function fm_getNewConditionInfo(id){
	var newCondition = {
		id: id,
		rule: '',
		tests: [],
		items: []
	};
	return newCondition;
}

function fm_showHideCondition(id){
	Effect.toggle(id + '-div', 'Blind', {duration:0.1});
	var str = document.getElementById(id + '-showhide').innerHTML;
	if(str == 'show')
		document.getElementById(id + '-showhide').innerHTML = 'hide';
	else
		document.getElementById(id + '-showhide').innerHTML = 'show';
}

function fm_removeCondition(id){
	var LI = document.getElementById('fm-condition-' + id);
	LI.parentNode.removeChild(LI);
}

function fm_removeTest(id, index){
	var LI = document.getElementById(id + '_test_li_' + index);
	LI.parentNode.removeChild(LI);
	fm_fixConnectives(document.getElementById(id + '-tests'), id);
}

function fm_removeItem(id, index){
	var LI = document.getElementById(id + '_item_li_' + index);
	LI.parentNode.removeChild(LI);
}


function fm_addConditionTest(id){
	var listUL = document.getElementById(id + '-tests');
	var newLI = document.createElement('li');
	var count = document.getElementById(id + '-test-count').value++;
	
	newLI.className = "postbox condition-test";	
	newLI.id = id + '_test_li_' + count;
	newLI.innerHTML = fm_getTestHTML(id, false, count);
	
	listUL.appendChild(newLI);
	
	Sortable.create(id + '-tests', {onUpdate: function(el){fm_fixConnectives(el,id);}});
}
function fm_fixConnectives(el,condID){
	var str = "";
	var id = el.childNodes[0].id.toString();
	var index = id.substr(id.indexOf('_test_li_') + 9);
	
	document.getElementById(condID + '-condition-td-' + index).style.visibility = 'hidden';
	for(var x=1;x<el.childNodes.length;x++){
		id = el.childNodes[x].id.toString();
		index = id.substr(id.indexOf('_test_li_') + 9);
		document.getElementById(condID + '-condition-td-' + index).style.visibility = 'visible';
	}
}

function fm_initConditionBox(conditionInfo){
	Sortable.create(conditionInfo.id + '-tests', {onUpdate: function(el){fm_fixConnectives(el,conditionInfo.id);}});
	Sortable.create(conditionInfo.id + '-items');
	fm_fixConnectives(document.getElementById(conditionInfo.id + '-tests'), conditionInfo.id);
}

function fm_addConditionItem(id){
	var listUL = document.getElementById(id + '-items');
	var newLI = document.createElement('li');
	
	var count = document.getElementById(id + '-item-count').value++;
	
	newLI.className = "condition-item";
	newLI.innerHTML = fm_getItemHTML(id, '', count);
	newLI.id = id + '_item_li_' + count;
	
	listUL.appendChild(newLI);
	
	Sortable.create(id + '-items');
}

/* 

Rule types:

onlyshowif - only show the listed elements if X
showif - set the listed elements to 'show' if X
hideif - set the listed elements to 'hide' if X
addrequireif - make the listed elements required if X
removerequireif - make the listed elements not required if X
requiregroup - a list of elements collectively considered required, as in only one of the group needs to be populated

eq
neq
lt
gt
lteq
gteq
checked
unchecked
*/ 


/* helpers */



function fm_getItemSelect(id, itemID){
	var itemIDs = [''];
	var itemNames = ['...'];
	for(var x=0;x<fm_form_items.length;x++){
		if(fm_form_items[x].type != 'separator' &&
			fm_form_items[x].type != 'note' &&
			fm_form_items[x].type != 'recaptcha'){
				itemIDs.push(fm_form_items[x].unique_name);
				if(fm_form_items[x].nickname != "")
					itemNames.push(fm_form_items[x].nickname);
				else
					itemNames.push(fm_form_items[x].label);
			}
	}
	
	return fm_getSelect(id, itemIDs, itemNames, itemID);
}

function fm_getAllItemsSelect(id, itemID){
	var itemIDs = [''];
	var itemNames = ['...'];
	for(var x=0;x<fm_form_items.length;x++){		
		itemIDs.push(fm_form_items[x].unique_name);
		if(fm_form_items[x].nickname != "")
			itemNames.push(fm_form_items[x].nickname);
		else
			itemNames.push(fm_form_items[x].label);
	}
	
	return fm_getSelect(id, itemIDs, itemNames, itemID);
}

function fm_getSelect(id, keys, names, selected){
	var str = "";
	str += '<select id="' + id + '" name="' + id + '">';	
	for(var x=0;x<keys.length;x++){
		str += '<option value="' + keys[x] + '"';
		if(keys[x] == selected) str += ' selected="selected" ';
		str += '>' + names[x] + '</option>';
	}
	str += '</select>';
	return str;
}

/* functions to register form items with javascript */

var fm_form_items = [];

function fm_register_form_item(itemInfo){
	fm_form_items.push(itemInfo);
}

function fm_getItemType(itemID){
	for(var x=0;x<fm_form_items.length;x++){
		if(fm_form_items[x].unique_name == itemID) 
			return fm_form_items[x].type;
	}
}


/* save script */

function fm_saveConditions(){
	var mainUL = document.getElementById('fm-conditions');
	
	var str = "";
	var IDstr = "";
	
	var currCondID;
	var currCondTestUL;
	var currTestLI;
	
	var testIDs;
	var id;
	
	for(var x=0;x<mainUL.childNodes.length;x++){
						//prefix is 'fm-condition-'
		currCondID = mainUL.childNodes[x].id.substr(13);
		if(x>0) IDstr += ",";
		IDstr += currCondID;
		
		currTestUL = document.getElementById(currCondID + '-tests');
		
		str = "";
		for(var y=0;y<currTestUL.childNodes.length;y++){
			currTestLI = currTestUL.childNodes[y];
			id = currTestLI.id;
			if(y>0) str += ",";
			str += id.substr(id.indexOf('_test_li_') + 9);
		}
		
		document.getElementById(currCondID + '-test-order').value = str;
	}
	document.getElementById('fm-conditions-ids').value = IDstr;	
	
	return true;
}


////////////////////////////////////////////////////////////
//// HELPERS ///////////////////////////////////////////////

function fm_trim(str){
	return str.replace(/^\s+|\s+$/g,"");
}
function fm_fix_str(str){
	return str.replace(/[\\]/g,'\\\\\\\\').replace(/[']/g,'\\\\\\$&');
}
function fm_htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}