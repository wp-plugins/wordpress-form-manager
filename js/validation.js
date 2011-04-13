////////////////////////////////////////////////////////////
//// VALIDATION ////////////////////////////////////////////

//fm_display_class registers the validator callbacks defined by each control, using the functions below.

//array of arrays: itemID -> item unique name, callbackFn -> callback function
//	callback function takes unique name as only argument

var fm_val_required = [];
function fm_val_register_required(_formID, _itemID, _callbackFn, _msg){
	var newReq = {
		formID: _formID,
		itemID: _itemID,
		callbackFn: _callbackFn,
		msg: _msg
	};	
	fm_val_required.push(newReq);
}

function fm_val_required_satisfied(){
	var temp = true;
	var msg = "";
	for(var x=0;x<fm_val_required.length;x++){
		eval("temp = " + fm_val_required[x].callbackFn + "('" + fm_val_required[x].formID + "', '" + fm_val_required[x].itemID + "');");
		if(!temp){
			if(msg != "") msg += "\n";
			msg += fm_val_required[x].msg;	
		}
	}
	if(msg != ""){
		alert(msg);
		return false;
	}
	return true;
}

function fm_validate(formID){
	return fm_val_required_satisfied(formID);
}