<?php 
global $fm_controls;

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

fm_set_form_defaults(array(		'title' => 				"New Form",
								'labels_on_top' =>		 0, 
								'submitted_msg' => 		'Thank you! Your data has been submitted.', 
								'submit_btn_text' => 	'Submit', 
								'required_msg' => 		"\'%s\' is required.", 
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

$fm_form_behavior_types = array(	"Default"							=> '', 
									"Registered users only" 			=> 'reg_user_only',
									"Keep only most recent submission" 	=> 'reg_user_only,overwrite',
									"Single submission"					=> 'reg_user_only,display_summ,single_submission',
									"'User profile' style" 				=> 'reg_user_only,display_summ,edit'									
								);
$fm_controls['text']->initValidators();
?>