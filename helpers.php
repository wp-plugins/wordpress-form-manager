<?php
//adds slashes for single quotes; useful for putting text from a database into javascript functions
function format_string_for_js($str){
	return preg_replace("/(['])/","\\\\\${0}",preg_replace("/[\\\\]/","\\\\\\\\",$str));
}

//allows valid array expressions (and broken array expressions that will not evaluate anyway).  
function is_valid_array_expr($exprStr){
	$dbl_quote_str_lit = '[\\\\]*"([^"\\\\]|\\\\.)*[\\\\]*"';	//quotes may be slashed; this is okay
	$sngl_quote_str_lit = "[\\\\]*'([^'\\\\]|\\\\.)*[\\\\]*'";
	
	$arr_item_list_expr = "(".$dbl_quote_str_lit."|".$sngl_quote_str_lit."|[0-9]+|[(),]|array|=>|\s)*";
	return (preg_match("/^".$arr_item_list_expr."$/", $exprStr) > 0);
}
?>