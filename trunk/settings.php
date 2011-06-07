<?php 
/* translators: the following are general plugin settings */
global $fm_controls;
global $fm_form_behavior_types;
global $fm_registered_user_only_msg;

//// DEFINITIONS FOR TEXT FIELD VALIDATORS

//function fm_new_text_validator($name, $label, $message, $regexp)

/*				
//accepts integers and decimal; no scientific notation...				
fm_new_text_validator('number', "Numbers Only", "'%s' must be a valid number", '/^\s*[0-9]*[\.]?[0-9]+\s*$/');

//accepts anything with ten numbers, grouped appropriately. This covers any good faith attempt at a phone number, and also accepts extensions, etc.
fm_new_text_validator('phone', "Phone Number", "'%s' must be a valid phone number", '/^.*[0-9]{3}.*[0-9]{3}.*[0-9]{4}.*$/');

//accepts email addresses (makes sure there is an '@' and a '.' - otherwise, could be jibberish, funny characters, etc., but thats okay.)
fm_new_text_validator('email', "E-Mail", "'%s' Must be a valid E-Mail address", '/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/');
*/

//// DEFAULT FORM SETTINGS

fm_set_form_defaults(array(		'title' => 				__("New Form", 'wordpress-form-manager'),
								'labels_on_top' =>		 0, 
								'submitted_msg' => 		__('Thank you! Your data has been submitted.', 'wordpress-form-manager'), 
								'submit_btn_text' => 	__('Submit', 'wordpress-form-manager'), 
								'required_msg' => 		__("\'%s\' is required.", 'wordpress-form-manager'), 
								'show_title' => 		1,
								'show_border' => 		1,
								'label_width' => 		200
								));	
								
$fm_registered_user_only_msg = "'%s' is only available to registered users.";

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