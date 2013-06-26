<?php
global $wpdb;

$tables = array(
	$wpdb->prefix.get_option( 'fm-forms-table-name' ),
	$wpdb->prefix.get_option( 'fm-items-table-name' ),
	$wpdb->prefix.get_option( 'fm-settings-table-name' ),
	$wpdb->prefix.get_option( 'fm-templates-table-name' )
	);
?>
<h3>
<?php
_e("There was a problem accessing the database.", 'wordpress-form-manager');
?>
</h3>
<p>
<?php
_e("Form Manager is not able to find one or more of the following tables:", 'wordpress-form-manager');
?>
<ul>
<?php
foreach( $tables as $tableName ){
	echo '<li>'.$tableName.'</li>';
}
?>
</ul>
</p>
