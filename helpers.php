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

function fm_write_file($fullPath, $fileData, $text = true){
	
	
	add_filter('filesystem_method', 'fm_getFSMethod');
	if(! WP_Filesystem( ) ){
		return "Could not initialize WP filesystem";
	}	
	remove_filter('filesystem_method', '_return_direct');
	
	global $wp_filesystem;
	if(! $wp_filesystem->put_contents( $fullPath, $fileData, FS_CHMOD_FILE ) ) {
		return "Error writing file";
	}
	
	return 0;
}
function fm_getFSMethod($autoMethod) {
	$method = get_option('fm-file-method'); 
	if($method = 'auto') return $autoMethod;
	return $method;
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

function fm_is_private_item($itemInfo){
	return $itemInfo['set'] != 0;
}


/////////////////////////////////////////////////////////////////////////
// Data page

function fm_getDefaultDataCols(){
	$cols = array(); 
				
	$cols[] = array('attributes' => 
					array( 'class' => 'timestamp-column' ),
					'value' => __("Timestamp", 'wordpress-form-manager'),
					'key' => 'timestamp',
					'editable' => false,
					);
	
	$cols[] = array('attributes' => 
					array( 'class' => 'user-column' ),
					'value' => __("User", 'wordpress-form-manager'),
					'key' => 'user',
					'editable' => false,
					);
	
	$cols[] = array('attributes' =>
					array( 'class' => 'ip-column' ),
					'value' => __("IP Address", 'wordpress-form-manager'),
					'key' => 'user_ip',
					'editable' => false,
					);
	$cols[] = array('attributes' =>
					array( 'class' => 'post-column' ),
					'value' => __("Post Link", 'wordpress-form-manager'),
					'key' => 'post_id',
					'editable' => false,
					'show-callback' => 'fm_getPostLink',
					);
	return $cols;
}

function fm_getPostLink($col, $dbRow){
	$postID = $dbRow['post_id'];
	if($postID != 0)
		return '<a href="'.get_permalink($postID).'">'.$postID.'</a>';
	else
		return "";
}

function fm_getFileLink($col, $dbRow){
	global $fm_controls;
	$link = $fm_controls['file']->parseData($col['key'], $col['item'], $dbRow[$col['key']]);
	if(strpos($link, "<a ") !== 0)
		$link = '<a class="fm-download-link" onclick="fm_downloadFile(\''.$col['item']['ID'].'\', \''.$col['item']['unique_name'].'\', \''.$dbRow['unique_id'].'\')" >'.$link.'</a>';
	return $link;
}

function fm_getTableCol($item){
	$col = array( 
		'value' => (empty($item['nickname']) ? $item['label'] : $item['nickname']),
		'key' => $item['unique_name'],
		'item' => $item,
		'editable' => true,
		);
	
	if($item['type'] == 'file'){
		$col['show-callback'] = 'fm_getFileLink';
		$col['value'] = '<a class="fm-download-link" onclick="fm_downloadAllFiles(\''.$col['item']['ID'].'\', \''.$col['item']['unique_name'].'\')" >'.$col['value'].'</a>';
	}
	
	return $col;
}

function fm_dataBuildTableCols($form, $subMetaFields, &$cols){
	foreach($form['items'] as $item){
		if($item['db_type'] != "NONE"){
			$newCol = fm_getTableCol($item);
			$cols[] = $newCol;
		}
	}
	
	foreach($subMetaFields as $item){
		$newCol = fm_getTableCol($item);
		$cols[] = $newCol;
	}
}

function fm_applyColSettings($fm_dataPageSettings, &$cols){
	global $fm_MEMBERS_EXISTS;
	foreach($cols as $i=>$col){
		$cols[$i]['hidden'] = in_array($col['key'], $fm_dataPageSettings['hide']);
		$cols[$i]['editable'] = !in_array($col['key'], $fm_dataPageSettings['noedit']);
		$cols[$i]['nosummary'] = in_array($col['key'], $fm_dataPageSettings['nosummary']);
		
		if($fm_MEMBERS_EXISTS){
			$cols[$i]['edit_capability'] = $fm_dataPageSettings['edit_capabilities'][$cols[$i]['key']];
		}
	}
}

// post processing

function fm_getCheckedItems(){
	$numrows = $_POST['fm-num-rows'];
	$checked=array();
	for($x=0;$x<$numrows;$x++){
		$rowID = $_POST['cb-'.$x];
		if(isset($_POST['cb-'.$rowID])){
			$checked[] = $rowID;
		}
	}
	return $checked;
}

function fm_getEditItems(){
	$numrows = $_POST['fm-num-rows'];
	$edit = array();
	for($x=0;$x<$numrows;$x++){
		$rowID = $_POST['cb-'.$x];
		if($_POST['cb-'.$rowID] == 'edit'){
			$edit[] = $rowID;
		}
	}
	return $edit;
}

function fm_getEditPost($subID, $cols, $all = false){
	global $fm_controls;
	global $fm_MEMBERS_EXISTS;
	
	$data=array();
	foreach($cols as $col){
		if(isset($col['item']) && 
			($all 
			|| (!$col['hidden'] 
				&& $col['editable'] 
				&& (!$fm_MEMBERS_EXISTS 
					|| trim($col['edit_capability']) == "" 
					|| current_user_can($col['edit_capability']))
				)
			)
		){
			$item = $col['item'];
			$postName = $subID.'-'.$item['unique_name'];
			$processed = $fm_controls[$item['type']]->processPost($postName, $item);
			if($processed !== NULL)
				$data[$item['unique_name']] = $processed;
		}
	}
	return $data;
}

function fm_createCSVFile($formID, $query, $filename){
	global $fmdb;

	$csvData = $fmdb->getFormSubmissionDataCSV($formID, $query);
	
	fm_write_file($filename, $csvData);
}

function fm_getTmpPath(){
	return  WP_PLUGIN_DIR."/".dirname(plugin_basename(__FILE__))."/".get_option("fm-temp-dir")."/";
}

function fm_getTmpURL(){
	return WP_PLUGIN_URL."/".dirname(plugin_basename(__FILE__))."/".get_option("fm-temp-dir")."/";
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