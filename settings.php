<?php 
/* translators: the following are general plugin settings */
global $fm_controls;
global $fm_form_behavior_types;
global $fm_registered_user_only_msg;

$fm_registered_user_only_msg = __("'%s' is only available to registered users.", 'wordpress-form-manager');

/* 
reg_user_only 		- only show form to registered users
display_summ		- show the previous submission rather than the form
no_dup				- do not allow a submission after the first
edit				- give an 'edit' button after the previous submission summary
overwrite			- only store the latest submission
*/

/* translators: the following are descriptions of the different behavior types */

$fm_form_behavior_types = array(	__("Default", 'wordpress-form-manager')							=> '', 
									__("Registered users only", 'wordpress-form-manager') 			=> 'reg_user_only',
									__("Keep only most recent submission", 'wordpress-form-manager') 	=> 'reg_user_only,overwrite',
									__("Single submission", 'wordpress-form-manager')					=> 'reg_user_only,display_summ,single_submission',
									__("'User profile' style", 'wordpress-form-manager') 				=> 'reg_user_only,display_summ,edit'									
								);
								
$fm_controls['text']->initValidators();
?>