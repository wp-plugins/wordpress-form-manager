////////////////////////////////////////////////////////////
//// VALIDATION ////////////////////////////////////////////

//fm_display_class registers the validator callbacks defined by each control, using the functions below.

//array of arrays: itemID -> item unique name, callbackFn -> callback function
//	callback function takes unique name as only argument

var fm_val = [];
function fm_val_register(_formID, _itemID, _callbackFn, _msg, _extra){
	if(!_extra) _extra = "";
	var newReq = {
		formID: _formID,
		itemID: _itemID,
		callbackFn: _callbackFn,
		msg: _msg,
		extra: _extra
	};	
	fm_val.push(newReq);
}

function fm_val_satisfied(formID){
	var temp = true;
	var msg = "";
	for(var x=0;x<fm_val.length;x++){
		if(fm_val[x].formID == formID){
			if(fm_val[x].extra == "")		
				eval("temp = " + fm_val[x].callbackFn + "('" + fm_val[x].formID + "', '" + fm_val[x].itemID + "');");
			else		
				eval("temp = " + fm_val[x].callbackFn + "('" + fm_val[x].formID + "', '" + fm_val[x].itemID + "', '" + fm_val[x].extra + "');");
				
			if(!temp){
				if(msg != "") msg += "\n";
				msg += fm_val[x].msg;	
			}
		}
	}
	if(msg != ""){
		alert(msg);
		return false;
	}
	return true;
}

function fm_validate(formID){
	return fm_val_satisfied(formID);
}
