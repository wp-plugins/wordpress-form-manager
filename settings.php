<?php 
//// DEFINITIONS FOR TEXT FIELD VALIDATORS

//function fm_new_text_validator($name, $label, $message, $regexp)
					
//accepts integers and decimal; no scientific notation...				
fm_new_text_validator('number', "Numbers Only", "'%s' must be a valid number", '/^\s*[0-9]*[\.]?[0-9]+\s*$/');

//accepts anything with ten numbers, grouped appropriately. This covers any good faith attempt at a phone number, and also accepts extensions, etc.
fm_new_text_validator('phone', "Phone Number", "'%s' must be a valid phone number", '/^.*[0-9]{3}.*[0-9]{3}.*[0-9]{4}.*$/');

//accepts email addresses (makes sure there is an '@' and a '.' - otherwise, could be jibberish, funny characters, etc., but thats okay.)
fm_new_text_validator('email', "E-Mail", "'%s' Must be a valid E-Mail address", '/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/');



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

/* this shows up in the editor under 'behavior type'. The values are a comma separated list of behavior flags.
The following flags are available:
reg_user_only		- form is only available to registered users.  If an unregistered user or somebody who is not logged in tries to view the form, the $fm_registered_user_only_msg is displayed
no_dup				- only allow a single submission per registered user; must include reg_user_only to behave properly.  If the user views the form after submitting data, a summary of the user's submission data will be displayed.
edit				- must include reg_user_only and no_dup.  Like above, but gives an 'edit' option to change the submitted values.  Basically a 'user profile' type of behavior.
overwrite			- allow multiple submissions per registered user, but only store the latest submission.  must include reg_user_only to behave properly.  
*/

$fm_form_behavior_types = array(	"Default"							=> '', 
									"Registered users only" 			=> 'reg_user_only',
									"Single submission"					=> 'reg_user_only,no_dup',
									"'User profile' style" 				=> 'reg_user_only,no_dup,edit',
									"Keep only most recent submission" 	=> 'reg_user_only,overwrite'
								);
								
/* 

Registered users only - form is 

*/

?>