<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<script type='text/javascript' src='http://localhost/wordpress/wp-admin/load-scripts.php?c=1&amp;load=prototype,jquery,utils,scriptaculous-root,scriptaculous-builder,scriptaculous-effects,scriptaculous-dragdrop,scriptaculous-slider,scriptaculous-controls&amp;ver=7815bf91dc2db898f56e3fe715b63b2a'></script>
<script type='text/javascript' src='http://localhost/wordpress/wp-content/plugins/formmanager/js/scripts.js?ver=3.0.4'></script>

<title>Untitled Document</title>
</head>

<body>

<div style="width:500px">	
	<ul id="multi-panel-item">
	</ul>
	<table><tr><td><input type="button" value="Add" onclick="js_multi_item_add('multi-panel-item',getItem())"/></td></tr></table>
</div>

<script type="text/javascript">
js_multi_item_create('multi-panel-item');
function getItem(){
	return "<input type=\"text\" />";
}

function some_fn(){
	some_other_fn('this string was passed as a parameter with an apostrophe(\') in it, single quote string...');
}
function some_other_fn(val){
	alert(val);
}
</script>

<input type="button" value="Test" onclick="some_fn()" />

<pre>
<?php print_r(unserialize('a:1:{s:7:"options";a:1:{i:0;s:9:"slashe \s";}}'));?>
</pre>

</body>
</html>
