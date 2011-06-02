<?php
global $fm_MEMBERS_EXISTS;

parse_str($_SERVER['QUERY_STRING'], $queryVars);

$pages = array( array('sec' => 'design',
					'title' => __("Edit this form", 'wordpress-form-manager'),
					'linktext' => __("Edit", 'wordpress-form-manager'),
					'capability' => 'form_manager_forms',
					'page' => 'editformdesign.php'
				),
				array('sec' => 'data',
					'title' => __("View form data", 'wordpress-form-manager'),
					'linktext' => __("Submission Data", 'wordpress-form-manager'),
					'capability' => 'form_manager_data',
					'page' => 'formdata.php'
				),
				array('sec' => 'nicknames',
					'title' => __("Form item nicknames", 'wordpress-form-manager'),
					'linktext' => __("Item Nicknames", 'wordpress-form-manager'),
					'capability' => 'form_manager_nicknames',
					'page' => 'editformnn.php'
				),
				array('sec' => 'advanced',
					'title' => __("Advanced form settings", 'wordpress-form-manager'),
					'linktext' => __("Advanced", 'wordpress-form-manager'),
					'capability' => 'form_manager_forms_advanced',
					'page' => 'editformadv.php'
				)				
		);

// show the tabs

?>
<div class="wrap">
	<div id="icon-edit-pages" class="icon32"></div>
	<h2><?php _e("Edit Form", 'wordpress-form-manager'); ?></h2>
	<div id="fm-editor-tabs-wrap">
		<?php
		$arr = array();
		foreach($pages as $page){
			if(!$fm_MEMBERS_EXISTS || current_user_can($page['capability'])){
				if($_REQUEST['sec'] == $page['sec'])
					$arr[] = "<a class=\"nav-tab nav-tab-active\" href=\"".get_admin_url(null, 'admin.php')."?page=fm-edit-form&sec=".$page['sec']."&id=".$_REQUEST['id']."\" title=\"".$page['title']."\" />".$page['linktext']."</a>";
				else
					$arr[] = "<a class=\"nav-tab nav-tab-inactive\" href=\"".get_admin_url(null, 'admin.php')."?page=fm-edit-form&sec=".$page['sec']."&id=".$_REQUEST['id']."\" title=\"".$page['title']."\" />".$page['linktext']."</a>";	
			}
		}
		
		foreach($arr as $a){
			echo '<span class="fm-editor-tab">'.$a.'</span>';
		}
		
		?>
	</div>
	
</div>

<?php 

// show the appropriate page
$found = false;
foreach($pages as $page)
	if($queryVars['sec'] == $page['sec'] &&
		(!$fm_MEMBERS_EXISTS || current_user_can($page['capability']))){
			include dirname(__FILE__).'/'.$page['page'];
			$found = true;
	}


if (!$found && (!$fm_MEMBERS_EXISTS || current_user_can('form_manager_forms')))
	include dirname(__FILE__).'/editformdesign.php';

?>