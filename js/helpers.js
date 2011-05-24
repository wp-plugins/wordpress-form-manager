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
function fm_register_form_item(_formID, _itemID, _type, _extra){
	if(!_extra) _extra = "";
	var newItem = {
		formID: _formID,
		itemID: _itemID,
		type: _type,
		extra: _extra
	};	
	fm_form_items.push(newItem);
}

function fm_supports_placeholder(){
	placeholderSupport = ("placeholder" in document.createElement("input"));
	return placeholderSupport;
}

function fm_fix_placeholders(force){
	if(!force)force = false;
	if(!fm_supports_placeholder() || force){
		for(var i=0;i<fm_form_items.length;i++){
			if(fm_form_items[i].type == 'text'){
				var textItem = document.getElementById('fm-form-' + fm_form_items[i].formID)[fm_form_items[i].itemID];
				textItem.value = fm_form_items[i].extra.placeholder;
			}
		}
	}
}