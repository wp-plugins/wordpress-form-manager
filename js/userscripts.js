var fm_registered_form_items = [];
var fm_registered_forms = [];

/* PHP passes the form structure to JS using 'fm_register_form_item' and 'fm_register_form' */
//itemDef is an 'associative array', the item's database entry (unpacked)
function fm_register_form_item(formID, itemDef){
	itemDef.formID = formID;
	fm_registered_form_items.push(itemDef);
}

//adds the appropriate event handlers to the form: 'fm_submit_onclick' becomes the onclick event handler for any item with the name 'fm_form_submit'
function fm_register_form(formID){
	var formElement = document.getElementById('fm-form-' + formID);
	var submitButtons = document.getElementsByName('fm_form_submit');
	for(x=0;x<submitButtons.length;x++){
		submitButtons[x].onclick = function(){ return fm_submit_onclick(formID); }		
	}
}

function fm_submit_onclick(formID){
	if(!fm_check_required_items(formID)) return false;
	if(!fm_check_text_validation(formID)) return false;
	return true;
}

/* Validation checks */

//check text validation
function fm_check_text_validation(formID){
	var msg = "";
	for(var x=0;x<fm_registered_form_items.length;x++){
		if(fm_registered_form_items[x].formID == formID && 
			fm_registered_form_items[x].type == 'text' &&
			!fm_item_validation_satisfied(fm_registered_form_items[x])){
			if(!temp){
				if(msg != "") msg += "\n";
				msg += fm_registered_form_items[x].validation_msg;	
			}
		}
	}
	if(msg != ""){
		alert(msg);
		return false;
	}
	return true;
}

function fm_item_validation_satisfied(itemDef){
	if(itemDef.validation_callback != ""){
		eval("temp = " + itemDef.validation_callback + "('" + itemDef.formID + "', '" + itemDef.unique_name + "', '" + itemDef.validation_type + "');");
		return temp;
	}
	return true;
}



//check required items
function fm_check_required_items(formID){
	var msg = "";
	for(var x=0;x<fm_registered_form_items.length;x++){
		if(fm_registered_form_items[x].formID == formID && 
			!fm_item_required_satisfied(fm_registered_form_items[x])){
			if(!temp){
				if(msg != "") msg += "\n";
				msg += fm_registered_form_items[x].required_msg;	
			}
		}
	}
	if(msg != ""){
		alert(msg);
		return false;
	}
	return true;
}


function fm_item_required_satisfied(itemDef){
	if(itemDef.required == 1 && itemDef.required_callback != ""){
		eval("temp = " + itemDef.required_callback + "('" + itemDef.formID + "', '" + itemDef.unique_name + "');");
		return temp;
	}
	return true;
}

function fm_set_required(itemID, req){
	for(var x=0;x<fm_registered_form_items.length;x++){
		temp = fm_registered_form_items[x];
		if(temp.unique_name == itemID){			
			fm_registered_form_items[x].required = req;
			tempID = 'fm-item-' + (temp.nickname != "" ? temp.nickname : temp.unique_name);
			EMs = document.getElementById(tempID).getElementsByTagName('em');
			EMs[0].style.display = (req == 1 ? 'inline' : 'none');
		}
	}
	
}
/* HTML 5 support & simulation */

function fm_supports_placeholder(){
	placeholderSupport = ("placeholder" in document.createElement("input"));
	return placeholderSupport;
}

function fm_add_placeholders(){
	if(!fm_supports_placeholder()){
		for(var i=0;i<fm_registered_form_items.length;i++){
			if(fm_registered_form_items[i].type == 'text'){
				var textItem = document.getElementById('fm-form-' + fm_registered_form_items[i].formID)[fm_registered_form_items[i].unique_name];
				textItem.value = fm_registered_form_items[i].extra.value;
				textItem.ph_hasEdit = false;
				textItem.ph_thePlaceholder = fm_registered_form_items[i].extra.value;
				textItem.onfocus = fm_simulate_placeholder_onfocus;
				textItem.onblur = fm_simulate_placeholder_onblur;
				textItem.onchange = fm_simulate_placeholder_onchange;
			}
		}
	}
}

function fm_simulate_placeholder_onfocus(){
	if(!this.ph_hasEdit)
		this.value = '';
}
function fm_simulate_placeholder_onblur(){
	if(this.value == "") this.ph_hasEdit = false;
	if(!this.ph_hasEdit)
		this.value = this.ph_thePlaceholder;
}
function fm_simulate_placeholder_onchange(){
	this.ph_hasEdit = true;
}	

function fm_remove_placeholders(){	
	for(var i=0;i<fm_registered_form_items.length;i++){
		if(fm_registered_form_items[i].type == 'text'){
			var textItem = document.getElementById('fm-form-' + fm_registered_form_items[i].formID)[fm_registered_form_items[i].unique_name];
			textItem.value = fm_registered_form_items[i].extra.placeholder;
		}
	}
}

function fm_simulate_HTML5(){
	
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