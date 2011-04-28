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

//shortens a string to a specified width; if $useEllipse is true (default), three of these characters will be '...'
function fm_restrictString($string, $length, $useEllipse = true){
	if(strlen($string)<=$length) return $string;
	if($length > 3 && $useEllipse)	return substr($string, 0, $length-3)."...";
	else return substr($string, 0, $length);
}

function helper_text_field($id, $label, $value, $desc = ""){
	global $fm_globalSettings;
	?>
<tr valign="top">
	<th scope="row"><label for="<?php echo $id;?>"><?php echo $label;?></label></th>
	<td><input name="<?php echo $id;?>" type="text" id="<?php echo $id;?>"  value="<?php echo $value;?>" class="regular-text" />
	<span class="description"><?php echo $desc;?></span>
	</td>
</tr>
<?php
}

function helper_checkbox_field($id, $label, $checked, $desc = ""){
	global $fm_globalSettings;
	?>
<tr valign="top">
	<th scope="row"><label for="<?php echo $id;?>"><?php echo $label;?></label></th>
	<td><input name="<?php echo $id;?>" type="checkbox" id="<?php echo $id;?>"  <?php echo $checked===true?"checked":"";?> class="regular-text" />
	<span class="description"><?php echo $desc;?></span>
	</td>
</tr>
<?php
}

?>