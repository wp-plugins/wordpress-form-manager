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

function helper_option_field($id, $label, $options, $value = false, $desc = ""){
	?>
<tr valign="top">
	<th scope="row"><label for="<?php echo $id;?>"><?php echo $label;?></label></th>
	<td>
		<select name="<?php echo $id;?>" type="text" id="<?php echo $id;?>"/>
		<?php foreach($options as $k=>$v): ?>
			<option value="<?php echo $k;?>" <?php echo ($value==$k)?"selected=\"selected\"":"";?> ><?php echo $v;?></option>
		<?php endforeach; ?>
		</select>
	<span class="description"><?php echo $desc;?></span>
	</td>
</tr>
	<?php
}

function fm_get_file_data( $file, $fields) {
	
	$fp = fopen( $file, 'r' );
	$file_data = fread( $fp, 8192 );
	fclose( $fp );
	
	$file_vars = fm_get_str_data($file_data, $fields);
	
	return $file_vars;
}

function fm_get_str_data($str, $fields){
	$file_vars = array();
	foreach ( $fields as $field => $regex ) {
		$matches = array();
		preg_match_all( '/^[ \t\/*#@]*' . $regex . ':(.*)$/mi', $str, $matches, PREG_OFFSET_CAPTURE);
		
		foreach($matches[1] as $match){
			$arr = array('field' => $field,
							'value' => trim($match[0])
							);
			$file_vars[$match[1]] = $arr;
		}
	}
	
	ksort($file_vars);
	
	return $file_vars;
}

function fm_get_user_IP(){
	if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $IPAddr=$_SERVER['HTTP_X_FORWARDED_FOR'];
    else $IPAddr=$_SERVER['REMOTE_ADDR'];
	return $IPAddr;
}

function fm_get_slimstat_IP_link($queryVars, $ipAddr){
	return "<a href=\"".get_admin_url(null, 'index.php')."?".http_build_query(array_merge($queryVars, array('page' => 'wp-slimstat/view/index.php', 'slimpanel' => 4, 'ip' => $ipAddr, 'ip-op' => 'equal', 'direction' => 'DESC')))."\">".$ipAddr."</a>";
}

/////////////////////////////////////////////////////////////////////////
// Custom shortcode processor

class fm_custom_shortcode_parser{

	var $shortcodeList;
	var $shortcodeCallback; 
	
	function __construct($shortcodeList, $shortcodeCallback){
		$this->shortcodeList = $shortcodeList;
		$this->shortcodeCallback = $shortcodeCallback;
	}
	
	function parse($inputStr){		
		return $this->parseShortcodes($inputStr);
	}
	
	/////////////////////////////////////////////////////////////////////////
	// Parse Shortcodes
	
	function getShortcodeRegexp() {		
		$regexp = implode('|', $this->shortcodeList);
		return '/\[('.$regexp.')\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?/s';
	}
	
	function parseShortcodes($inputStr){		
		return preg_replace_callback($this->getShortcodeRegexp(), $this->shortcodeCallback, $inputStr);
	}
}

class fm_custom_attribute_parser{	
	function getAttributes($str){
		$vars = array();
		$matches = array();		
		preg_match_all( '/^[ \t\/*#@]*([a-zA-Z0-9\-]+):(.*)$/mi', $str, $matches, PREG_OFFSET_CAPTURE);		
		foreach($matches[2] as $index => $match)
			$vars[$matches[1][$index][0]] = $match[0];
		return $vars;
	}
}
?>