function fm_trim(str){
	return str.replace(/^\s+|\s+$/g,"");
}
function fm_fix_str(str){
	return str.replace(/[\\]/g,'\\\\\\\\').replace(/[']/g,'\\\\\\$&');
}
function fm_htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}