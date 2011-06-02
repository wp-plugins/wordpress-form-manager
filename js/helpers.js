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

////////////////////////////////////////////////////////////
//// USER SIDE /////////////////////////////////////////////

var fm_form_items = [];
function fm_register_form_item(_formID, _itemID, _nickname, _type, _extra){
	if(!_extra) _extra = "";
	var newItem = {
		formID: _formID,
		itemID: _itemID,
		nickname: _nickname,
		type: _type,
		extra: _extra
	};	
	fm_form_items.push(newItem);
}

function fm_get_item_ID(_formID, _nickname){
	for(var i=0;i<fm_form_items.length;i++)
		if(fm_form_items[i].nickname == _nickname && fm_form_items[i].formID == _formID)
			return fm_form_items[i].itemID;
	return false;
}

function fm_supports_placeholder(){
	placeholderSupport = ("placeholder" in document.createElement("input"));
	return placeholderSupport;
}

function fm_add_placeholders(){
	if(!fm_supports_placeholder()){
		for(var i=0;i<fm_form_items.length;i++){
			if(fm_form_items[i].type == 'text'){
				var textItem = document.getElementById('fm-form-' + fm_form_items[i].formID)[fm_form_items[i].itemID];
				textItem.value = fm_form_items[i].extra.placeholder;
				textItem.ph_hasEdit = false;
				textItem.ph_thePlaceholder = fm_form_items[i].extra.placeholder;
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
	for(var i=0;i<fm_form_items.length;i++){
		if(fm_form_items[i].type == 'text'){
			var textItem = document.getElementById('fm-form-' + fm_form_items[i].formID)[fm_form_items[i].itemID];
			textItem.value = fm_form_items[i].extra.placeholder;
		}
	}
}

function fm_simulate_HTML5(){
	
}